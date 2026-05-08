<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Gate\Models\Modul;
use Modules\Gate\Models\Resource;
use Modules\Gate\Models\Role;
use Modules\Litabmas\Data\RoleData;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roleData = new RoleData();

        $resources = [
            [
                'nama_resource' => 'Beranda',
                'segmen_url' => '',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get'),
                ]
            ],
            [
                'nama_resource' => 'Periode Pendanaan',
                'segmen_url' => 'periode-pendanaan',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Sumber Pendanaan',
                'segmen_url' => 'sumber-pendanaan',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Klaster Pendanaan',
                'segmen_url' => 'klaster-pendanaan',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Output',
                'segmen_url' => 'jenis-output-penelitian',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Outcome',
                'segmen_url' => 'jenis-outcome-penelitian',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Tema Kegiatan',
                'segmen_url' => 'tema-kegiatan',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Review Proposal',
                'segmen_url' => 'aspek-penilaian-isian-proposal',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Penilaian Aspek Proposal',
                'segmen_url' => 'penilaian-komposisi-proposal',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Penilaian Presentasi Proposal',
                'segmen_url' => 'penilaian-presentasi-proposal',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Penilaian Output',
                'segmen_url' => 'aspek-penilaian-output',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Jenis Aktivitas',
                'segmen_url' => 'jenis-aktivitas',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Tahapan Kegiatan',
                'segmen_url' => 'agenda-kegiatan',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Dokumen Petunjuk Teknis',
                'segmen_url' => 'dokumen-petunjuk-teknis',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Usulan Anggota',
                'segmen_url' => 'usulan-dosen-eksternal',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Penilaian Administrasi',
                'segmen_url' => 'penilaian-administrasi',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Penentuan Nominasi',
                'segmen_url' => 'penentuan-nominasi',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Penentuan Pendanaan',
                'segmen_url' => 'penentuan-pendanaan',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Bidang Ilmu',
                'segmen_url' => 'bidang-ilmu',
                'permissions' => [ // sync itu hak aksesnya create
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create'),
                ]
            ],
            [
                'nama_resource' => 'Riwayat Proposal',
                'segmen_url' => 'riwayat-proposal',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get'),
                ]
            ],
            [
                'nama_resource' => 'Pendanaan Kegiatan',
                'segmen_url' => 'pendanaan-kegiatan',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get'),
                ]
            ],
            [
                'nama_resource' => 'Pengumuman',
                'segmen_url' => 'pengumuman-pendanaan',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'nama_resource' => 'Laporan Pendanaan Kegiatan',
                'segmen_url' => 'laporan-pendanaan-kegiatan',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create'),
                ]
            ],
            [
                'nama_resource' => 'Laporan Pengajuan Proposal',
                'segmen_url' => 'laporan-pengajuan-proposal',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get', 'create'),
                ]
            ],
            [
                'nama_resource' => 'Pengajuan Pendanaan',
                'segmen_url' => 'pengajuan-pendanaan',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get'),
                ]
            ],
            [
                'nama_resource' => 'Pengajuan Pengabdian',
                'segmen_url' => 'pengajuan-pengabdian',
                'permissions' => [
                    Role::ROLE_ADMINPT => $roleData->getRolePermissions('get'),
                ]
            ],
        ];

        $moduleId = DB::table('gate.modul')->where('kode_modul', Modul::CODE_LITABMAS)->value('id');

        DB::beginTransaction();

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

            $permissions = $resource['permissions'];
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
