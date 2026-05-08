<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Gate\Models\Resource;
use Modules\Gate\Models\Role;
use Modules\Litabmas\Helpers\Menu;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::beginTransaction();

        // [Start] Penyesuaian role
        // NOTE: hak akses/permission dosen dan dosen eksternal kurang lebih sama.
        // set role dosen, dosen eksternal, rektor, dan dekan utk bisa akses litabmas file home
        $resources = [
            [
                'nama_resource' => 'Beranda',
                'segmen_url' => '',
                'permissions' => [
                    ROLE::ROLE_DOSEN => $this->getRolePermissions('get'),
                    ROLE::ROLE_DOSEN_EKSTERNAL => $this->getRolePermissions('get'),
                    ROLE::ROLE_DEKAN => $this->getRolePermissions('get'),
                    ROLE::ROLE_REKTOR => $this->getRolePermissions('get'),
                ]
            ],
            [ // Pengajuan Pendanaan
                'nama_resource' => Menu::getTitle('pengajuan_pendanaan'),
                'segmen_url' => 'pengajuan-pendanaan',
                'permissions' => [
                    ROLE::ROLE_DOSEN => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    ROLE::ROLE_DOSEN_EKSTERNAL => $this->getRolePermissions('get', 'create', 'update', 'delete')
                ]
            ],
            [ // Usulan Anggota
                'nama_resource' => 'Usulan Anggota',
                'segmen_url' => 'usulan-dosen-eksternal',
                'permissions' => [
                    ROLE::ROLE_DOSEN => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
        ];

        // proses insert ke db
        $moduleId = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
        foreach ($resources as $resource) {
            $resourcesData = Arr::only($resource, ['id_modul', 'nama_resource', 'segmen_url']);
            $permissions = $resource['permissions'];

            try {
                $createdResource = Resource::firstOrCreate([
                    'id_modul' => $moduleId,
                    'segmen_url' => $resource['segmen_url'],
                ], $resourcesData);
            } catch (\Exception $e) {
                DB::rollBack();
            }

            foreach ($permissions as $roleCode => $permission) {
                $role = Role::where('kode_role', $roleCode)->first();

                try {
                    DB::table('gate.role_akses')->updateOrInsert(
                        ['id_role' => $role->id, 'id_resource' => $createdResource->id],
                        ['id_role' => $role->id, 'id_resource' => $createdResource->id, ...$permission]
                    );
                } catch (\Exception $e) {
                    DB::rollBack();
                }
            }
        }
        // [End] Penyesuaian role

        \Illuminate\Support\Facades\DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    private function getRolePermissions(...$permissions)
    {
        $permissionData = array_filter(array_map(function ($permission) {
            if ($permission == 'get') {
                return ['key' => 'bisa_get', 'act' => true];
            } else if ($permission == 'create') {
                return ['key' => 'bisa_post', 'act' => true];
            } else if ($permission == 'update') {
                return ['key' => 'bisa_put', 'act' => true];
            } else if ($permission == 'delete') {
                return ['key' => 'bisa_delete', 'act' => true];
            }

            return null;
        }, $permissions));

        $permissionData = array_column(array_values($permissionData), 'act', 'key');

        foreach (['bisa_get', 'bisa_post', 'bisa_put', 'bisa_delete'] as $permission) {
            if (!isset($permissionData[$permission])) {
                $permissionData[$permission] = false;
            }
        }

        return $permissionData;
    }
};
