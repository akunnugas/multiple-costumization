<?php

namespace Modules\Litabmas\Helpers;

use Modules\Gate\Models\Modul;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Gate\Models\Role;

class Menu
{
    /**
     * Navbar atas.
     *
     * @return array
     */
    public static function navbar()
    {
        // cek scope user
        $userRole = auth()->user()?->kode_role;

        $isDosen = in_array($userRole, [Role::ROLE_DOSEN]);
        $isDosenEksternal = in_array($userRole, [Role::ROLE_DOSEN_EKSTERNAL]);

        if ($isDosen) {
            return self::navbarDosen();
        } else if ($isDosenEksternal) {
            return self::navbarDosenEksternal();
        } else {
            return self::navbarAdmin();
        }
    }

    /**
     * Navbar atas untuk dosen.
     */
    public static function navbarDosen()
    {
        return [
            ['label' => 'Beranda', 'path' => '/'],
            [
                'label' => 'Penelitian & Pengabdian',
                'items' => [
                    ['label' => 'Riwayat Penelitian', 'path' => 'pengajuan-pendanaan'],
                    ['label' => 'Riwayat Pengabdian', 'path' => 'pengajuan-pengabdian'],
                    ['label' => 'Pengajuan Akun Peneliti Eksternal', 'path' => 'usulan-dosen-eksternal'],
                ]
            ],
            ['label' => 'Penilaian PPM', 'path' => 'penilaian-reviewer'],
            ['label' => 'Bimbingan PPM', 'path' => 'bimbingan'],
            [
                'label' => 'Pengumuman',
                'items' => [
                    ['label' => 'Pengumuman', 'path' => 'pengumuman-pendanaan'],
                    ['label' => 'Klaster Pendanaan', 'path' => 'pengumuman-klaster'],
                ]
            ],
        ];
    }

    /**
     * Navbar atas untuk dosen eksternal.
     */
    public static function navbarDosenEksternal()
    {
        return [
            ['label' => 'Beranda', 'path' => '/'],
            [
                'label' => 'Penelitian & Pengabdian',
                'items' => [
                    ['label' => 'Riwayat Penelitian', 'path' => 'pengajuan-pendanaan'],
                    ['label' => 'Riwayat Pengabdian', 'path' => 'pengajuan-pengabdian'],
                ]
            ],
            ['label' => 'Penilaian PPM', 'path' => 'penilaian-reviewer'],
        ];
    }

