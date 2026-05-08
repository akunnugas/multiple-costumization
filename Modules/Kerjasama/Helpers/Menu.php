<?php

namespace Modules\Kerjasama\Helpers;

use Modules\Gate\Models\Modul;

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
            ['label' => self::getTitle('dashboard'), 'path' => 'dashboard'],
            ['label' => self::getTitle('data_kerjasama'), 'path' => 'data-kerjasama'],
            ['label' => self::getTitle('kegiatan'), 'path' => 'kegiatan'],
            ['label' => self::getTitle('mitra'), 'path' => 'mitra'],
            [
                'label' => 'Data Referensi',
                'items' => [
                    ['label' => self::getTitle('bentuk_kegiatan'), 'path' => 'bentuk-kegiatan'],
                    ['label' => self::getTitle('sasaran_kinerja'), 'path' => 'sasaran-kinerja'],
                    ['label' => self::getTitle('kriteria_mitra'), 'path' => 'kriteria-mitra'],
                    ['label' => self::getTitle('sumber_dana'), 'path' => 'sumber-dana'],
                    ['label' => self::getTitle('jenis_dokumen'), 'path' => 'jenis-dokumen'],
                    ['label' => self::getTitle('unit_kerja'), 'path' => 'unit-kerja'],
                ]
            ],
            [
                'label' => 'Laporan',
                'items' => [
                    ['label' => self::getTitle('laporan_kerjasama'), 'path' => 'laporan-kerjasama'],
                ]
            ],
        ];
    }

    /**
     * Master sidebar.
     */
    public static function masterSidebar($key)
    {
        return match ($key) {
            'master' => [
                'items' => [
                    [
                        'label' => 'Data Referensi',
                        'items' => [
                            ['label' => self::getTitle('bentuk_kegiatan'), 'path' => 'bentuk-kegiatan'],
                            ['label' => self::getTitle('sasaran_kinerja'), 'path' => 'sasaran-kinerja'],
                            ['label' => self::getTitle('kriteria_mitra'), 'path' => 'kriteria-mitra'],
                            ['label' => self::getTitle('sumber_dana'), 'path' => 'sumber-dana'],
                            ['label' => self::getTitle('jenis_dokumen'), 'path' => 'jenis-dokumen'],
                            ['label' => self::getTitle('unit_kerja'), 'path' => 'unit-kerja']
                        ]
                    ],
                ]
            ],
            default => null
        };
    }

    /**
     * Sub menu kerjasama
     *
     * @param string $key
     * @param int|null $id
     * @return array[]
     */
    public static function kerjasamaSidebar(string $id = null, string $evaluasiId = null)
    {
        return [
            'parent' => 'data-kerjasama',
            'items' => [
                [
                    'label' => __('kerjasama::kerjasama.main'),
                    'items' => [
                        ['label' => 'Detail Kerjasama', 'path' => 'data-kerjasama', 'id' => $id],
                        ['label' => 'Daftar Kegiatan', 'path' => 'kegiatan-kerjasama', 'id' => $id],
                        [
                            'label' => 'Evaluasi Kerjasama', 
                            'path' => 'evaluasi-kerjasama', 
                            'id' => $id,
                            'items' => $evaluasiId ? [
                                ['label' => 'Evaluasi Kuesioner', 'path' => 'evaluasi-kuesioner', 'id' => $evaluasiId]
                            ] : []
                        ],
                    ]
                ],
            ]
        ];
    }

    /**
     * Sub menu kerjasama
     *
     * @param string $key
     * @param int|null $id
     * @return array[]
     */
    public static function mitraSidebar(string $id = null)
    {
        return [
            'parent' => 'mitra',
            'items' => [
                [
                    'label' => __('kerjasama::mitra.main'),
                    'items' => [
                        ['label' => 'Detail Mitra', 'path' => 'mitra', 'id' => $id],
                        ['label' => 'Daftar Kerjasama', 'path' => 'kerjasama-mitra', 'id' => $id],
                    ]
                ],
            ]
        ];
    }
    

    private static function getTitle(string $resource)
    {
        return __(Modul::CODE_KERJASAMA . '::' . $resource . '.main');
    }
}
