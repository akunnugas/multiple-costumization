<?php

namespace Modules\Litabmas\Data;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Gate\Models\Modul;
use Modules\Gate\Models\Resource;
use Modules\Gate\Models\ResourceAksi;
use Modules\Gate\Models\Role;

class RoleData
{
    public function migrate()
    {
        // create or update role for Litabmas
        $this->updateRoleDataForLitabmas();

        DB::beginTransaction();

        // [Start] create main resource & permission
        // litabmas modul id
        $moduleId = DB::table('gate.modul')->where('kode_modul', Modul::CODE_LITABMAS)->value('id');

        // define resources
        $resources = $this->defineResources();
        foreach ($resources as $resource) {
            try {
                $createdResource = Resource::firstOrCreate([
                    'id_modul' => $moduleId,
                    'segmen_url' => $resource['segmen_url'],
                ], [
                    'nama_resource' => $resource['nama_resource']
                ]);
            } catch (\Exception $e) {
                Log::warning('Litabmas Role Data - Resource:' . $e->getMessage());
                DB::rollBack();
            }

            // Delete permission yang tidak ada di list
            $permissions = $resource['permissions'];
            $roleId = Role::whereIn('kode_role', array_keys($permissions))->pluck('id')->toArray();
            $forDelete = DB::table('gate.role_akses')
                ->whereNotIn('id_role', $roleId)
                ->where('id_resource', $createdResource->id)
                ->get()->toArray();

            if (!empty($forDelete)) {
                DB::table('gate.role_akses')
                    ->whereIn('id', array_column($forDelete, 'id'))
                    ->delete();
            }

            foreach ($permissions as $kodeRole => $permission) {
                $role = Role::where('kode_role', $kodeRole)->first();

                try {
                    DB::table('gate.role_akses')->updateOrInsert(
                        ['id_role' => $role->id, 'id_resource' => $createdResource->id],
                        ['id_role' => $role->id, 'id_resource' => $createdResource->id, ...$permission]
                    );
                } catch (\Exception $e) {
                    Log::warning('Litabmas Role Data - Permission:' . $e->getMessage());
                    DB::rollBack();
                }
            }
        }

        DB::commit();
        // [End] create main resource & permission

        // [Start] custom permission & resource
        DB::beginTransaction();

        $customResources = $this->defineCustomResources();
        $registeredCustomResourceIds = [];
        foreach ($customResources as $customResource) {
            $parentIdResource = Resource::where('segmen_url', $customResource['parent_segmen_url'])
                ->where('id_modul', $moduleId)
                ->value('id');

            try {
                $createdResourceAksi = ResourceAksi::firstOrCreate([
                    'id_resource' => $parentIdResource,
                    'kode_aksi' => $customResource['kode_aksi'],
                ], [
                    'nama_aksi' => $customResource['nama_aksi'],
                ]);
                $registeredCustomResourceIds[] = $createdResourceAksi->id;
            } catch (\Exception $e) {
                Log::warning('Litabmas Role Data - Custom Resource:' . $e->getMessage());
                DB::rollBack();
            }

            // delete permission yang tidak ada di list
            $allowedRoles = $customResource['allowed_roles'];
            $roleIds = Role::whereIn('kode_role', $allowedRoles)->pluck('id')->toArray();
            $forDelete = DB::table('gate.role_akses_aksi')
                ->where('id_resource_aksi', $createdResourceAksi->id)
                ->whereNotIn('id_role', $roleIds)
                ->get()->toArray();

            if (!empty($forDelete)) {
                DB::table('gate.role_akses_aksi')
                    ->whereIn('id', array_column($forDelete, 'id'))
                    ->delete();
            }

            foreach ($allowedRoles as $kodeRole) {
                $role = Role::where('kode_role', $kodeRole)->first();

                try {
                    DB::table('gate.role_akses_aksi')->updateOrInsert([
                        'id_role' => $role->id,
                        'id_resource_aksi' => $createdResourceAksi->id,
                    ], [
                        'id_role' => $role->id,
                        'id_resource_aksi' => $createdResourceAksi->id,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Litabmas Role Data - Custom Permission:' . $e->getMessage());
                    DB::rollBack();
                }
            }
        }

        // [Start] Delete un-used resource_aksi & role_akses_aksi
        // get semua resource_aksi yang tidak terdaftar di custom resource modul litabmas
        $forDeleteResourceAksi = DB::table('gate.resource_aksi')
            ->join('gate.resource', 'gate.resource_aksi.id_resource', '=', 'gate.resource.id')
            ->where('gate.resource.id_modul', $moduleId)
            ->whereNotIn('gate.resource_aksi.id', $registeredCustomResourceIds)
            ->select('gate.resource_aksi.id')
            ->get()->toArray();

        if (!empty($forDeleteResourceAksi)) {
            $resourceAksiIds = array_column($forDeleteResourceAksi, 'id');

            // delete role_akses_aksi first
            DB::table('gate.role_akses_aksi')
                ->whereIn('id_resource_aksi', $resourceAksiIds)
                ->delete();

            // delete resource_aksi
            DB::table('gate.resource_aksi')
                ->whereIn('id', $resourceAksiIds)
                ->delete();
        }
        // [End] Delete un-used resource_aksi & role_akses_aksi

        DB::commit();
        // [End] custom permission & resource
    }

    private function defineCustomResources()
    {
        return [
            [
                'parent_segmen_url' => 'bimbingan',
                'nama_aksi' => 'Feedback Aktivitas Peneliti',
                'kode_aksi' => 'feedback-akvts-pnltn',
                'allowed_roles' => [Role::ROLE_DOSEN]
            ],
        ];
    }

    private function defineResources()
    {
        return [
            [
                'nama_resource' => 'Beranda',
                'segmen_url' => '',
                'permissions' => [
                    Role::ROLE_LITABMAS_KETUA_LPPM => $this->getRolePermissions('get'),
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get'),
                    Role::ROLE_DOSEN => $this->getRolePermissions('get'),
                    Role::ROLE_DOSEN_EKSTERNAL => $this->getRolePermissions('get'),
                    Role::ROLE_REKTOR => $this->getRolePermissions('get'),
                    Role::ROLE_DEKAN => $this->getRolePermissions('get'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_REKTOR_1 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_REKTOR_2 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_REKTOR_3 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_DEKAN_1 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_DEKAN_2 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_DEKAN_3 => $this->getRolePermissions('get'),
                ]
            ],
            [
                'nama_resource' => 'Periode Pendanaan',
                'segmen_url' => 'periode-pendanaan',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Sumber Pendanaan',
                'segmen_url' => 'sumber-pendanaan',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_LITABMAS_KETUA_LPPM => $this->getRolePermissions('get'),
                    Role::ROLE_REKTOR => $this->getRolePermissions('get'),
                    Role::ROLE_DEKAN => $this->getRolePermissions('get'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_REKTOR_1 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_REKTOR_2 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_REKTOR_3 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_DEKAN_1 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_DEKAN_2 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_DEKAN_3 => $this->getRolePermissions('get'),

                ]
            ],
            [
                'nama_resource' => 'Klaster Pendanaan',
                'segmen_url' => 'klaster-pendanaan',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Output',
                'segmen_url' => 'jenis-output-penelitian',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Outcome',
                'segmen_url' => 'jenis-outcome-penelitian',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Tema Kegiatan',
                'segmen_url' => 'tema-kegiatan',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Review Proposal',
                'segmen_url' => 'aspek-penilaian-isian-proposal',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Penilaian Aspek Proposal',
                'segmen_url' => 'penilaian-komposisi-proposal',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Penilaian Presentasi Proposal',
                'segmen_url' => 'penilaian-presentasi-proposal',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Penilaian Output',
                'segmen_url' => 'aspek-penilaian-output',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Jenis Aktivitas',
                'segmen_url' => 'jenis-aktivitas',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Tahapan Kegiatan',
                'segmen_url' => 'agenda-kegiatan',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get'),
                ]
            ],
            [
                'nama_resource' => 'Dokumen Petunjuk Teknis',
                'segmen_url' => 'dokumen-petunjuk-teknis',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Usulan Anggota',
                'segmen_url' => 'usulan-dosen-eksternal',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_DOSEN => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Pengajuan Pendanaan',
                'segmen_url' => 'pengajuan-pendanaan',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'update', 'delete'),
                    Role::ROLE_LITABMAS_KETUA_LPPM => $this->getRolePermissions('get', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get'),
                    Role::ROLE_DOSEN => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_DOSEN_EKSTERNAL => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Proposal Pengabdian',
                'segmen_url' => 'pengajuan-pengabdian',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'update', 'delete'),
                    Role::ROLE_LITABMAS_KETUA_LPPM => $this->getRolePermissions('get'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get'),
                    Role::ROLE_DOSEN => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_DOSEN_EKSTERNAL => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Penilaian Administrasi',
                'segmen_url' => 'penilaian-administrasi',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Penilain Presentasi Proposal',
                'segmen_url' => 'penilaian-presentasi',
                'permissions' => [
                    Role::ROLE_DOSEN => $this->getRolePermissions('get', 'update'),
                    Role::ROLE_DOSEN_EKSTERNAL => $this->getRolePermissions('get', 'update'),
                ]
            ],
            [
                'nama_resource' => 'Penilaian Isian Proposal',
                'segmen_url' => 'penilaian-isian',
                'permissions' => [
                    Role::ROLE_DOSEN => $this->getRolePermissions('get', 'update'),
                    Role::ROLE_DOSEN_EKSTERNAL => $this->getRolePermissions('get', 'update'),
                ]
            ],
            [
                'nama_resource' => 'Penentuan Nominasi',
                'segmen_url' => 'penentuan-nominasi',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_LITABMAS_KETUA_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Daftar Bimbingan',
                'segmen_url' => 'bimbingan',
                'permissions' => [
                    Role::ROLE_DOSEN => $this->getRolePermissions('get'),
                ]
            ],
            [
                'nama_resource' => 'Penentuan Pendanaan',
                'segmen_url' => 'penentuan-pendanaan',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_LITABMAS_KETUA_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Penilaian Progress Report',
                'segmen_url' => 'penilaian-progres',
                'permissions' => [
                    Role::ROLE_DOSEN => $this->getRolePermissions('get', 'update'),
                    Role::ROLE_DOSEN_EKSTERNAL => $this->getRolePermissions('get', 'update'),
                ]
            ],
            [
                'nama_resource' => 'Penilaian Output',
                'segmen_url' => 'penilaian-output',
                'permissions' => [
                    Role::ROLE_DOSEN => $this->getRolePermissions('get', 'update'),
                    Role::ROLE_DOSEN_EKSTERNAL => $this->getRolePermissions('get', 'update'),
                ]
            ],
            [
                'nama_resource' => 'Bidang Ilmu',
                'segmen_url' => 'bidang-ilmu',
                'permissions' => [ // sync itu hak aksesnya create
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create'),
                ]
            ],
            [
                'nama_resource' => 'Riwayat Proposal',
                'segmen_url' => 'riwayat-proposal',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get'),
                    Role::ROLE_LITABMAS_KETUA_LPPM => $this->getRolePermissions('get'),
                    Role::ROLE_DEKAN => $this->getRolePermissions('get'),
                    Role::ROLE_REKTOR => $this->getRolePermissions('get'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_REKTOR_1 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_REKTOR_2 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_REKTOR_3 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_DEKAN_1 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_DEKAN_2 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_DEKAN_3 => $this->getRolePermissions('get'),
                ]
            ],
            [
                'nama_resource' => 'Pendanaan Kegiatan',
                'segmen_url' => 'pendanaan-kegiatan',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get'),
                    Role::ROLE_LITABMAS_KETUA_LPPM => $this->getRolePermissions('get'),
                    Role::ROLE_DEKAN => $this->getRolePermissions('get'),
                    Role::ROLE_REKTOR => $this->getRolePermissions('get'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_REKTOR_1 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_REKTOR_2 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_REKTOR_3 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_DEKAN_1 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_DEKAN_2 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_DEKAN_3 => $this->getRolePermissions('get'),
                ]
            ],
            [
                'nama_resource' => 'Publikasi',
                'segmen_url' => 'publikasi',
                'permissions' => [
                    Role::ROLE_DOSEN => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_DOSEN_EKSTERNAL => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Pengumuman',
                'segmen_url' => 'pengumuman-pendanaan',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_LITABMAS_KETUA_LPPM => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_DEKAN => $this->getRolePermissions('get'),
                    Role::ROLE_REKTOR => $this->getRolePermissions('get'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_REKTOR_1 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_REKTOR_2 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_REKTOR_3 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_DEKAN_1 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_DEKAN_2 => $this->getRolePermissions('get'),
                    Role::ROLE_WAKIL_DEKAN_3 => $this->getRolePermissions('get'),
                    Role::ROLE_DOSEN => $this->getRolePermissions('get'),
                    Role::ROLE_DOSEN_EKSTERNAL => $this->getRolePermissions('get'),
                ]
            ],
            [
                'nama_resource' => 'Laporan Pendanaan Kegiatan',
                'segmen_url' => 'laporan-pendanaan-kegiatan',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create'),
                    Role::ROLE_LITABMAS_KETUA_LPPM => $this->getRolePermissions('get', 'create'),
                ]
            ],
            [
                'nama_resource' => 'Laporan Pengajuan Proposal',
                'segmen_url' => 'laporan-pengajuan-proposal',
                'permissions' => [
                    Role::ROLE_LITABMAS_ADMIN_LPPM => $this->getRolePermissions('get', 'create'),
                    Role::ROLE_LITABMAS_KETUA_LPPM => $this->getRolePermissions('get', 'create'),
                ]
            ],
        ];
    }

    private function updateRoleDataForLitabmas()
    {
        DB::beginTransaction();

        Role::updateOrCreate([
            'kode_role' => Role::ROLE_LITABMAS_ADMIN_LPPM,
        ], [
            'nama_role' => 'Admin LPPM',
            'kode_role' => Role::ROLE_LITABMAS_ADMIN_LPPM,
            'apakah_statis' => true,
            'ref_key_siakad' => 'LALPM'
        ]);
        Role::updateOrCreate([
            'kode_role' => Role::ROLE_LITABMAS_KETUA_LPPM,
        ], [
            'nama_role' => 'Ketua LPPM',
            'kode_role' => Role::ROLE_LITABMAS_KETUA_LPPM,
            'apakah_statis' => true,
            'ref_key_siakad' => 'LKLPM'
        ]);
        Role::updateOrCreate([
            'kode_role' => Role::ROLE_REKTOR
        ], [
            'nama_role' => 'Rektor',
            'kode_role' => Role::ROLE_REKTOR,
            'ref_key_siakad' => 'Rktor',
            'apakah_statis' => true
        ]);
        Role::updateOrCreate([
            'kode_role' => Role::ROLE_DEKAN
        ], [
            'nama_role' => 'Dekan',
            'apakah_statis' => true,
            'ref_key_siakad' => 'dek'
        ]);
        Role::updateOrCreate([
            'kode_role' => Role::ROLE_DOSEN
        ], [
            'nama_role' => 'Dosen',
            'apakah_statis' => true,
            'ref_key_siakad' => 'dosen'
        ]);
        Role::updateOrCreate([
            'kode_role' => Role::ROLE_DOSEN_EKSTERNAL
        ], [
            'nama_role' => 'Dosen Eksternal',
            'apakah_statis' => true,
            'ref_key_siakad' => 'doeks'
        ]);

        DB::commit();
    }

    public function getRolePermissions(...$permissions)
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
}
