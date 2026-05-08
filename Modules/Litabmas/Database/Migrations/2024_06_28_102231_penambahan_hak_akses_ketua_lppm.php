<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Gate\Models\Resource;
use Modules\Gate\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $resources = [
            [
                'nama_resource' => 'Pengumuman',
                'segmen_url' => 'pengumuman-pendanaan',
                'permissions' => [
                    Role::ROLE_LITABMAS_KETUA_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Pendanaan Kegiatan',
                'segmen_url' => 'pendanaan-kegiatan',
                'permissions' => [
                    Role::ROLE_LITABMAS_KETUA_LPPM => $this->getRolePermissions('get'),
                ]
            ]
        ];

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

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();

        try {
            $idModul = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
            $role = Role::whereIn('kode_role', [Role::ROLE_LITABMAS_KETUA_LPPM])->get();

            $resources = Resource::where('id_modul', $idModul)
                ->whereIn('segmen_url', ['pengumuman-pendanaan', 'pendanaan-kegiatan'])
                ->get();

            foreach ($resources as $resource) {
                foreach ($role as $r) {
                    DB::table('gate.role_akses')->where('id_role', $r->id)
                        ->where('id_resource', $resource->id)
                        ->delete();
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }

        DB::commit();
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
