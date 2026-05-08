<?php

namespace Modules\Kerjasama\Data;

use Arr;
use Illuminate\Support\Facades\DB;
use Modules\Gate\Models\Resource;
use Modules\Gate\Models\Role;

class RoleData
{
    public function migrate()
    {
        $this->updateRoleData();

        $moduleId = DB::table('gate.modul')->where('kode_modul', 'kerjasama')->value('id');

        DB::beginTransaction();

        $roleBasic = [
            Role::ROLE_ADMIN_KERJASAMA => $this->getRolePermissions('get', 'create', 'update', 'delete'),
            Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete')
        ];

        $resources = [
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Beranda',
                'segmen_url' => '',
                'permissions' => $roleBasic
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Bentuk Kegiatan',
                'segmen_url' => 'bentuk-kegiatan',
                'permissions' => $roleBasic
            ], 
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Status Kerjasama',
                'segmen_url' => 'status-kerjasama',
                'permissions' => $roleBasic
            ], 
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Kriteria Mitra',
                'segmen_url' => 'kriteria-mitra',
                'permissions' => $roleBasic
            ], 
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Sumber Dana',
                'segmen_url' => 'sumber-dana',
                'permissions' => $roleBasic
            ], 
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Jenis Dokumen',
                'segmen_url' => 'jenis-dokumen',
                'permissions' => $roleBasic
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Sasaran Kinerja',
                'segmen_url' => 'sasaran-kinerja',
                'permissions' => $roleBasic
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Sasaran Kinerja',
                'segmen_url' => 'sasaran-kinerja',
                'permissions' => $roleBasic
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Kegiatan',
                'segmen_url' => 'kegiatan',
                'permissions' => $roleBasic
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Kegiatan',
                'segmen_url' => 'kegiatan',
                'permissions' => $roleBasic
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Kegiatan Kerjasama',
                'segmen_url' => 'kegiatan-kerjasama',
                'permissions' => $roleBasic
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Mitra',
                'segmen_url' => 'mitra',
                'permissions' => $roleBasic
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Kerjasama',
                'segmen_url' => 'data-kerjasama',
                'permissions' => $roleBasic
            ],
             [
                'id_modul' => $moduleId,
                'nama_resource' => 'Dashboard',
                'segmen_url' => 'dashboard',
                'permissions' => $roleBasic
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Kerjasama Mitra',
                'segmen_url' => 'kerjasama-mitra',
                'permissions' => $roleBasic
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Laporan Kerjasama',
                'segmen_url' => 'laporan-kerjasama',
                'permissions' => $roleBasic
            ],
        ];

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

            // Delete permission yang tidak ada di list
            $roleId = Role::whereIn('kode_role', array_keys($permissions))->pluck('id')->toArray();
            $forDelete = DB::table('gate.role_akses')->whereNotIn('id_role', $roleId)->where('id_resource', $createdResource->id)->get()->toArray();

            if (!empty($forDelete)) {
                DB::table('gate.role_akses')->whereIn('id', array_column($forDelete, 'id'))->delete();
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

    protected function updateRoleData()
    {
        DB::beginTransaction();

        Role::updateOrCreate(
            ['kode_role' => Role::ROLE_ADMIN_KERJASAMA],
            [
                'nama_role' => 'Admin Kerjasama',
                'kode_role' => Role::ROLE_ADMIN_KERJASAMA,
                'ref_key_siakad' => 'ADMKS',
                'apakah_statis' => true
            ]
        );

        DB::commit();
    }
}
