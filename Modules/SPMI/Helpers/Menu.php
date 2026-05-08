<?php

namespace Modules\SPMI\Helpers;

use Modules\Gate\Models\Modul;
use Modules\SPMI\Models\AkreditasiBuku;

class Menu
{
    /**
     * Navbar atas.
     *
     * @return array
     */
    public static function navbar()
    {
        return [
            ['label' => 'Beranda', 'path' => '/'],
            ['label' => 'Dokumen Mutu', 'path' => 'spmi-dokumen'],
            [
                'label' => 'Audit Mutu Internal', 'items' => [
                    ['label' => self::getTitle('jadwal_audit'), 'path' => 'jadwal-audit'],
                    ['label' => self::getTitle('target_indikator'), 'path' => 'target-indikator'],
                    ['label' => self::getTitle('sk_auditor'), 'path' => 'sk-auditor'],
                    ['label' => self::getTitle('surat_tugas_auditor'), 'path' => 'surat-tugas-auditor'],
                    ['label' => self::getTitle('pengisian_indikator'), 'path' => 'pengisian-indikator'],
                    ['label' => self::getTitle('pengisian_indikator_led'), 'path' => 'pengisian-indikator-led'],
                    ['label' => self::getTitle('penilaian_mandiri'), 'path' => 'penilaian-mandiri'],
                    ['label' => self::getTitle('penilaian_auditor'), 'path' => 'penilaian-auditor'],
                    ['label' => self::getTitle('audit_temuan'), 'path' => 'audit-temuan'],
                    ['label' => self::getTitle('hasil_audit_temuan'), 'path' => 'hasil-audit-temuan'],
                    ['label' => self::getTitle('tinjauan_temuan'), 'path' => 'tinjauan-temuan'],
                    ['label' => self::getTitle('dokumen_hasil_audit'), 'path' => 'dokumen-hasil-audit'],
                    ['label' => self::getTitle('hasil_akhir_audit'), 'path' => 'hasil-akhir-audit'],
                ]
            ],
            [
                'label' => 'Referensi', 'items' => [
                    ['label' => 'Profil Perguruan Tinggi', 'path' => 'jenjang-pendidikan'],
                    ['label' => 'Data Pegawai', 'path' => 'pegawai'],
                    ['label' => 'Referensi Audit Mutu Internal', 'path' => 'jenis-standar'],
                    // ['label' => 'Jenis Dokumen', 'path' => 'document-types'],
                    // ['label' => 'Wilayah', 'path' => 'countries'],
                    // ['label' => 'Umum', 'path' => 'religions'],
                ]
            ],
            ['label' => 'Laporan', 'items' => [
                ['label' => 'Dokumen SPMI', 'path' => 'reports/'],
            ]],
        ];
    }

