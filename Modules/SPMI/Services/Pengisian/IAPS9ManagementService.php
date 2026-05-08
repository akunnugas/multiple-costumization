<?php

namespace Modules\SPMI\Services\Pengisian;

use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\DataPengisianLK;

class IAPS9ManagementService extends PengisianService
{
    /**
     * Get 1a
     * @param $table_key
     * @param $id
     * return array
     */
    public function get1a($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Lembaga',
                'input' => 'textarea',
            ],
            [
                'label' => 'Tingkat <sup>1)</sup>',
                'childs' => [
                    [
                        'label' => 'Internasional',
                        'input' => 'checkbox|#jenis_kerjasama',
                    ],
                    [
                        'label' => 'Nasional',
                        'input' => 'checkbox|#jenis_kerjasama',
                    ],
                    [
                        'label' => 'Wilayah',
                        'input' => 'checkbox|#jenis_kerjasama',
                    ],
                ]
            ],
            [
                'label' => 'Judul Kegiatan Kerjasama <sup>2)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Manfaat bagi PS yang Diakreditasi',
                'input' => 'textarea',
            ],
            [
                'label' => 'Waktu dan Durasi',
                'input' => 'textarea',
            ],
            [
                'label' => 'Bukti Kerjasama <sup>3)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Tahun Berakhirnya Kerjasama',
                'input' => 'input|number|max_length:4',
            ],
        ];

        $row = [
            'Pendidikan' => [
                'data_index' => 1,
                '_footer_' => [
                    'label' => 'Σ',
                    'function' => 'COUNT',
                    'indexes' => [2, 3, 4, 5]
                ]
            ],
            'Penelitian' => [
                'data_index' => 2,
                '_footer_' => [
                    'label' => 'Σ',
                    'function' => 'COUNT',
                    'indexes' => [2, 3, 4, 5]
                ]
            ],
            'Pengabdian Kepada Masyarakat' => [
                'data_index' => 3,
                '_footer_' => [
                    'label' => 'Σ',
                    'function' => 'COUNT',
                    'indexes' => [2, 3, 4, 5]
                ]
            ]
        ];

        $footer = [];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    public function get2a($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun Akademik',
                'input' => 'readonly',
            ],
            [
                'label' => 'Daya Tampung',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah Calon Mahasiswa',
                'childs' => [
                    [
                        'label' => 'Pendaftar',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'Lulus Seleksi',
                        'input' => 'input|number',
                    ],
                ]
            ],
            [
                'label' => 'Jumlah Mahasiswa Baru',
                'childs' => [
                    [
                        'label' => 'Reguler',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'Transfer',
                        'input' => 'input|number',
                    ],
                ]
            ],
            [
                'label' => 'Jumlah Mahasiswa Aktif',
                'childs' => [
                    [
                        'label' => 'Reguler',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'Transfer',
                        'input' => 'input|number',
                    ],
                ]
            ],
        ];

        $row = [
            $this->tsAudit->TS4 => [
                'data_index' => 1,
            ],
            $this->tsAudit->TS3 => [
                'data_index' => 2,

            ],
            $this->tsAudit->TS2 => [
                'data_index' => 3,
            ],
            $this->tsAudit->TS1 => [
                'data_index' => 4,

            ],
            $this->tsAudit->TS0 => [
                'data_index' => 5,
                'data_labels' => [
                    7 => 'N<sub>RTS</sub>',
                    8 => 'N<sub>TTS</sub>',
                ]
            ],
        ];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [3, 4, 5, 6, 7],
                'indexes_label' => [
                    3 => 'N<sub>A</sub>',
                    4 => 'N<sub>B</sub>',
                    5 => 'N<sub>C</sub>',
                    6 => 'N<sub>D</sub>',
                    7 => 'N<sub>M</sub> (N<sub>RTS</sub> + N<sub>TTS</sub>)',
                ],
                'indexes_function' => [
                    7 => [
                        'function' => 'SUM',
                        'cell' => [
                            [5, 7],
                            [5, 8]
                        ]
                    ]
                ]
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasNumber = false;
        $this->isHasAction = false;

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get 2b
     * @param $table_key
     * @param $id
     * return array
     */
    public function get2b($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Program Studi',
                'input' => 'select',
                'options' => UnitKerja::where('jenis_unit', UnitKerja::STUDY_PROGRAM)->pluck('nama_unit', 'id')->toArray(),
            ],
            [
                'label' => 'Jumlah Mahasiswa Aktif',
                'childs' => [
                    [
                        'label' => $this->tsAudit->TS2,
                        'input' => 'input|number',
                    ],
                    [
                        'label' => $this->tsAudit->TS1,
                        'input' => 'input|number',
                    ],
                    [
                        'label' => $this->tsAudit->TS0,
                        'input' => 'input|number',
                    ],
                ]
            ],
            [
                'label' => 'Jumlah Mahasiswa Asing Penuh Waktu (Full-time)',
                'childs' => [
                    [
                        'label' => $this->tsAudit->TS2,
                        'input' => 'input|number',
                    ],
                    [
                        'label' => $this->tsAudit->TS1,
                        'input' => 'input|number',
                    ],
                    [
                        'label' => $this->tsAudit->TS0,
                        'input' => 'input|number',
                    ],
                ]
            ],
            [
                'label' => 'Jumlah Mahasiswa Asing Paruh Waktu (Part-time)',
                'childs' => [
                    [
                        'label' => $this->tsAudit->TS2,
                        'input' => 'input|number',
                    ],
                    [
                        'label' => $this->tsAudit->TS1,
                        'input' => 'input|number',
                    ],
                    [
                        'label' => $this->tsAudit->TS0,
                        'input' => 'input|number',
                    ],
                ]
            ],
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [3, 4, 5, 6, 7, 8, 9, 10, 11]
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get3a1
     * @param $table_key
     * @param $id
     * return array
     */
    public function get3a1($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Dosen',
                'input' => 'select',
                'lazy_load' => true,
                'api_url' => '/v2/spmi/search-dosen',
            ],
            [
                'label' => 'NIDN/NIDK',
                'input' => 'input|number',
            ],
            [
                'label' => 'Pendidikan Pasca Sarjana',
                'childs' => [
                    [
                        'label' => 'Magister/ Magister Terapan/ Spesialis 1',
                        'input' => 'textarea',
                    ],
                    [
                        'label' => 'Doktor/ Doktor Terapan/ Spesialis 2',
                        'input' => 'textarea',
                    ],
                ]
            ],
            [
                'label' => 'Bidang Keahlian',
                'input' => 'textarea',
            ],
            [
                'label' => 'Kesesuaian dengan Kompetensi Inti PS',
                'input' => 'checkbox',
            ],
            [
                'label' => 'Jabatan Akademik',
                'input' => 'select',
                'options' => ['Tenaga Pengajar' => 'Tenaga Pengajar', 'Asisten Ahli' => 'Asisten Ahli', 'Lektor' => 'Lektor', 'Lektor Kepala' => 'Lektor Kepala', 'Guru Besar' => 'Guru Besar'],
            ],
            [
                'label' => 'Sertifikat Pendidik Profesional',
                'input' => 'textarea',
            ],
            [
                'label' => 'Sertifikat Kompetensi/ Profesi/ Industri',
                'input' => 'textarea',
            ],
            [
                'label' => 'Mata Kuliah yang Diampu pada PS yang Diakreditasi',
                'input' => 'textarea',
            ],
            [
                'label' => 'Kesesuaian Bidang Keahlian dengan Mata Kuliah yang Diampu',
                'input' => 'checkbox',
            ],
            [
                'label' => 'Mata Kuliah yang Diampu pada PS Lain',
                'input' => 'textarea',
            ],
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Σ',
                'function' => 'COUNT',
                'indexes' => [2, 7, 9, 10, 12],
                'indexes_label' => [
                    2 => 'NDT',
                    7 => 'NDTPS',
                ]
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get3a2
     * @param $table_key
     * @param $id
     * return array
     */
    public function get3a2($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Dosen',
                'input' => 'select',
                'lazy_load' => true,
                'api_url' => '/v2/spmi/search-dosen',
            ],
            [
                'label' => 'Jumlah Mahasiswa yang Dibimbing',
                'childs' => [
                    [
                        'label' => 'pada PS yang Diakreditasi',
                        'childs' => [
                            [
                                'label' => $this->tsAudit->TS2,
                                'input' => 'input|number',
                            ],
                            [
                                'label' => $this->tsAudit->TS1,
                                'input' => 'input|number',
                            ],
                            [
                                'label' => $this->tsAudit->TS0,
                                'input' => 'input|number',
                            ],
                            [
                                'label' => 'Rata-Rata',
                                'function' => 'AVERAGE',
                                'indexes' => [3, 4, 5],
                            ]
                        ]
                    ],
                    [
                        'label' => 'pada Program yang sama di PT',
                        'childs' => [
                            [
                                'label' => $this->tsAudit->TS2,
                                'input' => 'input|number',
                            ],
                            [
                                'label' => $this->tsAudit->TS1,
                                'input' => 'input|number',
                            ],
                            [
                                'label' => $this->tsAudit->TS0,
                                'input' => 'input|number',
                            ],
                            [
                                'label' => 'Rata-Rata',
                                'function' => 'AVERAGE',
                                'indexes' => [7, 8, 9],
                            ]
                        ]
                    ]
                ]
            ],
            [
                'label' => 'Rata-Rata Jumlah Bimbingan di seluruh Program/ Semester',
                'function' => 'AVERAGE',
                'indexes' => [3, 4, 5, 7, 8, 9],
            ],
        ];

        $row = [];

        $footer = [];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get3a3
     * @param $table_key
     * @param $id
     * return array
     */
    public function get3a3($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Dosen (DT)',
                'input' => 'select',
                'lazy_load' => true,
                'api_url' => '/v2/spmi/search-dosen',
            ],
            [
                'label' => 'DTPS',
                'input' => 'checkbox',
            ],
            [
                'label' => 'Ekuivalen Waktu Mengajar Penuh (EWMP) pada saat TS dalam satuan kredit semester (sks)',
                'childs' => [
                    [
                        'label' => 'Pendidikan : Pembelajaran dan Pembimbingan',
                        'childs' => [
                            [
                                'label' => 'PS yang Diakreditasi',
                                'input' => 'input|decimal',
                            ],
                            [
                                'label' => 'PS Lain di dalam PT',
                                'input' => 'input|decimal',
                            ],
                            [
                                'label' => 'PS Lain di luar PT',
                                'input' => 'input|decimal',
                            ],
                        ]
                    ],
                    [
                        'label' => 'Penelitian',
                        'input' => 'input|decimal',
                    ],
                    [
                        'label' => 'PkM',
                        'input' => 'input|decimal',
                    ],
                    [
                        'label' => 'Tugas Tambahan dan/atau Penunjang',
                        'input' => 'input|decimal',
                    ],
                ]
            ],
            [
                'label' => 'Jumlah (sks)',
                'function' => 'SUM',
                'indexes' => [4, 5, 6, 7, 8, 9],
            ],
            [
                'label' => 'Rata-rata per Semester (sks)',
                'function' => 'SUM',
                'divides' => 2,
                'indexes' => [4, 5, 6, 7, 8, 9],
            ],
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Rata-rata DT',
                'function' => 'AVERAGE',
                'indexes' => [10, 11],
                'colspan' => 9
            ],
            [
                'label' => 'Rata-rata DTPS',
                'function' => 'AVERAGE',
                'indexes' => [10, 11],
                'condition' => [3 => 1],
                'colspan' => 9
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get3a4
     * @param $table_key
     * @param $id
     * return array
     */
    public function get3a4($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Dosen',
                'input' => 'select',
                'lazy_load' => true,
                'api_url' => '/v2/spmi/search-dosen',
            ],
            [
                'label' => 'NIDN/NIDK <sup>1)</sup>',
                'input' => 'input|number',
            ],
            [
                'label' => 'Pendidikan Pasca Sarjana <sup>2)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Bidang Keahlian <sup>3)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Jabatan Akademik',
                'input' => 'select',
                'options' => ['Tenaga Pengajar' => 'Tenaga Pengajar', 'Asisten Ahli' => 'Asisten Ahli', 'Lektor' => 'Lektor', 'Lektor Kepala' => 'Lektor Kepala', 'Guru Besar' => 'Guru Besar'],
            ],
            [
                'label' => 'Sertifikat Pendidik Profesional <sup>4)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Sertifikat Kompetensi/ Profesi/ Industri <sup>5)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Mata Kuliah yang Diampu pada PS yang Diakreditasi <sup>6)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Kesesuaian Bidang Keahlian dengan Mata Kuliah yang Diampu <sup>7)</sup>',
                'input' => 'checkbox',
            ],
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Σ',
                'function' => 'COUNT',
                'indexes' => [2, 7, 8, 10],
                'indexes_label' => [
                    2 => 'NDTT'
                ]
            ],
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get3a5
     * @param $table_key
     * @param $id
     * return array
     */
    public function get3a5($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Dosen Industri/ Praktisi',
                'input' => 'select',
                'lazy_load' => true,
                'api_url' => '/v2/spmi/search-dosen',
            ],
            [
                'label' => 'NIDK',
                'input' => 'input|number',
            ],
            [
                'label' => 'Perusahaan/ Industri',
                'input' => 'textarea',
            ],
            [
                'label' => 'Pendidikan Tertinggi',
                'input' => 'textarea',
            ],
            [
                'label' => 'Bidang Keahlian',
                'input' => 'textarea',
            ],
            [
                'label' => 'Sertifikat Profesi/ Kompetensi/ Industri',
                'input' => 'textarea',
            ],
            [
                'label' => 'Mata Kuliah yang Diampu',
                'input' => 'textarea',
            ],
            [
                'label' => 'Bobot Kredit (SKS)',
                'input' => 'input|number',
            ],
        ];

        $row = [];

        $footer = [];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get3b1
     * @param $table_key
     * @param $id
     * return array
     */
    public function get3b1($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Dosen',
                'input' => 'select',
                'lazy_load' => true,
                'api_url' => '/v2/spmi/search-dosen',
            ],
            [
                'label' => 'Bidang Keahlian',
                'input' => 'textarea',
            ],
            [
                'label' => 'Rekognisi dan Bukti Pendukung',
                'input' => 'textarea',
            ],
            [
                'label' => 'Tingkat',
                'childs' => [
                    [
                        'label' => 'Wilayah',
                        'input' => 'checkbox|#tingkat_wilayah',
                    ],
                    [
                        'label' => 'Nasional',
                        'input' => 'checkbox|#tingkat_wilayah',
                    ],
                    [
                        'label' => 'Internasional',
                        'input' => 'checkbox|#tingkat_wilayah',
                    ],
                ]
            ],
            [
                'label' => 'Tahun',
                'input' => 'input|number|max_length:4',
            ],
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'COUNT',
                'indexes' => [5, 6, 7],
                'colspan' => 4
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get3b2
     * @param $table_key
     * @param $id
     * return array
     */
    public function get3b2($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Sumber Pembiayaan',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah Judul',
                'childs' => [
                    [
                        'label' => $this->tsAudit->TS2,
                        'input' => 'input|number',
                    ],
                    [
                        'label' => $this->tsAudit->TS1,
                        'input' => 'input|number',
                    ],
                    [
                        'label' => $this->tsAudit->TS0,
                        'input' => 'input|number',
                    ],
                ]
            ],
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [3, 4, 5]
            ],
        ];

        $row = [
            'a) Perguruan Tinggi <br>b) Mandiri 1)' => [
                'data_index' => 1,
            ],
            'Lembaga Dalam Negeri (di luar PT)' => [
                'data_index' => 2,
            ],
            'Lembaga Luar Negeri' => [
                'data_index' => 3,
            ]
        ];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [3, 4, 5, 6],
                'colspan' => 2
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasAction = false;

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get3b3
     * @param $table_key
     * @param $id
     * return array
     */
    public function get3b3($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Sumber Pembiayaan',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah Judul',
                'childs' => [
                    [
                        'label' => $this->tsAudit->TS2,
                        'input' => 'input|number',
                    ],
                    [
                        'label' => $this->tsAudit->TS1,
                        'input' => 'input|number',
                    ],
                    [
                        'label' => $this->tsAudit->TS0,
                        'input' => 'input|number',
                    ],
                ]
            ],
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [3, 4, 5]
            ],
        ];

        $table_row = [
            'a) Perguruan Tinggi <br>b) Mandiri 1)' => [
                'data_index' => 1,
            ],
            'Lembaga Dalam Negeri (di luar PT)' => [
                'data_index' => 2,
            ],
            'Lembaga Luar Negeri' => [
                'data_index' => 3,
            ]
        ];

        $table_footer = [
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [3, 4, 5, 6],
                'colspan' => 2
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $table_data = [];
        if ($data_raw) {
            $table_data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasAction = false;

        return $this->buildTable($table, $table_row, $table_footer, $table_data);
    }

    /**
     * Get get3b4a
     * @param $table_key
     * @param $id
     * return array
     */
    public function get3b4a($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Media Publikasi',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah Judul',
                'childs' => [
                    [
                        'label' => $this->tsAudit->TS2,
                        'input' => 'input|number',
                    ],
                    [
                        'label' => $this->tsAudit->TS1,
                        'input' => 'input|number',
                    ],
                    [
                        'label' => $this->tsAudit->TS0,
                        'input' => 'input|number',
                    ],
                ]
            ],
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [3, 4, 5]
            ],
        ];

        $row = [
            'Jurnal nasional tidak terakreditasi' => [
                'data_index' => 1,
                'data_labels' => [
                    6 => 'N<sub>A1</sub>',
                ]
            ],
            'Jurnal nasional terakreditasi' => [
                'data_index' => 2,
                'data_labels' => [
                    6 => 'N<sub>A2</sub>',
                ]
            ],
            'Jurnal internasional' => [
                'data_index' => 3,
                'data_labels' => [
                    6 => 'N<sub>A3</sub>',
                ]
            ],
            'Jurnal internasional bereputasi' => [
                'data_index' => 4,
                'data_labels' => [
                    6 => 'N<sub>A4</sub>',
                ]
            ],
            'Seminar wilayah/lokal/perguruan tinggi' => [
                'data_index' => 5,
                'data_labels' => [
                    6 => 'N<sub>B1</sub>',
                ]
            ],
            'Seminar nasional' => [
                'data_index' => 6,
                'data_labels' => [
                    6 => 'N<sub>B2</sub>',
                ]
            ],
            'Seminar internasional' => [
                'data_index' => 7,
                'data_labels' => [
                    6 => 'N<sub>B3</sub>',
                ]
            ],
            'Tulisan di media massa wilayah' => [
                'data_index' => 8,
                'data_labels' => [
                    6 => 'N<sub>C1</sub>',
                ]
            ],
            'Tulisan di media massa nasional' => [
                'data_index' => 9,
                'data_labels' => [
                    6 => 'N<sub>C2</sub>',
                ]
            ],
            'Tulisan di media massa internasional' => [
                'data_index' => 10,
                'data_labels' => [
                    6 => 'N<sub>C3</sub>',
                ]
            ]
        ];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [3, 4, 5, 6],
                'colspan' => 2
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasAction = false;

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get3b4b
     * @param $table_key
     * @param $id
     * return array
     */
    public function get3b4b($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Media Publikasi',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah Judul',
                'childs' => [
                    [
                        'label' => $this->tsAudit->TS2,
                        'input' => 'input|number',
                    ],
                    [
                        'label' => $this->tsAudit->TS1,
                        'input' => 'input|number',
                    ],
                    [
                        'label' => $this->tsAudit->TS0,
                        'input' => 'input|number',
                    ],
                ]
            ],
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [3, 4, 5]
            ],
        ];

        $row = [
            'Publikasi di jurnal nasional tidak terakreditasi' => [
                'data_index' => 1,
                'data_labels' => [
                    6 => 'N<sub>A1</sub>',
                ]
            ],
            'Jurnal penelitian nasional terakreditasi' => [
                'data_index' => 2,
                'data_labels' => [
                    6 => 'N<sub>A2</sub>',
                ]
            ],
            'Jurnal penelitian internasional' => [
                'data_index' => 3,
                'data_labels' => [
                    6 => 'N<sub>A3</sub>',
                ]
            ],
            'Jurnal penelitian internasional bereputasi' => [
                'data_index' => 4,
                'data_labels' => [
                    6 => 'N<sub>A4</sub>',
                ]
            ],
            'Publikasi di seminar wilayah/lokal/perguruan tinggi' => [
                'data_index' => 5,
                'data_labels' => [
                    6 => 'N<sub>B1</sub>',
                ]
            ],
            'Publikasi di seminar nasional' => [
                'data_index' => 6,
                'data_labels' => [
                    6 => 'N<sub>B2</sub>',
                ]
            ],
            'Publikasi di seminar internasional' => [
                'data_index' => 7,
                'data_labels' => [
                    6 => 'N<sub>B3</sub>',
                ]
            ],
            'Pagelaran/pameran/presentasi dalam forum di tingkat wilayah' => [
                'data_index' => 8,
                'data_labels' => [
                    6 => 'N<sub>C1</sub>',
                ]
            ],
            'Pagelaran/pameran/presentasi dalam forum di tingkat nasional' => [
                'data_index' => 9,
                'data_labels' => [
                    6 => 'N<sub>C2</sub>',
                ]
            ],
            'Pagelaran/pameran/presentasi dalam forum di tingkat internasional' => [
                'data_index' => 10,
                'data_labels' => [
                    6 => 'N<sub>C3</sub>',
                ]
            ]
        ];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [3, 4, 5, 6],
                'colspan' => 2
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasAction = false;

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get3b5
     * @param $table_key
     * @param $id
     * return array
     */
    public function get3b5($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Dosen',
                'input' => 'select',
                'lazy_load' => true,
                'api_url' => '/v2/spmi/search-dosen',
            ],
            [
                'label' => 'Judul Artikel yang Disitasi (Jurnal/Buku, Volume, Tahun, Nomor, Halaman)',
                'input' => 'textarea',
            ],
            [
                'label' => 'Jumlah Sitasi',
                'input' => 'input|number',
            ],
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'COUNT',
                'indexes' => [3, 4],
                'colspan' => 2,
                'indexes_function' => [
                    4 => [
                        'function' => 'SUM',
                        'is_static' => false,
                        'cell' => [
                            [4]
                        ]
                    ]
                ]
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get3b6
     * @param $table_key
     * @param $id
     * return array
     */
    public function get3b6($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Dosen',
                'input' => 'select',
                'lazy_load' => true,
                'api_url' => '/v2/spmi/search-dosen',
            ],
            [
                'label' => 'Nama Produk/Jasa',
                'input' => 'textarea',
            ],
            [
                'label' => 'Deskripsi Produk/Jasa',
                'input' => 'textarea',
            ],
            [
                'label' => 'Bukti',
                'input' => 'textarea',
            ],
            [
                'label' => 'Tahun',
                'input' => 'input|number|max_length:4',
            ],
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'COUNT',
                'indexes' => [3],
                'colspan' => 2
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get3b7_1
     * @param $table_key
     * @param $id
     * return array
     */
    public function get3b7_1($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Luaran Penelitian dan PkM',
                'input' => 'textarea',
            ],
            [
                'label' => 'Tahun',
                'input' => 'input|number|max_length:4',
            ],
            [
                'label' => 'Keterangan',
                'input' => 'textarea',
            ],
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'COUNT',
                'indexes' => [3],
                'colspan' => 2,
                'indexes_label' => [
                    3 => 'N<sub>A</sub>',
                ]

            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get3b7_2
     * @param $table_key
     * @param $id
     * return array
     */
    public function get3b7_2($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Luaran Penelitian dan PkM',
                'input' => 'textarea',
            ],
            [
                'label' => 'Tahun',
                'input' => 'input|number|max_length:4',
            ],
            [
                'label' => 'Keterangan',
                'input' => 'textarea',
            ],
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'COUNT',
                'indexes' => [3],
                'colspan' => 2,
                'indexes_label' => [
                    3 => 'N<sub>B</sub>',
                ]
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get3b7_3
     * @param $table_key
     * @param $id
     * return array
     */
    public function get3b7_3($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Luaran Penelitian dan PkM',
                'input' => 'textarea',
            ],
            [
                'label' => 'Tahun',
                'input' => 'input|number|max_length:4',
            ],
            [
                'label' => 'Keterangan',
                'input' => 'textarea',
            ],
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'COUNT',
                'indexes' => [3],
                'colspan' => 2,
                'indexes_label' => [
                    3 => 'N<sub>C</sub>',
                ]
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get3b7_4
     * @param $table_key
     * @param $id
     * return array
     */
    public function get3b7_4($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Luaran Penelitian dan PkM',
                'input' => 'textarea',
            ],
            [
                'label' => 'Tahun',
                'input' => 'input|number|max_length:4',
            ],
            [
                'label' => 'Keterangan',
                'input' => 'textarea',
            ],
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'COUNT',
                'indexes' => [3],
                'colspan' => 2,
                'indexes_label' => [
                    3 => 'N<sub>D</sub>',
                ]
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get4
     * @param $table_key
     * @param $id
     * return array
     */
    public function get4($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Jenis Penggunaan',
                'input' => 'readonly',
            ],
            [
                'label' => 'Unit Pengelola Program Studi (Rupiah)',
                'childs' => [
                    [
                        'label' => $this->tsAudit->TS2,
                        'input' => 'input|number|currency,min:0',
                    ],
                    [
                        'label' => $this->tsAudit->TS1,
                        'input' => 'input|number|currency,min:0',
                    ],
                    [
                        'label' => $this->tsAudit->TS0,
                        'input' => 'input|number|currency,min:0',
                    ],
                    [
                        'label' => 'Rata-rata',
                        'function' => 'AVERAGE',
                        'type' => 'currency',
                        'indexes' => [3, 4, 5],
                    ],
                ]
            ],
            [
                'label' => 'Program Studi (Rupiah)',
                'childs' => [
                    [
                        'label' => $this->tsAudit->TS2,
                        'input' => 'input|number|currency,min:0',
                    ],
                    [
                        'label' => $this->tsAudit->TS1,
                        'input' => 'input|number|currency,min:0',
                    ],
                    [
                        'label' => $this->tsAudit->TS0,
                        'input' => 'input|number|currency,min:0',
                    ],
                    [
                        'label' => 'Rata-rata',
                        'function' => 'AVERAGE',
                        'type' => 'currency',
                        'indexes' => [7, 8, 9]
                    ],
                ]
            ],
        ];

        $table_row = [
            'Biaya Operasional Pendidikan' => [
                'data_index' => 1,
                'readonly' => true,
                'childs' => [
                    'Biaya Dosen (Gaji, Honor)',
                    'Biaya Tenaga Kependidikan (Gaji, Honor)',
                    'Biaya Operasional Pembelajaran (Bahan dan Peralatan Habis Pakai)',
                    'Biaya Operasional Tidak Langsung (Listrik, Gas, Air, Pemeliharaan Gedung, Pemeliharaan Sarana, Uang Lembur, Telekomunikasi, Konsumsi, Transport Lokal, Pajak, Asuransi, dll)',
                    'index' => 'Biaya operasional kemahasiswaan (penalaran, minat, bakat, bimbingan karir, dan kesejahteraan)'
                ],
                '_footer_' => [
                    'label' => 'Jumlah',
                    'function' => 'SUM',
                    'type' => 'currency',
                    'indexes' => [3, 4, 5, 6, 7, 8, 9, 10]
                ]

            ],
            'Biaya Penelitian' => [
                'data_index' => 7,
                'childs' => [
                    'index' => 'Biaya PkM',
                ],
                '_footer_' => [
                    'label' => 'Jumlah',
                    'function' => 'SUM',
                    'type' => 'currency',
                    'indexes' => [3, 4, 5, 6, 7, 8, 9, 10]
                ]
            ],
            'Biaya Investasi SDM' => [
                'data_index' => 9,
                'childs' => [
                    'index:1' => 'Biaya Investasi Sarana',
                    'index:2' => 'Biaya Investasi Prasarana',
                ],
                '_footer_' => [
                    'label' => 'Jumlah',
                    'function' => 'SUM',
                    'type' => 'currency',
                    'indexes' => [3, 4, 5, 6, 7, 8, 9, 10]
                ]
            ],
        ];

        $footer = [
            [
                'label' => 'Total',
                'function' => 'SUM',
                'type' => 'currency',
                'indexes' => [3, 4, 5, 6, 7, 8, 9, 10],
                'colspan' => 2
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasAction = false;

        return $this->buildTable($table, $table_row, $footer, $data);
    }

    /**
     * Get get5a
     * @param $table_key
     * @param $id
     * return array
     */
    public function get5a($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Semester',
                'input' => 'input|number',
            ],
            [
                'label' => 'Kode Mata Kuliah',
                'input' => 'textarea',
            ],
            [
                'label' => 'Nama Mata Kuliah',
                'input' => 'textarea',
            ],
            [
                'label' => 'Mata Kuliah Kompetensi <sup>1)</sup>',
                'input' => 'checkbox',
            ],
            [
                'label' => 'Bobot Kredit (SKS)',
                'childs' => [
                    [
                        'label' => 'Kuliah/ Proposal/ Tutorial',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'Seminar',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'Praktikum/ Praktik/ Praktik Lapangan',
                        'input' => 'input|number',
                    ]
                ]
            ],
            [
                'label' => 'Konversi Kredit ke Jam <sup>2)</sup>',
                'input' => 'input|number',
            ],
            [
                'label' => 'Capaian Pembelajaran <sup>3)</sup>',
                'childs' => [
                    [
                        'label' => 'Sikap',
                        'input' => 'checkbox',
                    ],
                    [
                        'label' => 'Pengetahuan',
                        'input' => 'checkbox',
                    ],
                    [
                        'label' => 'Keterampilan Umum',
                        'input' => 'checkbox',
                    ],
                    [
                        'label' => 'Keterampilan Khusus',
                        'input' => 'checkbox',
                    ]
                ]
            ],
            [
                'label' => 'Dokumen Rencana Pembelajaran <sup>4)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Unit Penyelenggara',
                'input' => 'textarea',
            ]
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [5, 6, 7, 8, 9],
                'colspan' => 4
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get5b
     * @param $table_key
     * @param $id
     * return array
     */
    public function get5b($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Judul Penelitian/ PkM <sup>1)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Nama Dosen',
                'input' => 'select',
                'lazy_load' => true,
                'api_url' => '/v2/spmi/search-dosen',
            ],
            [
                'label' => 'Mata Kuliah',
                'input' => 'textarea',
            ],
            [
                'label' => 'Bentuk Integrasi <sup>2)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Tahun',
                'input' => 'input|number|max_length:4',
            ]
        ];

        $row = [];

        $footer = [];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get5c
     * @param $table_key
     * @param $id
     * return array
     */
    public function get5c($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Aspek yang Diukur',
                'input' => 'readonly',
            ],
            [
                'label' => 'Tingkat Kepuasan Mahasiswa (%)',
                'childs' => [
                    [
                        'label' => 'Sangat Baik',
                        'input' => 'input|decimal',
                    ],
                    [
                        'label' => 'Baik',
                        'input' => 'input|decimal',
                    ],
                    [
                        'label' => 'Cukup',
                        'input' => 'input|decimal',
                    ],
                    [
                        'label' => 'Kurang',
                        'input' => 'input|decimal',
                    ],
                ]
            ],
            [
                'label' => 'Rencana Tindak Lanjut oleh UPPS/PS',
                'input' => 'textarea',
            ],
        ];

        $row = [
            'Keandalan (reliability): kemampuan dosen, tenaga kependidikan, dan pengelola dalam memberikan pelayanan' => [
                'data_index' => 1,
            ],
            'Daya tanggap (responsiveness): kemauan dari dosen, tenaga kependidikan, dan pengelola dalam membantu mahasiswa dan memberikan jasa dengan cepat' => [
                'data_index' => 2,
            ],
            'Kepastian (assurance): kemampuan dosen, tenaga kependidikan, dan pengelola untuk memberi keyakinan kepada mahasiswa bahwa pelayanan yang diberikan telah sesuai dengan ketentuan' => [
                'data_index' => 3,
            ],
            'Empati (empathy): kesediaan/kepedulian dosen, tenaga kependidikan, dan pengelola untuk memberi perhatian kepada mahasiswa' => [
                'data_index' => 4,
            ],
            'Tangible: penilaian mahasiswa terhadap kecukupan, aksesibitas, kualitas sarana dan prasarana' => [
                'data_index' => 5,
            ],
        ];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [3, 4, 5, 6],
                'colspan' => 2
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasAction = false;

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get6a
     * @param $table_key
     * @param $id
     * return array
     */
    public function get6a($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Dosen',
                'input' => 'select',
                'lazy_load' => true,
                'api_url' => '/v2/spmi/search-dosen',
            ],
            [
                'label' => 'Tema Penelitian sesuai Roadmap',
                'input' => 'textarea',
            ],
            [
                'label' => 'Nama Mahasiswa',
                'input' => 'textarea',
            ],
            [
                'label' => 'Judul Kegiatan <sup>1)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Tahun',
                'input' => 'input|number|max_length:4',
            ],
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'COUNT',
                'indexes' => [5],
                'colspan' => 4
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get6b
     * @param $table_key
     * @param $id
     * return array
     */
    public function get6b($table_key, $id = null)
    {
        return $this->get6a($table_key, $id);
    }

    /**
     * Get get7
     * @param $table_key
     * @param $id
     * return array
     */
    public function get7($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Dosen',
                'input' => 'select',
                'lazy_load' => true,
                'api_url' => '/v2/spmi/search-dosen',
            ],
            [
                'label' => 'Tema PkM sesuai Roadmap',
                'input' => 'textarea',
            ],
            [
                'label' => 'Nama Mahasiswa',
                'input' => 'textarea',
            ],
            [
                'label' => 'Judul Kegiatan <sup>1)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Tahun',
                'input' => 'input|number|max_length:4',
            ],
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'COUNT',
                'indexes' => [5],
                'colspan' => 4
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get8a
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8a($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun Lulus',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah Lulusan',
                'input' => 'input|number',
            ],
            [
                'label' => 'Indeks Prestasi Kumulatif (IPK)',
                'childs' => [
                    [
                        'label' => 'Minimal',
                        'input' => 'input|decimal|max:4,min:0',
                    ],
                    [
                        'label' => 'Rata-rata',
                        'input' => 'input|decimal|max:4,min:0',
                    ],
                    [
                        'label' => 'Maksimal',
                        'input' => 'input|decimal|max:4,min:0',
                    ],
                ]
            ],
        ];

        $row = [
            $this->tsAudit->TS2 => [
                'data_index' => 1,
            ],
            $this->tsAudit->TS1 => [
                'data_index' => 2,

            ],
            $this->tsAudit->TS0 => [
                'data_index' => 3,
            ],
        ];

        $footer = [];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasAction = false;
        $this->isHasNumber = false;

        return $this->buildTable($table, $row, $footer, $data);
    }


    /**
     * Get get8b1
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8b1($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Kegiatan',
                'input' => 'textarea',
            ],
            [
                'label' => 'Tahun Perolehan',
                'input' => 'input|number|max_length:4',
            ],
            [
                'label' => 'Tingkat <sup>1)</sup>',
                'childs' => [
                    [
                        'label' => 'Lokal/ Wilayah',
                        'input' => 'checkbox|#tingkat_wilayah',
                    ],
                    [
                        'label' => 'Nasional',
                        'input' => 'checkbox|#tingkat_wilayah',
                    ],
                    [
                        'label' => 'Internasional',
                        'input' => 'checkbox|#tingkat_wilayah',
                    ],
                ]
            ],
            [
                'label' => 'Prestasi yang Dicapai',
                'input' => 'textarea',
            ],
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'COUNT',
                'indexes' => [4, 5, 6],
                'colspan' => 3
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get8b2
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8b2($table_key, $id = null)
    {
        return $this->get8b1($table_key, $id);
    }

    /**
     * Get get8c1
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8c1($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun Masuk',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah Mahasiswa Diterima <sup>1)</sup>',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah Mahasiswa yang lulus pada',
                'childs' => [
                    [
                        'label' => $this->tsAudit->TS4,
                        'input' => 'input|number',
                        'disabled' => [1, 2, 3]
                    ],
                    [
                        'label' => $this->tsAudit->TS3,
                        'input' => 'input|number',
                        'disabled' => [1, 2, 3]
                    ],
                    [
                        'label' => $this->tsAudit->TS2,
                        'input' => 'input|number',
                        'disabled' => [2, 3]
                    ],
                    [
                        'label' => $this->tsAudit->TS1,
                        'input' => 'input|number',
                        'disabled' => [3]
                    ],
                    [
                        'label' => $this->tsAudit->TS0,
                        'input' => 'input|number',
                    ],
                ]
            ],
            [
                'label' => 'Jumlah Lulusan s.d. akhir TS',
                'input' => 'input|number',
            ],
            [
                'label' => 'Rata-rata Masa Studi',
                'input' => 'input|decimal',
            ],
        ];

        $row = [
            $this->tsAudit->TS4 => [
                'data_index' => 1,
            ],
            $this->tsAudit->TS3 => [
                'data_index' => 2,
            ],
            $this->tsAudit->TS2 => [
                'data_index' => 3,
            ],
        ];


        $footer = [];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasNumber = false;
        $this->isHasAction = false;

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get8c2
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8c2($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun Masuk',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah Mahasiswa Diterima <sup>1)</sup>',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah Mahasiswa yang lulus pada',
                'childs' => [
                    [
                        'label' => $this->tsAudit->TS6,
                        'input' => 'input|number',
                        'disabled' => [1, 2, 3, 4]
                    ],
                    [
                        'label' => $this->tsAudit->TS5,
                        'input' => 'input|number',
                        'disabled' => [1, 2, 3, 4]
                    ],
                    [
                        'label' => $this->tsAudit->TS4,
                        'input' => 'input|number',
                        'disabled' => [1, 2, 3, 4]
                    ],
                    [
                        'label' => $this->tsAudit->TS3,
                        'input' => 'input|number',
                        'disabled' => [2, 3, 4]
                    ],
                    [
                        'label' => $this->tsAudit->TS2,
                        'input' => 'input|number',
                        'disabled' => [3, 4]
                    ],
                    [
                        'label' => $this->tsAudit->TS1,
                        'input' => 'input|number',
                        'disabled' => [4]
                    ],
                    [
                        'label' => $this->tsAudit->TS0,
                        'input' => 'input|number',
                    ],
                ]
            ],
            [
                'label' => 'Jumlah Lulusan s.d. akhir TS',
                'input' => 'input|number',
            ],
            [
                'label' => 'Rata-rata Masa Studi',
                'input' => 'input|decimal',
            ],
        ];

        $row = [
            $this->tsAudit->TS6 => [
                'data_index' => 1,
            ],
            $this->tsAudit->TS5 => [
                'data_index' => 2,
            ],
            $this->tsAudit->TS4 => [
                'data_index' => 3,
            ],
            $this->tsAudit->TS3 => [
                'data_index' => 4,
            ],
        ];


        $footer = [];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasNumber = false;
        $this->isHasAction = false;

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get8c3
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8c3($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun Masuk',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah Mahasiswa Diterima <sup>1)</sup>',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah Mahasiswa yang lulus pada',
                'childs' => [
                    [
                        'label' => $this->tsAudit->TS3,
                        'input' => 'input|number',
                        'disabled' => [1, 2, 3]
                    ],
                    [
                        'label' => $this->tsAudit->TS2,
                        'input' => 'input|number',
                        'disabled' => [2, 3]
                    ],
                    [
                        'label' => $this->tsAudit->TS1,
                        'input' => 'input|number',
                        'disabled' => [3]
                    ],
                    [
                        'label' => $this->tsAudit->TS0,
                        'input' => 'input|number',
                    ],
                ]
            ],
            [
                'label' => 'Jumlah Lulusan s.d. akhir TS',
                'input' => 'input|number',
            ],
            [
                'label' => 'Rata-rata Masa Studi',
                'input' => 'input|decimal',
            ],
        ];

        $row = [
            $this->tsAudit->TS3 => [
                'data_index' => 1,
            ],
            $this->tsAudit->TS2 => [
                'data_index' => 2,
            ],
            $this->tsAudit->TS1 => [
                'data_index' => 3,
            ],
        ];


        $footer = [];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasNumber = false;
        $this->isHasAction = false;

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get8c4
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8c4($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun Masuk',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah Mahasiswa Diterima <sup>1)</sup>',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah Mahasiswa yang lulus pada',
                'childs' => [
                    [
                        'label' => $this->tsAudit->TS6,
                        'input' => 'input|number',
                        'disabled' => [1, 2, 3, 4, 5]
                    ],
                    [
                        'label' => $this->tsAudit->TS5,
                        'input' => 'input|number',
                        'disabled' => [1, 2, 3, 4, 5]
                    ],
                    [
                        'label' => $this->tsAudit->TS4,
                        'input' => 'input|number',
                        'disabled' => [2, 3, 4, 5]
                    ],
                    [
                        'label' => $this->tsAudit->TS3,
                        'input' => 'input|number',
                        'disabled' => [3, 4, 5]
                    ],
                    [
                        'label' => $this->tsAudit->TS2,
                        'input' => 'input|number',
                        'disabled' => [4, 5]
                    ],
                    [
                        'label' => $this->tsAudit->TS1,
                        'input' => 'input|number',
                        'disabled' => [5]
                    ],
                    [
                        'label' => $this->tsAudit->TS0,
                        'input' => 'input|number',
                    ],
                ]
            ],
            [
                'label' => 'Jumlah Lulusan s.d. akhir TS',
                'input' => 'input|number',
            ],
            [
                'label' => 'Rata-rata Masa Studi',
                'input' => 'input|decimal',
            ],
        ];

        $row = [
            $this->tsAudit->TS6 => [
                'data_index' => 1,
            ],
            $this->tsAudit->TS5 => [
                'data_index' => 2,
            ],
            $this->tsAudit->TS4 => [
                'data_index' => 3,
            ],
            $this->tsAudit->TS3 => [
                'data_index' => 4,
            ],
            $this->tsAudit->TS2 => [
                'data_index' => 5,
            ],
        ];


        $footer = [];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasNumber = false;
        $this->isHasAction = false;

        return $this->buildTable($table, $row, $footer, $data);
    }


    /**
     * Get get8d1a
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8d1a($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun Lulus',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah lulusan',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah lulusan yang terlacak',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah lulusan yang dipesan sebelum lulus',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah lulusan dengan waktu tunggu mendapatkan pekerjaan',
                'childs' => [
                    [
                        'label' => 'WT < 3 bulan',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => '3 ≤ WT ≤ 6 bulan',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'WT > 6 bulan',
                        'input' => 'input|number',
                    ]
                ]
            ],
        ];

        $row = [
            $this->tsAudit->TS4 => [
                'data_index' => 1,
            ],
            $this->tsAudit->TS3 => [
                'data_index' => 2,
            ],
            $this->tsAudit->TS2 => [
                'data_index' => 3,
            ],
        ];


        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [2, 3, 4, 5, 6, 7],
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasNumber = false;
        $this->isHasAction = false;

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get8d1b
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8d1b($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun Lulus',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah lulusan',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah lulusan yang terlacak',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah lulusan dengan waktu tunggu mendapatkan pekerjaan',
                'childs' => [
                    [
                        'label' => 'WT < 6 bulan',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => '6 ≤ WT ≤ 18 bulan',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'WT > 18 bulan',
                        'input' => 'input|number',
                    ]
                ]
            ],
        ];

        $row = [
            $this->tsAudit->TS4 => [
                'data_index' => 1,
            ],
            $this->tsAudit->TS3 => [
                'data_index' => 2,
            ],
            $this->tsAudit->TS2 => [
                'data_index' => 3,
            ],
        ];


        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [2, 3, 4, 5, 6],
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasNumber = false;
        $this->isHasAction = false;

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get8d1c
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8d1c($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun Lulus',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah lulusan',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah lulusan yang terlacak',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah lulusan dengan waktu tunggu mendapatkan pekerjaan',
                'childs' => [
                    [
                        'label' => 'WT < 3 bulan',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => '3 ≤ WT ≤ 6 bulan',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'WT > 6 bulan',
                        'input' => 'input|number',
                    ]
                ]
            ],
        ];

        $row = [
            $this->tsAudit->TS4 => [
                'data_index' => 1,
            ],
            $this->tsAudit->TS3 => [
                'data_index' => 2,
            ],
            $this->tsAudit->TS2 => [
                'data_index' => 3,
            ],
        ];


        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [2, 3, 4, 5, 6],
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasNumber = false;
        $this->isHasAction = false;

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get8d2
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8d2($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun Lulus',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah lulusan',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah lulusan yang terlacak',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah lulusan dengan tingkat kesesuaian bidang kerja',
                'childs' => [
                    [
                        'label' => 'Rendah <sup>1)</sup>',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'Sedang <sup>2)</sup>',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'Tinggi <sup>3)</sup>',
                        'input' => 'input|number',
                    ]
                ]
            ],
        ];

        $row = [
            $this->tsAudit->TS4 => [
                'data_index' => 1,
            ],
            $this->tsAudit->TS3 => [
                'data_index' => 2,
            ],
            $this->tsAudit->TS2 => [
                'data_index' => 3,
            ],
        ];


        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [2, 3, 4, 5, 6],
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasNumber = false;
        $this->isHasAction = false;

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get8e1
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8e1($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun Lulus',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah lulusan',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah Lulusan yang Bekerja/ Berwira-usaha',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah Lulusan yang Bekerja berdasarkan Tingkat/Ukuran Tempat Kerja/Berwirausaha',
                'childs' => [
                    [
                        'label' => 'Lokal/ Wilayah/ Berwirausaha tidak Berizin',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'Nasional/ Berwirausaha Berizin',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'Multinasional/ Internasional',
                        'input' => 'input|number',
                    ]
                ]
            ],
        ];

        $row = [
            $this->tsAudit->TS4 => [
                'data_index' => 1,
            ],
            $this->tsAudit->TS3 => [
                'data_index' => 2,
            ],
            $this->tsAudit->TS2 => [
                'data_index' => 3,
            ],
        ];


        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [2, 3, 4, 5, 6],
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasNumber = false;
        $this->isHasAction = false;

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get8e2Ref
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8e2Ref($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun Lulus',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah lulusan',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah Tanggapan Kepuasan Pengguna yang Terlacak',
                'input' => 'input|number',
            ],
        ];

        $row = [
            $this->tsAudit->TS4 => [
                'data_index' => 1,
            ],
            $this->tsAudit->TS3 => [
                'data_index' => 2,
            ],
            $this->tsAudit->TS2 => [
                'data_index' => 3,
            ],
        ];


        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [2, 3],
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasNumber = false;
        $this->isHasAction = false;

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get8e2
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8e2($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Jenis Kemampuan',
                'input' => 'readonly',
            ],
            [
                'label' => 'Tingkat Kepuasan Pengguna (%)',
                'childs' => [
                    [
                        'label' => 'Sangat Baik',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'Baik',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'Cukup',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'Kurang',
                        'input' => 'input|number',
                    ],
                ]
            ],
            [
                'label' => 'Rencana Tindak Lanjut oleh UPPS/PS',
                'input' => 'textarea',
            ],
        ];

        $row = [
            'Etika' => [
                'data_index' => 1,
            ],
            'Keahlian pada bidang ilmu (kompetensi utama)' => [
                'data_index' => 2,
            ],
            'Kemampuan berbahasa asing' => [
                'data_index' => 3,
            ],
            'Penggunaan teknologi informasi' => [
                'data_index' => 4,
            ],
            'Kemampuan berkomunikasi' => [
                'data_index' => 5,
            ],
            'Kerjasama tim' => [
                'data_index' => 6,
            ],
            'Pengembangan diri' => [
                'data_index' => 7,
            ],
        ];


        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [3, 4, 5, 6],
                'colspan' => 2
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        $this->isHasAction = false;

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get8f1a
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8f1a($table_key, $id = null)
    {
        return $this->get3b4a($table_key, $id);
    }

    /**
     * Get get8f1b
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8f1b($table_key, $id = null)
    {
        return $this->get3b4b($table_key, $id);
    }

    /**
     * Get get8f2
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8f2($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Mahasiswa',
                'input' => 'input',
            ],
            [
                'label' => 'Judul Artikel yang Disitasi (Jurnal, Volume, Tahun, Nomor, Halaman)',
                'input' => 'textarea',
            ],
            [
                'label' => 'Jumlah Sitasi',
                'input' => 'input|number',
            ],
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'COUNT',
                'indexes' => [3, 4],
                'colspan' => 2,
                'indexes_function' => [
                    4 => [
                        'function' => 'SUM',
                        'is_static' => false,
                        'cell' => [
                            [4]
                        ]
                    ]
                ]
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get8f3
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8f3($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Mahasiswa',
                'input' => 'input',
            ],
            [
                'label' => 'Nama Produk/Jasa',
                'input' => 'textarea',
            ],
            [
                'label' => 'Deskripsi Produk/Jasa',
                'input' => 'textarea',
            ],
            [
                'label' => 'Bukti',
                'input' => 'textarea',
            ],
            [
                'label' => 'Tahun',
                'input' => 'input|number|max_length:4',
            ],
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'COUNT',
                'indexes' => [3],
                'colspan' => 2
            ]
        ];

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    /**
     * Get get8f4_1
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8f4_1($table_key, $id = null)
    {
        return $this->get3b7_1($table_key, $id);
    }

    /**
     * Get get8f4_2
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8f4_2($table_key, $id = null)
    {
        return $this->get3b7_2($table_key, $id);
    }

    /**
     * Get get8f4_3
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8f4_3($table_key, $id = null)
    {
        return $this->get3b7_3($table_key, $id);
    }

    /**
     * Get get8f4_4
     * @param $table_key
     * @param $id
     * return array
     */
    public function get8f4_4($table_key, $id = null)
    {
        return $this->get3b7_4($table_key, $id);
    }
}
