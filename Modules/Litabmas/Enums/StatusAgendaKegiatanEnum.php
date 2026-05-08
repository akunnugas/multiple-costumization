<?php

namespace Modules\Litabmas\Enums;

use Modules\Litabmas\Models\AgendaKegiatan;

enum StatusAgendaKegiatanEnum: string
{
    /**
     * Status .
     * @var string
     */
    const DRAFT = 'draft';
    const KONFIRMASI_ANGGOTA = 'konfirmasi_anggota';
    const DIAJUKAN = 'diajukan';
    const PROSES_SELEKSI_ADMINISTRASI = 'proses_seleksi_administrasi';
    const LOLOS_ADMINISTRASI = 'lolos_administrasi';
    const TIDAK_LOLOS_ADMINISTRASI = 'tidak_lolos_administrasi';
    const REVIEW_PROPOSAL = 'review_proposal';
    const LOLOS_NOMINASI = 'lolos_nominasi';
    const TIDAK_LOLOS_NOMINASI = 'tidak_lolos_nominasi';
    const PRESENTASI_PROPOSAL = 'presentasi_proposal';
    const LOLOS_PENDANAAN = 'lolos_pendanaan';
    const TIDAK_LOLOS_PENDANAAN = 'tidak_lolos_pendanaan';
    const PELAKSANAAN_PENELITIAN = 'pelaksanaan_penelitian';
    const PENGUMPULAN_PROGRESS_REPORT = 'pengumpulan_progress_report';
    const PRESENTASI_PROGRESS_REPORT = 'presentasi_progress_report';
    const PENGUMPULAN_OUTPUT = 'pengumpulan_output';
    const PRESENTASI_OUTPUT = 'presentasi_output';
    const PENGUMPULAN_OUTCOME = 'pengumpulan_outcome';
    const OUTCOME_SELESAI = 'outcome_selesai';
    const OUTCOME_DIBLOKIR = 'outcome_diblokir';
    const STATUSES = [
        self::DRAFT => 'Draft',
        self::KONFIRMASI_ANGGOTA => 'Konfirmasi Anggota',
        self::DIAJUKAN => 'Diajukan',
        self::PROSES_SELEKSI_ADMINISTRASI => 'Proses Seleksi Administrasi',
        self::LOLOS_ADMINISTRASI => 'Lolos Administrasi',
        self::TIDAK_LOLOS_ADMINISTRASI => 'Tidak Lolos Administrasi',
        self::REVIEW_PROPOSAL => 'Review Proposal',
        self::LOLOS_NOMINASI => 'Lolos Nominasi',
        self::TIDAK_LOLOS_NOMINASI => 'Tidak Lolos Nominasi',
        self::PRESENTASI_PROPOSAL => 'Presentasi Proposal',
        self::LOLOS_PENDANAAN => 'Lolos Pendanaan',
        self::TIDAK_LOLOS_PENDANAAN => 'Tidak Lolos Pendanaan',
        self::PELAKSANAAN_PENELITIAN => 'Pelaksanaan Penelitian',
        self::PENGUMPULAN_PROGRESS_REPORT => 'Pengumpulan Progress Report',
        self::PRESENTASI_PROGRESS_REPORT => 'Presentasi Progress Report',
        self::PENGUMPULAN_OUTPUT => 'Pengumpulan Output',
        self::PRESENTASI_OUTPUT => 'Presentasi Output',
        self::PENGUMPULAN_OUTCOME => 'Pengumpulan Outcome',
        self::OUTCOME_SELESAI => 'Outcome Selesai',
        self::OUTCOME_DIBLOKIR => 'Outcome Diblokir',
    ];


    const STATUSES_LAPORAN = self::STATUSES + ['' => 'Belum ditentukan'];

    // Tahapan
    const ADMINISTRASI = 'administrasi';
    const NOMINASI = 'nominasi';
    const PENDANAAN = 'pendanaan';

    const FILTER_AGENDA = [
        self::ADMINISTRASI => 'Administrasi',
        self::NOMINASI => 'Nominasi',
        self::PENDANAAN => 'Pendanaan',
    ];

    // Status Seleksi
    const LOLOS = 'lolos';
    const TIDAK_LOLOS = 'tidak_lolos';
    const PROSES = 'proses';