    /**
     * Master sidebar.
     */
    public static function masterSidebar($key)
    {
        return match ($key) {
            'organization' => [
                'parent' => 'jenjang-pendidikan', 'items' => [
                    [
                        'label' => 'Perguruan Tinggi', 'items' => [
                            ['label' => self::getTitle('jenjang_pendidikan'), 'path' => 'jenjang-pendidikan'],
                            ['label' => self::getTitle('unit_kerja'), 'path' => 'unit-kerja'],
                            // ['label' => self::getTitle('university_types'), 'path' => 'university-types']
                        ]
                    ],
                ]
            ],
            'accreditation' => [
                'parent' => 'jenis-standar', 'items' => [
                    [
                        'label' => 'Referensi Utama', 'items' => [
                            ['label' => self::getTitle('jenis_standar'), 'path' => 'jenis-standar'],
                            // ['label' => self::getTitle('akreditasi_standar'), 'path' => 'akreditasi-standar'],
                            // ['label' => self::getTitle('akreditasi_buku'), 'path' => 'akreditasi-buku'],
                            ['label' => self::getTitle('lembaga_akreditasi'), 'path' => 'lembaga-akreditasi'],
                            ['label' => self::getTitle('audit_periode'), 'path' => 'audit-periode'],
                            ['label' => self::getTitle('akreditasi_syarat'), 'path' => 'akreditasi-syarat'],
                        ]
                    ],
                    [
                        'label' => 'Pengisian',
                        'items' => [
                            ['label' => self::getTitle('pengisian_panduan'), 'path' => 'pengisian-panduan'],
                            ['label' => self::getTitle('indikator_laporan_kinerja'), 'path' => 'indikator-laporan-kinerja'],
                            ['label' => self::getTitle('indikator_laporan_kinerja_tambahan'), 'path' => 'indikator-lk-tambahan'],
                            ['label' => self::getTitle('indikator_evaluasi_diri'), 'path' => 'indikator-evaluasi-diri'],
                            ['label' => self::getTitle('indikator_evaluasi_diri_tambahan'), 'path' => 'indikator-led-tambahan'],
                            ['label' => self::getTitle('mapping_lkled'), 'path' => 'mapping-indikator-butir'],
                        ],
                    ],
                    [
                        'label' => 'Penilaian', 'items' => [
                            ['label' => self::getTitle('penilaian_panduan'), 'path' => 'penilaian-panduan'],
                            ['label' => self::getTitle('mapping_panduan'), 'path' => 'mapping-panduan'],
                            ['label' => self::getTitle('penilaian_matriks'), 'path' => 'penilaian-matriks'],
                            ['label' => self::getTitle('penilaian_matriks_ikt'), 'path' => 'penilaian-matriks-ikt'],
                            ['label' => self::getTitle('mapping-matriks-penilaian'), 'path' => 'mapping-matriks-penilaian']
                        ]
                    ],
                    [
                        'label' => 'Skor Akhir', 'items' => [
                            ['label' => self::getTitle('setting_indikator_bobot'), 'path' => 'setting-indikator-bobot'],
                            ['label' => self::getTitle('spmi_peringkat'), 'path' => 'spmi-peringkat'],
                            // ['label' => self::getTitle('akreditasi_peringkat'), 'path' => 'akreditasi-peringkat'],
                        ]
                    ]
                ]
            ],
            'region' => [
                'parent' => 'countries', 'items' => [
                    [
                        'label' => 'Wilayah', 'items' => [
                            ['label' => self::getTitle('countries'), 'path' => 'countries'],
                            ['label' => self::getTitle('provinces'), 'path' => 'provinces'],
                            ['label' => self::getTitle('cities'), 'path' => 'cities'],
                            ['label' => self::getTitle('districts'), 'path' => 'districts']
                        ]
                    ],
                ]
            ],
            'other' => [
                'parent' => 'religions', 'items' => [
                    [
                        'label' => 'Umum', 'items' => [
                            ['label' => self::getTitle('religions'), 'path' => 'religions']
                        ]
                    ],
                ]
            ],
            default => null
        };
    }

    /**
     * Sub Sidebar Indicator .
     */
    public static function indicatorSidebar($id)
    {
        return [
            'parent' => 'indikator-laporan-kinerja',
            'parent_sidebar' => 'masterSidebar.accreditation.indikator-laporan-kinerja',
            'items' => [
                [
                    'items' => [
                        [
                            'label' => self::getTitle('indikator_laporan_kinerja'), 'path' => 'indikator-laporan-kinerja/' . $id,
                            'permission' => 'indikator-laporan-kinerja'
                        ],
                        // [
                        //     'label' => self::getTitle('indikator_laporan_kinerja/indikator_kolom'), 'path' => 'indikator-laporan-kinerja/' . $id . '/indikator-kolom',
                        //     'permission' => 'indikator-laporan-kinerja'
                        // ],
                        // [
                        //     'label' => self::getTitle('indikator_laporan_kinerja/indikator_baris'), 'path' => 'indikator-laporan-kinerja/' . $id . '/indikator-baris',
                        //     'permission' => 'indikator-laporan-kinerja'
                        // ],
                        // [
                        //     'label' => self::getTitle('indikator_laporan_kinerja/indikator_cell'), 'path' => 'indikator-laporan-kinerja/' . $id . '/indikator-cell',
                        //     'permission' => 'indikator-laporan-kinerja'
                        // ],
                        // [
                        //     'label' => self::getTitle('indikator_laporan_kinerja/indicator_footers'), 'path' => 'indikator-laporan-kinerja/' . $id . '/indicator-footers',
                        //     'permission' => 'indikator-laporan-kinerja'
                        // ]
                    ]
                ],
            ]
        ];
    }

