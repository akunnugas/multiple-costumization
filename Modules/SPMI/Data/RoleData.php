<?php

namespace Modules\SPMI\Data;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Gate\Models\Resource;
use Modules\Gate\Models\Role;
use Modules\Gate\Services\RoleManagementService;

class RoleData
{
    public function migrate()
    {
        $this->updateRoleData();

        $moduleId = DB::table('gate.modul')->where('kode_modul', 'spmi')->value('id');

        DB::beginTransaction();

        $resourceFlows = [
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Beranda',
                'segmen_url' => '',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get'),
                    Role::ROLE_TIM_PENGISI_DATA => $this->getRolePermissions('get'),
                    Role::ROLE_REKTOR => $this->getRolePermissions('get'),
                    Role::ROLE_AUDITOR => $this->getRolePermissions('get'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get'),
                    Role::ROLE_AUDITEE => $this->getRolePermissions('get'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Dokumen Mutu',
                'segmen_url' => 'spmi-dokumen',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_TIM_PENGISI_DATA => $this->getRolePermissions('get'),
                    Role::ROLE_REKTOR => $this->getRolePermissions('get'),
                    Role::ROLE_AUDITOR => $this->getRolePermissions('get'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get'),
                    Role::ROLE_AUDITEE => $this->getRolePermissions('get'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Atur Target Capaian',
                'segmen_url' => 'target-indikator',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_REKTOR => $this->getRolePermissions('get'),
                    Role::ROLE_AUDITOR => $this->getRolePermissions('get'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get'),
                    Role::ROLE_AUDITEE => $this->getRolePermissions('get'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Penjadwalan Kegiatan Audit',
                'segmen_url' => 'jadwal-audit',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_REKTOR => $this->getRolePermissions('get'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get'),
                    Role::ROLE_AUDITEE => $this->getRolePermissions('get'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Surat Keputusan Auditor',
                'segmen_url' => 'sk-auditor',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Surat Tugas Audit',
                'segmen_url' => 'surat-tugas-auditor',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Pengisian Laporan Kinerja',
                'segmen_url' => 'pengisian-indikator',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_TIM_PENGISI_DATA => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_REKTOR => $this->getRolePermissions('get'),
                    Role::ROLE_AUDITOR => $this->getRolePermissions('get'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_AUDITEE => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Pengisian Evaluasi Diri',
                'segmen_url' => 'pengisian-indikator-led',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_REKTOR => $this->getRolePermissions('get'),
                    Role::ROLE_AUDITOR => $this->getRolePermissions('get'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_AUDITEE => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Penilaian Mandiri',
                'segmen_url' => 'penilaian-mandiri',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get', 'create', 'update'),
                    Role::ROLE_AUDITEE => $this->getRolePermissions('get', 'create', 'update'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Penilaian Auditor',
                'segmen_url' => 'penilaian-auditor',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update'),
                    Role::ROLE_AUDITOR => $this->getRolePermissions('get', 'create', 'update'),
                    Role::ROLE_AUDITEE => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Temuan Audit',
                'segmen_url' => 'audit-temuan',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update'),
                    Role::ROLE_AUDITOR => $this->getRolePermissions('get', 'create', 'update'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get'),
                    Role::ROLE_AUDITEE => $this->getRolePermissions('get'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Hasil Temuan Audit',
                'segmen_url' => 'hasil-audit-temuan',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update'),
                    Role::ROLE_AUDITOR => $this->getRolePermissions('get', 'create', 'update'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get'),
                    Role::ROLE_AUDITEE => $this->getRolePermissions('get'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Rapat Tinjauan Manajemen',
                'segmen_url' => 'tinjauan-temuan',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update'),
                    Role::ROLE_AUDITOR => $this->getRolePermissions('get'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get'),
                    Role::ROLE_AUDITEE => $this->getRolePermissions('get'),
                    Role::ROLE_REKTOR => $this->getRolePermissions('get'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Berita Acara',
                'segmen_url' => 'dokumen-hasil-audit',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update'),
                    Role::ROLE_REKTOR => $this->getRolePermissions('get'),
                    Role::ROLE_AUDITOR => $this->getRolePermissions('get'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get'),
                    Role::ROLE_AUDITEE => $this->getRolePermissions('get'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Skor Akhir',
                'segmen_url' => 'hasil-akhir-audit',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get'),
                    Role::ROLE_TIM_PENGISI_DATA => $this->getRolePermissions('get'),
                    Role::ROLE_REKTOR => $this->getRolePermissions('get'),
                    Role::ROLE_AUDITOR => $this->getRolePermissions('get'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get'),
                    Role::ROLE_AUDITEE => $this->getRolePermissions('get'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Pegawai',
                'segmen_url' => 'pegawai',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create'),
                ]
            ],
        ];

        $resourceMaster = [
            // Perguruan Tinggi
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Jenjang Pendidikan',
                'segmen_url' => 'jenjang-pendidikan',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Unit Kerja',
                'segmen_url' => 'unit-kerja',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],

            // AMI
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Jenis Standar',
                'segmen_url' => 'jenis-standar',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Standar Akreditasi',
                'segmen_url' => 'akreditasi-standar',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Buku Akreditasi',
                'segmen_url' => 'akreditasi-buku',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Lembaga Akreditasi',
                'segmen_url' => 'lembaga-akreditasi',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Periode Audit',
                'segmen_url' => 'audit-periode',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Syarat Perlu Akreditasi',
                'segmen_url' => 'akreditasi-syarat',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Panduan Pengisian',
                'segmen_url' => 'pengisian-panduan',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Tabel IKU',
                'segmen_url' => 'indikator-laporan-kinerja',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Tabel IKT',
                'segmen_url' => 'indikator-lk-tambahan',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Komponen ED Utama',
                'segmen_url' => 'indikator-evaluasi-diri',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Komponen ED Tambahan',
                'segmen_url' => 'indikator-led-tambahan',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Mapping Laporan Kinerja',
                'segmen_url' => 'mapping-laporan-kinerja',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Mapping LK & LED',
                'segmen_url' => 'mapping-indikator-butir',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Mapping Komponen ED',
                'segmen_url' => 'mapping-evaluasi-diri',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Mapping Panduan',
                'segmen_url' => 'mapping-panduan',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Panduan Penilaian',
                'segmen_url' => 'penilaian-panduan',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Matriks Penilaian',
                'segmen_url' => 'penilaian-matriks',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Matriks Penilaian IKT',
                'segmen_url' => 'penilaian-matriks-ikt',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Mapping Matriks Penilaian',
                'segmen_url' => 'mapping-matriks-penilaian',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Bobot Indikator Audit',
                'segmen_url' => 'setting-indikator-bobot',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Peringkat Audit',
                'segmen_url' => 'spmi-peringkat',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Peringkat Akreditasi',
                'segmen_url' => 'akreditasi-peringkat',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get'),
                ]
            ],

            // Sementara tidak dipakai
            // Wilayah
            // [
            //     'id_modul' => $moduleId,
            //     'nama_resource' => 'Negara',
            //     'segmen_url' => 'countries',
            //     'permissions' => []
            // ], [
            //     'id_modul' => $moduleId,
            //     'nama_resource' => 'Provinsi',
            //     'segmen_url' => 'provinces',
            //     'permissions' => []
            // ], [
            //     'id_modul' => $moduleId,
            //     'nama_resource' => 'Kabupaten / Kota',
            //     'segmen_url' => 'cities',
            //     'permissions' => []
            // ], [
            //     'id_modul' => $moduleId,
            //     'nama_resource' => 'Kecamatan',
            //     'segmen_url' => 'districts',
            //     'permissions' => []
            // ],

            // Umum
            // [
            //     'id_modul' => $moduleId,
            //     'nama_resource' => 'Agama',
            //     'segmen_url' => 'religions',
            //     'permissions' => []
            // ],

            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Import Indikator',
                'segmen_url' => 'import-indikator',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create', 'update', 'delete'),
                ]
            ],

            // FIXME: Laporan belum disesuaikan
            // Report
            [
                'id_modul' => $moduleId,
                'nama_resource' => 'Laporan',
                'segmen_url' => 'reports',
                'permissions' => [
                    Role::ROLE_ADMIN_PENJAMINAN_MUTU => $this->getRolePermissions('get', 'create'),
                    Role::ROLE_ADMINPT => $this->getRolePermissions('get', 'create'),
                    Role::ROLE_REKTOR => $this->getRolePermissions('get', 'create'),
                    Role::ROLE_AUDITOR => $this->getRolePermissions('get', 'create'),
                    Role::ROLE_KAPRODI => $this->getRolePermissions('get', 'create'),
                    Role::ROLE_AUDITEE => $this->getRolePermissions('get', 'create'),
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
            ['kode_role' => Role::ROLE_AUDITOR],
            [
                'nama_role' => 'Auditor',
                'kode_role' => Role::ROLE_AUDITOR,
                'ref_key_siakad' => 'ATR',
                'apakah_statis' => true
            ]
        );

        Role::updateOrCreate(
            ['kode_role' => Role::ROLE_ADMIN_PENJAMINAN_MUTU],
            [
                'nama_role' => 'Admin Penjaminan Mutu',
                'kode_role' => Role::ROLE_ADMIN_PENJAMINAN_MUTU,
                'ref_key_siakad' => 'ADMPJ',
                'apakah_statis' => true
            ]
        );

        Role::updateOrCreate(
            ['kode_role' => Role::ROLE_TIM_PENGISI_DATA],
            [
                'nama_role' => 'Tim Pengisi Data',
                'kode_role' => Role::ROLE_TIM_PENGISI_DATA,
                'ref_key_siakad' => 'TPD',
                'apakah_statis' => true
            ]
        );

        Role::updateOrCreate(
            ['kode_role' => Role::ROLE_REKTOR],
            [
                'nama_role' => 'Rektor',
                'kode_role' => Role::ROLE_REKTOR,
                'ref_key_siakad' => 'Rktor',
                'apakah_statis' => true
            ]
        );

        Role::updateOrCreate(
            ['kode_role' => Role::ROLE_KAPRODI],
            [
                'nama_role' => 'Kaprodi',
                'kode_role' => Role::ROLE_KAPRODI,
                'ref_key_siakad' => 'KA',
                'apakah_statis' => true
            ]
        );

        Role::updateOrCreate(
            ['kode_role' => Role::ROLE_AUDITEE],
            [
                'nama_role' => 'Auditee',
                'kode_role' => Role::ROLE_AUDITEE,
                'ref_key_siakad' => 'ADT',
                'apakah_statis' => true
            ]
        );

        DB::commit();
    }
}