    /**
     * Navbar atas untuk Admin.
     */
    public static function navbarAdmin()
    {
        return [
            ['label' => 'Beranda', 'path' => '/'],
            [
                'label' => 'Penelitian & Pengabdian',
                'items' => [
                    ['label' => 'Riwayat Penelitian', 'path' => 'pengajuan-pendanaan'],
                    ['label' => 'Riwayat Pengabdian', 'path' => 'pengajuan-pengabdian'],
                    ['label' => 'Pengajuan Akun Peneliti Eksternal', 'path' => 'usulan-dosen-eksternal'],
                ]
            ],
            [
                'label' => 'Pendanaan',
                'items' => [
                    ['label' => 'Setting Periode Pendanaan', 'path' => 'periode-pendanaan'],
                    ['label' => 'Sumber Pendanaan & Penentuan Tahapan Kegiatan', 'path' => 'sumber-pendanaan'],
                    ['label' => 'Komponen Proposal & Kriteria Penilaian', 'path' => 'aspek-penilaian-isian-proposal'],
                    ['label' => 'Klaster Pendanaan', 'path' => 'klaster-pendanaan'],
                    ['label' => 'Monitoring Pendanaan', 'path' => 'pendanaan-kegiatan'],
                ]
            ],
            [
                'label' => 'Data Referensi',
                'items' => [
                    ['label' => self::getTitle('agenda_kegiatan'), 'path' => 'agenda-kegiatan'],
                    ['label' => self::getTitle('bidang_ilmu'), 'path' => 'bidang-ilmu'],
                    ['label' => self::getTitle('tema_kegiatan'), 'path' => 'tema-kegiatan'],
                    ['label' => 'Hasil Akhir Kegiatan', 'path' => 'jenis-output-penelitian'],
                ]
            ],
            ['label' => 'Pengumuman', 'path' => 'pengumuman-pendanaan'],
            [
                'label' => 'Laporan',
                'items' => [
                    ['label' => 'Monitoring Pendanaan', 'path' => 'laporan-pendanaan-kegiatan'],
                    ['label' => 'Pengajuan Proposal', 'path' => 'laporan-pengajuan-proposal'],
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
            'master-aspek-penilaian' => [
                'parent' => 'aspek-penilaian-isian-proposal',
                'items' => [
                    [
                        'label' => 'Komponen Proposal & <br/> Kriteria Penilaian',
                        'items' => [
                            [
                                'label' => 'Komponen Proposal',
                                'path' => 'aspek-penilaian-isian-proposal'
                            ],
                            [
                                'label' => 'Kriteria Penilaian',
                                'path' => 'penilaian-komposisi-proposal'
                            ],
                            [
                                'label' => 'Kriteria Penilaian Presentasi',
                                'path' => 'penilaian-presentasi-proposal'
                            ],
                            [
                                'label' => 'Kriteria Penilaian Luaran',
                                'path' => 'aspek-penilaian-output'
                            ],
                        ]
                    ]
                ]
            ],
            default => null
        };
    }

    /**
     * Sub menu
     *
     * @param string $key
     * @param int|null $id
     * @return array[]
     */
    public static function sidebar(string $key, int $id = null)
    {
        return match ($key) {
            'pengajuan-pendanaan-admin' => [
                'parent' => 'pengajuan-pendanaan',
                'items' => [
                    [
                        'label' => 'Detail Proposal',
                        'items' => [
                            ['label' => 'Overview Proposal', 'path' => 'pengajuan-pendanaan-overview', 'id' => $id],
                        ]
                    ],
                    [
                        'label' => 'Seleksi Administrasi',
                        'items' => [
                            ['label' => 'Penilaian Similarity & AI', 'path' => 'pengajuan-pendanaan-similarity', 'id' => $id],
                            ['label' => 'Reviewer', 'path' => 'pengajuan-pendanaan-reviewer', 'id' => $id],
                        ]
                    ],
                    [
                        'label' => 'Seleksi Nominasi',
                        'items' => [
                            ['label' => 'Review Proposal', 'path' => 'pengajuan-pendanaan-revproposal', 'id' => $id],
                            ['label' => 'Jadwal Presentasi', 'path' => 'pengajuan-pendanaan-jadwalproposal', 'id' => $id],
                        ]
                    ],
                    [
                        'label' => 'Seleksi Pendanaan',
                        'items' => [
                            ['label' => 'Nilai Presentasi', 'path' => 'pengajuan-pendanaan-nilaipresensi', 'id' => $id],
                            ['label' => 'Pembimbing', 'path' => 'pengajuan-pendanaan-pembimbing', 'id' => $id],
                            ['label' => 'Laporan Antara', 'path' => 'pengajuan-pendanaan-progreport', 'id' => $id],
                            ['label' => 'Luaran Penelitian', 'path' => 'pengajuan-pendanaan-outputpenelitian', 'id' => $id],
                            ['label' => 'Publikasi', 'path' => 'pengajuan-pendanaan-publikasi', 'id' => $id],
                        ]
                    ]
                ]
            ],

            'pengajuan-pendanaan-dosen' => [
                'parent' => 'pengajuan-pendanaan',
                'items' => [
                    [
                        'label' => 'Detail Proposal',
                        'items' => [
                            ['label' => 'Overview Proposal', 'path' => 'pengajuan-pendanaan-overview', 'id' => $id],
                            ['label' => 'Review Proposal', 'path' => 'pengajuan-pendanaan-revproposal', 'id' => $id],
                            ['label' => 'Aktivitas Peneliti', 'path' => 'pengajuan-pendanaan-aktpenelitian', 'id' => $id],
                            ['label' => 'Laporan Antara', 'path' => 'pengajuan-pendanaan-progreport', 'id' => $id],
                            ['label' => 'Luaran Penelitian', 'path' => 'pengajuan-pendanaan-outputpenelitian', 'id' => $id],
                            // ['label' => 'Laporan & Keuangan', 'path' => 'pengajuan-pendanaan-laporanakhir', 'id' => $id],
                            ['label' => 'Publikasi', 'path' => 'pengajuan-pendanaan-publikasi', 'id' => $id],
                            ['label' => 'Summary', 'path' => 'pengajuan-pendanaan-summary', 'id' => $id],
                        ]
                    ]
                ]
            ],

            'pendanaan-kegiatan' => [
                'parent' => 'pendanaan-kegiatan',
                'items' => [
                    [
                        'label' => 'Detail Pendanaan',
                        'items' => [
                            ['label' => 'Detail Pendanaan', 'path' => 'pendanaan-kegiatan', 'id' => $id],
                        ]
                    ]
                ]
            ],

            'bimbingan' => [
                'parent' => 'bimbingan',
                'items' => [
                    [
                        'label' => 'Detail Bimbingan PPM',
                        'items' => [
                            ['label' => 'Aktivitas Peneliti', 'path' => 'bimbingan', 'id' => $id],
                        ]
                    ]
                ]
            ],

            'penilaian-reviewer-dosen' => [
                'parent' => 'penilaian-reviewer',
                'items' => [
                    [
                        'label' => 'Detail Proposal',
                        'items' => [
                            ['label' => 'Komponen Proposal', 'path' => 'penilaian-reviewer-overview', 'id' => $id],
                            ['label' => 'Kriteria Proposal', 'path' => 'penilaian-reviewer-penilaianaspek', 'id' => $id],
                            ['label' => 'Presentasi Proposal', 'path' => 'penilaian-reviewer-penilaianpresentasi', 'id' => $id],
                            ['label' => 'Laporan Antara', 'path' => 'penilaian-reviewer-progreport', 'id' => $id],
                            ['label' => 'Kriteria Luaran', 'path' => 'penilaian-reviewer-penilaianoutput', 'id' => $id],
                        ]
                    ],
                ]
            ],
        };
    }

    /**
     * Tab navigasi, tidak digunakan untuk menu tapi hanya untuk tab.
     * Biasanya digunakan untuk tab yang menempel pada tabel.
     *
     * @param $activeTab
     * @param int|null $id
     * @return array
     */
    public static function navTabs($activeTab, int $id = null)
    {
        return match ($activeTab) {
            'final-result-activity' => [
                'parent' => 'jenis-output-penelitian',
                'items' => [
                    ['label' => 'Luaran', 'path' => 'jenis-output-penelitian'],
                    ['label' => 'Outcome', 'path' => 'jenis-outcome-penelitian'],
                ]
            ],
            'penilaian-isian-proposal-by-jenis-pendanaan' => [
                'parent' => 'aspek-penilaian-isian-proposal',
                'items' => [
                    [
                        'label' => 'Penelitian',
                        'path' => 'aspek-penilaian-isian-proposal?tab=' . JenisPendanaanEnum::CODE_PENELITIAN,
                    ],
                    [
                        'label' => 'Pengabdian Masyarakat',
                        'path' => 'aspek-penilaian-isian-proposal?tab=' . JenisPendanaanEnum::CODE_PENGABDIAN
                    ],
                ]
            ],
            'penilaian-komposisi-proposal-by-jenis-pendanaan' => [
                'parent' => 'penilaian-komposisi-proposal',
                'items' => [
                    [
                        'label' => 'Penelitian',
                        'path' => 'penilaian-komposisi-proposal?tab=' . JenisPendanaanEnum::CODE_PENELITIAN,
                    ],
                    [
                        'label' => 'Pengabdian Masyarakat',
                        'path' => 'penilaian-komposisi-proposal?tab=' . JenisPendanaanEnum::CODE_PENGABDIAN
                    ],
                ]
            ],
            'penilaian-presentasi-proposal-by-jenis-pendanaan' => [
                'parent' => 'penilaian-presentasi-proposal',
                'items' => [
                    [
                        'label' => 'Penelitian',
                        'path' => 'penilaian-presentasi-proposal?tab=' . JenisPendanaanEnum::CODE_PENELITIAN,
                    ],
                    [
                        'label' => 'Pengabdian Masyarakat',
                        'path' => 'penilaian-presentasi-proposal?tab=' . JenisPendanaanEnum::CODE_PENGABDIAN
                    ],
                ]
            ],
            'pengajuan-pendanaan-by-jenis-pendanaan' => [
                'parent' => 'pengajuan-pendanaan',
                'items' => [
                    [
                        'label' => 'Penelitian',
                        'path' => 'pengajuan-pendanaan?tab=' . JenisPendanaanEnum::CODE_PENELITIAN,
                    ],
                    [
                        'label' => 'Pengabdian Masyarakat',
                        'path' => 'pengajuan-pendanaan?tab=' . JenisPendanaanEnum::CODE_PENGABDIAN
                    ],
                ]
            ],
            'penilaian-administrasi-by-jenis-pendanaan' => [
                'parent' => 'penilaian-administrasi',
                'items' => [
                    [
                        'label' => 'Penelitian',
                        'path' => 'penilaian-administrasi?tab=' . JenisPendanaanEnum::CODE_PENELITIAN,
                    ],
                    [
                        'label' => 'Pengabdian Masyarakat',
                        'path' => 'penilaian-administrasi?tab=' . JenisPendanaanEnum::CODE_PENGABDIAN
                    ],
                ]
            ],
            'penentuan-pendanaan-by-jenis-pendanaan' => [
                'parent' => 'penentuan-pendanaan',
                'items' => [
                    [
                        'label' => 'Penelitian',
                        'path' => 'penentuan-pendanaan?tab=' . JenisPendanaanEnum::CODE_PENELITIAN,
                    ],
                    [
                        'label' => 'Pengabdian Masyarakat',
                        'path' => 'penentuan-pendanaan?tab=' . JenisPendanaanEnum::CODE_PENGABDIAN
                    ],
                ]
            ],
            'daftar-bimbingan' => [
                'parent' => 'bimbingan',
                'items' => [
                    [
                        'label' => 'Penelitian',
                        'path' => 'bimbingan?tab=' . JenisPendanaanEnum::CODE_PENELITIAN,
                    ],
                    [
                        'label' => 'Pengabdian',
                        'path' => 'bimbingan?tab=' . JenisPendanaanEnum::CODE_PENGABDIAN
                    ],
                ]
            ],
            'penentuan-nominasi-by-jenis-pendanaan' => [
                'parent' => 'penentuan-nominasi',
                'items' => [
                    [
                        'label' => 'Penelitian',
                        'path' => 'penentuan-nominasi?tab=' . JenisPendanaanEnum::CODE_PENELITIAN,
                    ],
                    [
                        'label' => 'Pengabdian Masyarakat',
                        'path' => 'penentuan-nominasi?tab=' . JenisPendanaanEnum::CODE_PENGABDIAN
                    ],
                ]
            ],
            'riwayat-proposal-by-jenis-pendanaan' => [
                'parent' => 'riwayat-proposal',
                'items' => [
                    [
                        'label' => 'Penelitian',
                        'path' => 'riwayat-proposal?tab=' . JenisPendanaanEnum::CODE_PENELITIAN,
                    ],
                    [
                        'label' => 'Pengabdian Masyarakat',
                        'path' => 'riwayat-proposal?tab=' . JenisPendanaanEnum::CODE_PENGABDIAN
                    ],
                ]
            ],
            'publikasi-penelitian' => [
                'parent' => 'publikasi',
                'items' => [
                    [
                        'label' => 'Artikel',
                        'path' => 'publikasi?tab=artikel',
                    ],
                    [
                        'label' => 'Buku',
                        'path' => 'publikasi?tab=buku',
                    ]
                ]
            ],
            'pengajuan-pendanaan-publikasi-penelitian' => [
                'parent' => 'pengajuan-pendanaan',
                'items' => [
                    [
                        'label' => 'Artikel',
                        'path' => 'pengajuan-pendanaan/' . $id . '/publikasi-penelitian?tab=artikel',
                    ],
                    [
                        'label' => 'Buku',
                        'path' => 'pengajuan-pendanaan/' . $id . '/publikasi-penelitian?tab=buku',
                    ]
                ]
            ],
            'bimbingan-publikasi-penelitian' => [
                'parent' => 'publikasi-penelitian',
                'items' => [
                    [
                        'label' => 'Artikel',
                        'path' => 'bimbingan/' . $id . '/publikasi-penelitian?tab=artikel',
                    ],
                    [
                        'label' => 'Buku',
                        'path' => 'bimbingan/' . $id . '/publikasi-penelitian?tab=buku',
                    ]
                ]
            ]
        };
    }

    public static function getTitle(string $resource)
    {
        return __(Modul::CODE_LITABMAS . '::' . $resource . '.main');
    }
}