    public static function indicatorLKTambahan($id)
    {
        return [
            'parent' => 'indikator-lk-tambahan',
            'parent_sidebar' => 'masterSidebar.accreditation.indikator-laporan-kinerja-tambahan',
            'items' => [
                [
                    'items' => [
                        [
                            'label' => self::getTitle('indikator_laporan_kinerja_tambahan'), 'path' => 'indikator-lk-tambahan/' . $id,
                            'permission' => 'indikator-laporan-kinerja-tambahan'
                        ],
                    ]
                ],
            ]
        ];
    }

    public static function indicatorLEDTambahan($id)
    {
        return [
            'parent' => 'indikator-led-tambahan',
            'parent_sidebar' => 'masterSidebar.accreditation.indikator-evaluasi-diri-tambahan',
            'items' => [
                [
                    'items' => [
                        [
                            'label' => self::getTitle('indikator_evaluasi_diri_tambahan'), 'path' => 'indikator-led-tambahan/' . $id,
                            'permission' => 'indikator-evaluasi-diri-tambahan'
                        ],
                    ]
                ],
            ]
        ];
    }

    /**
     * Sub Sidebar Self Evaluation .
     */
    public static function indicatorSelfEvaluationSidebar($id)
    {
        return [
            'parent' => 'indikator-evaluasi-diri',
            'parent_sidebar' => 'masterSidebar.accreditation.indikator-evaluasi-diri',
            'items' => [
                [
                    'items' => [
                        [
                            'label' => self::getTitle('indikator_evaluasi_diri'), 'path' => 'indikator-evaluasi-diri/' . $id,
                            'permission' => 'indikator-evaluasi-diri'
                        ],
                        [
                            'label' => self::getTitle('indikator_evaluasi_diri/indikator_referensi'), 'path' => 'indikator-evaluasi-diri/' . $id . '/indikator-referensi',
                            'permission' => 'indikator-evaluasi-diri'
                        ],
                    ]
                ],
            ]
        ];
    }

    public static function PenilaianMatriksSidebar($id)
    {
        return [
            'parent' => 'penilaian-matriks-ikt',
            'parent_sidebar' => 'masterSidebar.accreditation.penilaian-matriks-ikt',
            'items' => [
                [
                    'items' => [
                        [
                            'label' => 'Data ' . self::getTitle('penilaian_matriks'), 'path' => 'penilaian-matriks/' . $id,
                            'permission' => 'penilaian-matriks'
                        ],
                        [
                            'label' => self::getTitle('penilaian_matriks/penilaian_matriks_predikat'), 'path' => 'penilaian-matriks/' . $id . '/penilaian-matriks-predikat',
                            'permission' => 'penilaian-matriks'
                        ],
                    ]
                ],
            ]
        ];
    }

    public static function PenilaianMatriksIktSidebar($id)
    {
        return [
            'parent' => 'penilaian-matriks-ikt',
            'parent_sidebar' => 'masterSidebar.accreditation.penilaian-matriks-ikt',
            'items' => [
                [
                    'items' => [
                        [
                            'label' => 'Data ' . self::getTitle('penilaian_matriks_ikt'), 'path' => 'penilaian-matriks-ikt/' . $id,
                            'permission' => 'penilaian-matriks-ikt'
                        ],
                        [
                            'label' => self::getTitle('penilaian_matriks_ikt/penilaian_matriks_predikat'), 'path' => 'penilaian-matriks-ikt/' . $id . '/penilaian-matriks-predikat',
                            'permission' => 'penilaian-matriks-ikt'
                        ],
                    ]
                ],
            ]
        ];
    }

