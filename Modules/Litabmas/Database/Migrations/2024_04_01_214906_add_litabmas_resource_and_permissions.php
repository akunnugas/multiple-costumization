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
        DB::beginTransaction();

        // mapping
        $resourceReference = [
            [
                'nama_resource' => 'Beranda',
                'segmen_url' => '',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get'),
                    'lm_reviewer' => $this->getRolePermissions('get'),
                    'lm_peneliti' => $this->getRolePermissions('get'),
                    'lm_pembimbing' => $this->getRolePermissions('get'),
                    Role::ROLE_LITABMAS_KETUA_LPPM => $this->getRolePermissions('get'),
                ]
            ],
            [
                'nama_resource' => Menu::getTitle('periode_pendanaan'),
                'segmen_url' => 'periode-pendanaan',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => Menu::getTitle('sumber_pendanaan'),
                'segmen_url' => 'sumber-pendanaan',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => Menu::getTitle('klaster_pendanaan'),
                'segmen_url' => 'klaster-pendanaan',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => Menu::getTitle('jenis_output_penelitian'),
                'segmen_url' => 'jenis-output-penelitian',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => Menu::getTitle('jenis_outcome_penelitian'),
                'segmen_url' => 'jenis-outcome-penelitian',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => Menu::getTitle('tema_kegiatan'),
                'segmen_url' => 'tema-kegiatan',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => Menu::getTitle('aspek_penilaian_isian_proposal'),
                'segmen_url' => 'aspek-penilaian-isian-proposal',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => Menu::getTitle('aspek_penilaian_komposisi_proposal'),
                'segmen_url' => 'penilaian-komposisi-proposal',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => Menu::getTitle('aspek_penilaian_presentasi_proposal'),
                'segmen_url' => 'penilaian-presentasi-proposal',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => Menu::getTitle('aspek_penilaian_output'),
                'segmen_url' => 'aspek-penilaian-output',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => Menu::getTitle('jenis_aktivitas'),
                'segmen_url' => 'jenis-aktivitas',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => Menu::getTitle('agenda_kegiatan'),
                'segmen_url' => 'agenda-kegiatan',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get'),
                ]
            ],
            [
                'nama_resource' => Menu::getTitle('dokumen_petunjuk_teknis'),
                'segmen_url' => 'dokumen-petunjuk-teknis',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
        ];

        $resourceFlows = [
            [ // Usulan Anggota
                'nama_resource' => 'Usulan Anggota',
                'segmen_url' => 'usulan-dosen-eksternal',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    'lm_peneliti' => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [ // Pengajuan Pendanaan
                'nama_resource' => Menu::getTitle('pengajuan_pendanaan'),
                'segmen_url' => 'pengajuan-pendanaan',
                'permissions' => [
                    'lm_peneliti' => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ]
        ];

        // proses insert ke db
        $moduleId = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
        $resources = array_merge($resourceReference, $resourceFlows);
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
