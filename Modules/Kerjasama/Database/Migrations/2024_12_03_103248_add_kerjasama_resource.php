<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Gate\Models\Resource;
use Modules\Gate\Models\Role;
use Modules\Gate\Models\Modul;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $moduleId = DB::table('gate.modul')->where('kode_modul', Modul::CODE_KERJASAMA)->value('id');

        DB::beginTransaction();

        $resourceFlows = [
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Beranda',
                'segmen_url' => '',
                'permissions' => [
                    Role::ROLE_ADMIN_KERJASAMA => $this->getRolePermissions('get'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Daftar Mitra Kerjasama',
                'segmen_url' => 'mitra',
                'permissions' => [
                    Role::ROLE_ADMIN_KERJASAMA => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Data Kerjasama',
                'segmen_url' => 'data-kerjasama',
                'permissions' => [
                    Role::ROLE_ADMIN_KERJASAMA => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
        ];

        $resourceMaster = [
            // Data Pendukung
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Bentuk Kegiatan',
                'segmen_url' => 'bentuk-kegiatan',
                'permissions' => [
                    Role::ROLE_ADMIN_KERJASAMA => $this->getRolePermissions('get', 'create', 'update', 'delete')
                ]
            ], [
                'id_modul' => $moduleId,
                'nama_resource' => 'Status Kerjasama',
                'segmen_url' => 'status-kerjasama',
                'permissions' => [
                    Role::ROLE_ADMIN_KERJASAMA => $this->getRolePermissions('get', 'create', 'update', 'delete')
                ]
            ], [
                'id_modul' => $moduleId,
                'nama_resource' => 'Kriteria Mitra',
                'segmen_url' => 'kriteria-mitra',
                'permissions' => [
                    Role::ROLE_ADMIN_KERJASAMA => $this->getRolePermissions('get', 'create', 'update', 'delete')
                ]
            ], [
                'id_modul' => $moduleId,
                'nama_resource' => 'Sumber Dana',
                'segmen_url' => 'sumber-dana',
                'permissions' => [
                    Role::ROLE_ADMIN_KERJASAMA => $this->getRolePermissions('get', 'create', 'update', 'delete')
                ]
            ], [
                'id_modul' => $moduleId,
                'nama_resource' => 'Jenis Dokumen',
                'segmen_url' => 'jenis-dokumen',
                'permissions' => [
                    Role::ROLE_ADMIN_KERJASAMA => $this->getRolePermissions('get', 'create', 'update', 'delete')
                ]
            ],
        ];

        $resources = array_merge($resourceFlows, $resourceMaster);

        foreach ($resources as $resource) {
            $resourcesData = Arr::only($resource, ['id_modul', 'nama_resource', 'segmen_url']);
            $permissions = $resource['permissions'];

            try {
                $createdResource = Resource::firstOrCreate([
                    'id_modul' => $resource['id_modul'],
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

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }

    protected function getRolePermissions(...$permissions)
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