    public static function accreditationSyncSidebar()
    {
        return [
            'parent' => 'accreditation-syncs',
            'parent_sidebar' => 'masterSidebar.accreditation.accreditation-syncs',
            'items' => [
                [
                    'items' => [
                        ['label' => 'Panduan Pengisian', 'path' => 'accreditation-syncs?filter=111'],
                        ['label' => 'P', 'path' => 'accreditation-syncs/accreditation-sync-details'],
                    ],
                ],
            ]
        ];
    }

    public static function mappingIndikatorButirSidebar($unitId, $periodId)
    {
        return [
            'label' => self::getLangRes('mapping_lkled.sidebar.title'),
            'parent' => 'mapping-indikator-butir',
            'parent_sidebar' => 'masterSidebar.accreditation.mapping-indikator-butir',
            'items' => [
                [
                    'items' => [
                        [
                            'label' => self::getLangRes('mapping_lkled.sidebar.lk'),
                            'path' => "mapping-indikator-butir/{$unitId}/{$periodId}",
                            'permission' => 'mapping-indikator-butir',
                        ],
                        [
                            'label' => self::getLangRes('mapping_lkled.sidebar.ed'),
                            'path' => "mapping-indikator-butir/{$unitId}/{$periodId}?tab=".AkreditasiBuku::SELF_EVALUATION,
                            'permission' => 'mapping-indikator-butir'
                        ]
                    ]
                ]
            ],
        ];
    }

    public static function mappingPenilaianMatriksSidebar($unitId, $periodId)
    {
        return [
            'label' => self::getLangRes('mapping-matriks-penilaian.sidebar.title'),
            'parent' => 'mapping-matriks-penilaian',
            'parent_sidebar' => 'masterSidebar.accreditation.mapping-matriks-penilaian',
            'items' => [
                [
                    'items' => [
                        [
                            'label' => self::getLangRes('mapping-matriks-penilaian.sidebar.mapping'),
                            'path' => "mapping-matriks-penilaian/{$unitId}/{$periodId}",
                            'permission' => 'mapping-matriks-penilaian',
                        ],
                    ]
                ]
            ],
        ];
    }

    public static function penilaianPanduanSidebar($id)
    {
        return [
            'parent' => 'penilaian-panduan',
            'parent_sidebar' => 'masterSidebar.accreditation.penilaian-panduan',
            'items' => [
                [
                    'items' => [
                        [
                            'label' => 'Data ' . self::getTitle('penilaian_panduan'), 'path' => 'penilaian-panduan/' . $id,
                            'permission' => 'penilaian-panduan'
                        ],
                    ]
                ],
                [
                    'items' => [
                        [
                            'label' => self::getTitle('penilaian_panduan/akreditasi_peringkat'), 'path' => 'penilaian-panduan/' . $id. '/akreditasi-peringkat',
                            'permission' => 'penilaian-panduan'
                        ],
                        [
                            'label' => self::getTitle('penilaian_panduan/akreditasi_status'), 'path' => 'penilaian-panduan/' . $id. '/akreditasi-status',
                            'permission' => 'penilaian-panduan'
                        ],
                        [
                            'label' => self::getTitle('penilaian_panduan/skor_matriks_predikat'), 'path' => 'penilaian-panduan/' . $id. '/skor-matriks-predikat',
                            'permission' => 'penilaian-panduan'
                        ],
                    ]
                ]
            ]
        ];
    }

    private static function getTitle(string $resource)
    {
        return __(Modul::CODE_SPMI . '::' . $resource . '.main');
    }

    private static function getLangRes(string $resource)
    {
        return __(Modul::CODE_SPMI . '::' . $resource);
    }
}