    const FILTER_SELEKSI = [
        self::LOLOS => 'Lolos',
        self::TIDAK_LOLOS => 'Tidak Lolos',
        self::PROSES => 'Proses Seleksi',
    ];

    /**
     * Metode untuk mendapatkan text & variant berdasarkan status enum.
     * variant berdasarkan quantum.
     *
     * @param string $status
     * @return array
     */
    public static function getInfo(string $status): array
    {
        if (!empty(self::getStatuses()[$status])) {
            return self::getStatuses()[$status];
        } else {
            foreach (self::getStatuses() as $subStatus) {
                if (is_array($subStatus)) {
                    foreach ($subStatus as $key => $info) {
                        if ($key === $status) {
                            return $info;
                        }
                    }
                }
            }
        }

        return [
            'text' => 'Tidak Diketahui',
            'variant' => 'default'
        ];
    }

    /**
     * Metode untuk mendapatkan semua status.
     * Sekalian utk mendapatkan status memiliki sub status apa saja.
     *
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::DRAFT => [
                'text' => 'Draft',
                'variant' => 'default'
            ],
            AgendaKegiatan::STEP_PENDAFTARAN => [
                self::KONFIRMASI_ANGGOTA => [
                    'text' => 'Konfirmasi Anggota',
                    'variant' => 'primary'
                ],
                self::DIAJUKAN => [
                    'text' => 'Diajukan',
                    'variant' => 'primary'
                ]
            ],
            self::PROSES_SELEKSI_ADMINISTRASI => [
                'text' => 'Proses Seleksi Administrasi',
                'variant' => 'primary'
            ],
            AgendaKegiatan::STEP_PENGUMUMAN_ADMINISTRASI => [
                self::LOLOS_ADMINISTRASI => [
                    'text' => 'Lolos Administrasi',
                    'variant' => 'success'
                ],
                self::TIDAK_LOLOS_ADMINISTRASI => [
                    'text' => 'Tidak Lolos Administrasi',
                    'variant' => 'danger'
                ]
            ],
            self::REVIEW_PROPOSAL => [
                'text' => 'Review Proposal',
                'variant' => 'primary'
            ],
            AgendaKegiatan::STEP_PENGUMUMAN_NOMINASI => [
                self::LOLOS_NOMINASI => [
                    'text' => 'Lolos Nominasi',
                    'variant' => 'success'
                ],
                self::TIDAK_LOLOS_NOMINASI => [
                    'text' => 'Tidak Lolos Nominasi',
                    'variant' => 'danger'
                ]
            ],
            self::PRESENTASI_PROPOSAL => [
                'text' => 'Presentasi Proposal',
                'variant' => 'primary'
            ],
            AgendaKegiatan::STEP_PENGUMUMAN_PENDANAAN => [
                self::LOLOS_PENDANAAN => [
                    'text' => 'Lolos Pendanaan',
                    'variant' => 'success'
                ],
                self::TIDAK_LOLOS_PENDANAAN => [
                    'text' => 'Tidak Lolos Pendanaan',
                    'variant' => 'danger'
                ]
            ],
            self::PELAKSANAAN_PENELITIAN => [
                'text' => 'Pelaksanaan Penelitian',
                'variant' => 'primary'
            ],
            self::PENGUMPULAN_PROGRESS_REPORT => [
                'text' => 'Pengumpulan Progress Report',
                'variant' => 'primary'
            ],
            self::PRESENTASI_PROGRESS_REPORT => [
                'text' => 'Presentasi Progress Report',
                'variant' => 'primary'
            ],
            self::PENGUMPULAN_OUTPUT => [
                'text' => 'Pengumpulan Output',
                'variant' => 'primary'
            ],
            self::PRESENTASI_OUTPUT => [
                'text' => 'Presentasi Output',
                'variant' => 'primary'
            ],
            AgendaKegiatan::STEP_PENGUMPULAN_HASIL => [
                self::PENGUMPULAN_OUTCOME => [
                    'text' => 'Pengumpulan Outcome',
                    'variant' => 'primary'
                ],
                self::OUTCOME_SELESAI => [
                    'text' => 'Outcome Selesai',
                    'variant' => 'success'
                ],
                self::OUTCOME_DIBLOKIR => [
                    'text' => 'Outcome Diblokir',
                    'variant' => 'danger'
                ],
            ],
        ];
    }
}
