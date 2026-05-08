<?php

namespace Modules\SPMI\Services\Pengisian;

use Modules\SPMI\Models\DataPengisianLK;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\PengisianIndikator;

class IAPS51ManagementService extends PengisianService
{
    private function getFooterProfilDosen($table_key)
    {
        $pengisianIndikator = PengisianIndikator::where([
            'id_audit_periode' => $table_key['id_audit_periode'],
            'id_lembaga_akreditasi' => $table_key['id_lembaga_akreditasi'],
            'id_pengisian_panduan' => $table_key['id_pengisian_panduan'],
            'id_unit' => $table_key['id_unit'],
        ])->first();

        if ($pengisianIndikator == null) {
            return [0, 0, 0];
        }

        $indikatorDPR = IndikatorLaporanKinerja::where('id_pengisian_panduan', $table_key['id_pengisian_panduan'])
        ->where('nomor_indikator', '1.1.a.1')->first();
        $dataPengisian = DataPengisianLK::where([
            'id_pengisian_indikator' => $pengisianIndikator->id,
            'id_indikator_laporan_kinerja' => $indikatorDPR->id
        ])->first();

        if ($dataPengisian == null) {
            $ndpr = 0;
        } else {
            $aData = json_decode($dataPengisian->data_pengisian_lk, true);
            $ndpr = count($aData);
        }

        $indikatorDTT = IndikatorLaporanKinerja::where('id_pengisian_panduan', $table_key['id_pengisian_panduan'])
        ->where('nomor_indikator', '1.1.a.2')->first();
        $dataPengisian = DataPengisianLK::where([
            'id_pengisian_indikator' => $pengisianIndikator->id,
            'id_indikator_laporan_kinerja' => $indikatorDTT->id
        ])->first();

        if ($dataPengisian == null) {
            $ndtt = 0;
        } else {
            $aData = json_decode($dataPengisian->data_pengisian_lk, true);
            $ndtt = count($aData);
        }

        $pdtt = ($ndpr + $ndtt) > 0 ? ($ndtt / ($ndpr + $ndtt)) * 100 : 0;

        return [$ndpr, $ndtt, number_format($pdtt, 2)];
    }

    public function get11a1($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Dosen/Instruktur <sup>4)</sup>',
                'input' => 'select',
                'lazy_load' => true,
                'api_url' => '/v2/spmi/search-dosen',
            ],
            [
                'label' => 'Pendidikan Tertinggi',
                'input' => 'textarea',
            ],
            [
                'label' => 'Keahlian <sup>1)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Jabatan Akademik',
                'input' => 'select',
                'options' => ['Tenaga Pengajar' => 'Tenaga Pengajar', 'Asisten Ahli' => 'Asisten Ahli', 'Lektor' => 'Lektor', 'Lektor Kepala' => 'Lektor Kepala', 'Guru Besar' => 'Guru Besar'],
            ],
            [
                'label' => 'Sertifikat Pendidik Profesional <sup>2)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Luaran DPR <sup>3)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Kesesuaian keahlian & luaran dosen dengan Kompetensi inti program studi',
                'childs' => [
                    [
                        'label' => 'Sesuai',
                        'input' => 'checkbox|#kesesuaian',
                    ],
                    [
                        'label' => 'Tidak Sesuai',
                        'input' => 'checkbox|#kesesuaian',
                    ],
                ]
            ],
        ];

        $row = [];

        list($ndpr, $ndtt, $pdtt) = $this->getFooterProfilDosen($table_key);

        $footer = [
            [
                'label' => 'Jumlah Dosen Tidak Tetap',
                'function' => 'CUSTOM_VALUE',
                'indexes' => [5],
                'values' => [5 => $ndtt],
                'colspan' => 4
            ],
            [
                'label' => 'Jumlah Dosen Penghitung Rasio (DPR)',
                'function' => 'COUNT',
                'indexes' => [5],
                'references' => [5 => 2],
                'colspan' => 4
            ],
            [
                'label' => 'Persentase Dosen Tetap PDTT = (NDTT / (NDTT + NDPR)) x 100%',
                'function' => 'CUSTOM_VALUE',
                'indexes' => [5],
                'values' => [5 => $pdtt . '%'],
                'colspan' => 4
            ],
            [
                'label' => 'Jumlah DPR yang sesuai dan tidak sesuai bidang',
                'function' => 'COUNT',
                'indexes' => [8, 9],
                'colspan' => 4
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

    public function get11a2($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Dosen',
                'input' => 'select',
                'lazy_load' => true,
                'api_url' => '/v2/spmi/search-dosen',
            ],
            [
                'label' => 'Pendidikan Tertinggi',
                'input' => 'textarea',
            ],
            [
                'label' => 'Keahlian <sup>1)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Jabatan Akademik',
                'input' => 'select',
                'options' => ['Tenaga Pengajar' => 'Tenaga Pengajar', 'Asisten Ahli' => 'Asisten Ahli', 'Lektor' => 'Lektor', 'Lektor Kepala' => 'Lektor Kepala', 'Guru Besar' => 'Guru Besar'],
            ],
            [
                'label' => 'Sertifikat Pendidik Profesional <sup>2)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Luaran Dosen Tidak Tetap <sup>3)</sup>',
                'input' => 'textarea',
            ],
            [
                'label' => 'Kesesuaian keahlian & luaran dosen dengan Kompetensi inti program studi',
                'childs' => [
                    [
                        'label' => 'Sesuai',
                        'input' => 'checkbox|#kesesuaian',
                    ],
                    [
                        'label' => 'Tidak Sesuai',
                        'input' => 'checkbox|#kesesuaian',
                    ],
                ]
            ],
        ];

        $row = [];

        list($ndpr, $ndtt, $pdtt) = $this->getFooterProfilDosen($table_key);

        $footer = [
            [
                'label' => 'Jumlah Dosen Tidak Tetap',
                'function' => 'COUNT',
                'indexes' => [5],
                'references' => [5 => 2],
                'colspan' => 4
            ],
            [
                'label' => 'Jumlah Dosen Penghitung Rasio (DPR)',
                'function' => 'CUSTOM_VALUE',
                'indexes' => [5],
                'values' => [5 => $ndpr],
                'colspan' => 4
            ],
            [
                'label' => 'Persentase Dosen Tetap PDTT = (NDTT / (NDTT + NDPR)) x 100%',
                'function' => 'CUSTOM_VALUE',
                'indexes' => [5],
                'values' => [5 => $pdtt . '%'],
                'colspan' => 4
            ],
            [
                'label' => 'Jumlah DPR yang sesuai dan tidak sesuai bidang',
                'function' => 'COUNT',
                'indexes' => [8, 9],
                'colspan' => 4
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

    public function get11a3($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Dosen Industri/Praktisi',
                'input' => 'select',
                'lazy_load' => true,
                'api_url' => '/v2/spmi/search-dosen',
            ],
            [
                'label' => 'Nomor Registrasi <sup>1)</sup>',
                'input' => 'input',
            ],
            [
                'label' => 'Perusahaan/ Industri <sup>2)</sup>',
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
                'label' => 'Matakuliah yang diampu',
                'input' => 'textarea',
            ],
            [
                'label' => 'Bobot Kredit (sks)',
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

    public function get11a4($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Dosen',
                'input' => 'select',
                'lazy_load' => true,
                'api_url' => '/v2/spmi/search-dosen',
            ],
            [
                'label' => 'DPRPS',
                'input' => 'checkbox',
            ],
            [
                'label' => 'Ekuivalen Waktu Mendidik Penuh (EWMP) dalam sks',
                'childs' => [
                    [
                        'label' => 'Pendidikan: Pembelajaran dan Pembimbingan',
                        'childs' => [
                            [
                                'label' => 'PS yang diakreditasi',
                                'input' => 'input|decimal',
                            ],
                            [
                                'label' => 'PS lain di dalam PT',
                                'input' => 'input|decimal',
                            ],
                            [
                                'label' => 'Di luar PT',
                                'input' => 'input|decimal',
                            ]
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
                        'label' => 'Tugas Tambahan dan/atau Penunjang *)',
                        'input' => 'input|decimal',
                    ],
                    [
                        'label' => 'Total sks dalam setahun',
                        'function' => 'SUM',
                        'indexes' => [4, 5, 6, 7, 8, 9],
                    ],
                    [
                        'label' => 'Rata-rata dalam semester',
                        'function' => 'SUM',
                        'divides' => 2,
                        'indexes' => [4, 5, 6, 7, 8, 9],
                    ],
                ],
            ],
        ];

        $row = [];

        $footer = [
            [
                'label' => 'Rata-rata EWMP DPR',
                'function' => 'AVERAGE',
                'indexes' => [10, 11],
                'colspan' => 9
            ],
            [
                'label' => 'Rata-rata EWMP DPRPS',
                'function' => 'AVERAGE',
                'indexes' => [10, 11],
                'colspan' => 9,
                'condition' => [3 => 1]
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

    public function get11a5($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Jenis Tenaga Kependidikan',
                'input' => 'readonly',
            ],
            [
                'label' => 'Sertifikasi Pelatihan <sup>2)</sup>',
                'input' => 'input',
            ],
            [
                'label' => 'Kualifikasi Pendidikan Tertinggi',
                'childs' => [
                    [
                        'label' => 'S-1',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'S-2',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'S-3',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'D-4',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'D-3',
                        'input' => 'input|number',
                    ]
                ]
            ],
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [4, 5, 6, 7, 8],
            ]
        ];

        $row = [
            'Pustakawan <sup>1)</sup>' => [
                'data_index' => 1,
                'is_raw_html' => true,
            ],
            'Laboran' => [
                'data_index' => 2,
            ],
            'Teknisi/Analis/Operator' => [
                'data_index' => 3,
            ],
            'Programer' => [
                'data_index' => 4,
            ],
            'Administrasi' => [
                'data_index' => 5,
            ]
        ];

        $footer = [
            [
                'label' => 'Jumlah',
                'function' => 'SUM',
                'indexes' => [3, 4, 5, 6, 7, 8],
                'colspan' => 2
            ]
        ];

        $this->isHasAction = false;

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    public function get11a6($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Jenis Biaya',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah Penggunaan (Rupiah)',
                'childs' => [
                    [
                        'label' => 'TS-2',
                        'input' => 'input|number|currency',
                    ],
                    [
                        'label' => 'TS-1',
                        'input' => 'input|number|currency',
                    ],
                    [
                        'label' => 'TS',
                        'input' => 'input|number|currency',
                    ],
                ]
            ],
            [
                'label' => 'Rata-rata',
                'function' => 'AVERAGE',
                'type' => 'currency',
                'indexes' => [3, 4, 5],
            ]
        ];

        $cell1 = [];
        for($i = 1; $i <= 5; $i++) {
            for($j = 3; $j <=5; $j++) {
                $cell1[] = [$i, $j];
            }
        }

        $cell2 = [];
        for($i = 6; $i <= 8; $i++) {
            for($j = 3; $j <=5; $j++) {
                $cell2[] = [$i, $j];
            }
        }

        $row = [
            'Biaya Dosen (Gaji, Honor)' => [
                'data_index' => 1,
                'childs' => [
                    'index:2' => 'Biaya Tenaga Kependidikan (Gaji, Honor)',
                    'index:3' => 'Biaya Operasional Pembelajaran (Bahan dan Peralatan Habis Pakai)',
                    'index:4' => 'Biaya Operasional Tidak Langsung (Listrik, Gas, Air, Pemeliharaan Gedung, Pemeliharaan Sarana, Uang Lembur, Telekomunikasi, Konsumsi, Transport Lokal, Pajak, Asuransi, dll.)',
                    'index:5' => 'Biaya operasional kemahasiswaan (penalaran, minat, bakat, dan kesejahteraan). Jumlah Biaya Operasional Pendidikan',
                ],
                '_footer_' => [
                    'label' => 'Jumlah Biaya Operasional Pendidikan (1+2+3+4+5)',
                    'type' => 'currency',
                    'function' => 'SUM',
                    'indexes' => [3, 4, 5, 6],
                    'indexes_function' => [
                        6 => [
                            'function' => 'AVERAGE_Y',
                            'cell' => $cell1
                        ]
                    ],
                    'colspan' => 2
                ],
            ],
            'Biaya Investasi SDM' => [
                'data_index' => 6,
                'childs' => [
                    'index:7' => 'Biaya Investasi Sarana',
                    'index:8' => 'Biaya Investasi Prasarana',
                ],
                '_footer_' => [
                    'label' => 'Jumlah Biaya Investasi (6+7+8)',
                    'type' => 'currency',
                    'function' => 'SUM',
                    'indexes' => [3, 4, 5, 6],
                    'indexes_function' => [
                        6 => [
                            'function' => 'AVERAGE_Y',
                            'cell' => $cell1
                        ]
                    ],
                    'colspan' => 2
                ]
            ],
        ];

        $footer = [];

        $this->isHasAction = false;

        $data_raw = DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $table_key['id_indikator_laporan_kinerja'])
            ->first();

        $data = [];
        if ($data_raw) {
            $data = json_decode($data_raw->data_pengisian_lk, true);
        }

        return $this->buildTable($table, $row, $footer, $data);
    }

    public function get11b61($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Bentuk Kegiatan Pembelajaran <sup>*)</sup>',
                'input' => 'input',
            ],
            [
                'label' => 'Tempat Kegiatan',
                'input' => 'input',
            ],
            [
                'label' => 'Beban Kegiatan (sks)',
                'input' => 'input',
            ],
            [
                'label' => 'Bukti-Bukti Pendukung',
                'input' => 'input',
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

    public function get11c7($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Jumlah lulusan pada saat TS-n',
                'childs' => [
                    [
                        'label' => 'TS-2',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'TS-1',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'TS <sup>1)</sup>',
                        'input' => 'input|number',
                    ],
                ],
            ],
            [
                'label' => 'Presentase Penurunan',
                'input' => 'input|decimal|suffix:%',
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

    public function get11c8a($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun Masuk Awal TS/TS-1',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah Mahasiswa Diterima Pada Akhir TS-1/TS **)',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah Lulusan Pada Akhir TS-1/TS **)',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah Lulusan dengan Lama Masa Studi (Bulan)',
                'childs' => [
                    [
                        'label' => '9 ≦ MS ≦ 15 <sup>1)</sup>',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => '15 < MS ≦ 24 <sup>2)</sup>',
                        'input' => 'input|number',
                    ],
                ],
            ],
        ];

        $row = [
            'TS-1' => [
                'data_index' => 1,
            ],
            'TS' => [
                'data_index' => 2,
            ],
        ];

        $this->isHasNumber = false;

        $footer = [];

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

    public function get11c8b($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun Masuk',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah Mahasiswa Diterima **)',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah Lulusan **)',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah Lulusan dengan Lama Masa Studi (Tahun)',
                'childs' => [
                    [
                        'label' => '1,5 ≦ MS ≦ 2,5 <sup>1)</sup>',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => '2,5 < MS ≦ 4 <sup>2)</sup>',
                        'input' => 'input|number',
                    ],
                ],
            ],
        ];

        $row = [
            'TS-3' => [
                'data_index' => 1,
            ],
            'TS-2' => [
                'data_index' => 2,
            ],
            'TS-1' => [
                'data_index' => 3,
            ],
        ];

        $this->isHasNumber = false;

        $footer = [];

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

    public function get11c8c($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun Masuk',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah Mahasiswa Diterima **)',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah Lulusan **)',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah Lulusan dengan Lama Masa Studi (Tahun)',
                'childs' => [
                    [
                        'label' => '2,5 ≦ MS ≦ 3,5 <sup>1)</sup>',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => '3,5 < MS ≦ 6 <sup>2)</sup>',
                        'input' => 'input|number',
                    ],
                ],
            ],
        ];

        $row = [
            'TS-5' => [
                'data_index' => 1,
            ],
            'TS-4' => [
                'data_index' => 2,
            ],
            'TS-3' => [
                'data_index' => 3,
            ],
            'TS-2' => [
                'data_index' => 4,
            ],
        ];

        $this->isHasNumber = false;

        $footer = [];

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

    public function get11c8d($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun Masuk',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah Mahasiswa Diterima **)',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah Lulusan **)',
                'input' => 'input|number',
            ],
            [
                'label' => 'Jumlah Lulusan dengan Lama Masa Studi (Tahun)',
                'childs' => [
                    [
                        'label' => '3,5 ≦ MS ≦ 6 <sup>1)</sup>',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => '6 < MS ≦ 8,0 <sup>3)</sup>',
                        'input' => 'input|number',
                    ],
                ],
            ],
        ];

        $row = [
            'TS-7' => [
                'data_index' => 1,
            ],
            'TS-6' => [
                'data_index' => 2,
            ],
            'TS-5' => [
                'data_index' => 3,
            ],
            'TS-4' => [
                'data_index' => 4,
            ],
            'TS-3' => [
                'data_index' => 5,
            ],
        ];

        $this->isHasNumber = false;

        $footer = [];

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

    public function get11c9($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun Masuk',
                'input' => 'readonly',
            ],
            [
                'label' => 'Hasil Pengukuran CPL <sup>*)</sup>',
                'childs' => [
                    [
                        'label' => 'TS-2',
                        'input' => 'input',
                        'disabled' => [1, 2]
                    ],
                    [
                        'label' => 'TS-1',
                        'input' => 'input',
                        'disabled' => [1, 3]
                    ],
                    [
                        'label' => 'TS',
                        'input' => 'input',
                        'disabled' => [2, 3]
                    ],
                ],
            ],
        ];

        $this->isHasNumber = false;

        $row = [
            'TS-2' => [
                'data_index' => 1,
            ],
            'TS-1' => [
                'data_index' => 2,
            ],
            'TS' => [
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

        return $this->buildTable($table, $row, $footer, $data);
    }

    public function get11d11($table_key, $id = null)
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
                ],
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
                'colspan' => 2,
                'function' => 'SUM',
                'indexes' => [3, 4, 5, 6],
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

    public function get11d12($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Jumlah mahasiswa baru pada saat TS-n',
                'childs' => [
                    [
                        'label' => 'TS-2',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'TS-1',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'TS',
                        'input' => 'input|number',
                    ],
                ],
            ],
            [
                'label' => 'Presentase Penurunan Mahasiswa Baru',
                'input' => 'input|decimal|suffix:%',
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

    public function get11d13($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Kegiatan',
                'input' => 'input',
            ],
            [
                'label' => 'Tahun Perolehan',
                'input' => 'input|number',
            ],
            [
                'label' => 'Tingkat',
                'childs' => [
                    [
                        'label' => 'Internasional',
                        'input' => 'checkbox|#tingkat',
                    ],
                    [
                        'label' => 'Nasional',
                        'input' => 'checkbox|#tingkat',
                    ],
                    [
                        'label' => 'Lokal/Wilayah',
                        'input' => 'checkbox|#tingkat',
                    ],
                ],
            ],
            [
                'label' => 'Prestasi yang Dicapai',
                'input' => 'input',
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

    public function get11d14($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Tahun lulus',
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
                'label' => 'Rata-rata Masa Tunggu Lulusan Mendapatkan Pekerjaan Pertama (Bulan)',
                'input' => 'input|number',
            ],
            [
                'label' => 'Persentase Kesesuaian Bidang Kerja',
                'input' => 'input|number',
            ]
        ];

        $this->isHasNumber = false;
        $this->isHasAction = false;

        $row = [
            'TS-4' => [
                'data_index' => 1,
            ],
            'TS-3' => [
                'data_index' => 2,
            ],
            'TS-2' => [
                'data_index' => 3,
            ],
        ];

        $footer = [
            [
                'label' => 'Rata-rata',
                'function' => 'AVERAGE',
                'indexes' => [5],
                'colspan' => 3,
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

    public function get11d10($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Lulusan',
                'input' => 'input',
            ],
            [
                'label' => 'Bidang yang mendapatkan Pengakuan/apresiasi',
                'input' => 'input',
            ],
            [
                'label' => 'Lembaga/Instansi terkait yang berkompeten',
                'input' => 'input',
            ],
            [
                'label' => 'Tingkat',
                'childs' => [
                    [
                        'label' => 'Wilayah',
                        'input' => 'checkbox|#tingkat',
                    ],
                    [
                        'label' => 'Nasional',
                        'input' => 'checkbox|#tingkat',
                    ],
                    [
                        'label' => 'Internasional',
                        'input' => 'checkbox|#tingkat',
                    ],
                ],
            ],
            [
                'label' => 'Tahun (YYYY)',
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

    public function get121($table_key, $id = null)
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
                'input' => 'input',
            ],
            [
                'label' => 'Nama Mahasiswa',
                'input' => 'textarea',
            ],
            [
                'label' => 'Judul Kegiatan <sup>1)</sup>',
                'input' => 'input',
            ],
            [
                'label' => 'Tahun',
                'input' => 'input|number',
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

    public function get122($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Jenis Publikasi',
                'input' => 'readonly',
            ],
            [
                'label' => 'Jumlah Judul',
                'childs' => [
                    [
                        'label' => 'TS-2',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'TS-1',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'TS',
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
                    6 => 'N<sub>A1</sub>'
                ]
            ],
            'Jurnal nasional terakreditasi' => [
                'data_index' => 2,
                'data_labels' => [
                    6 => 'N<sub>A2</sub>'
                ]
            ],
            'Jurnal internasional' => [
                'data_index' => 3,
                'data_labels' => [
                    6 => 'N<sub>A3</sub>'
                ]
            ],
            'Jurnal internasional bereputasi' => [
                'data_index' => 4,
                'data_labels' => [
                    6 => 'N<sub>A4</sub>'
                ]
            ],
            'Seminar wilayah/lokal/perguruan tinggi' => [
                'data_index' => 5,
                'data_labels' => [
                    6 => 'N<sub>B1</sub>'
                ]
            ],
            'Seminar nasional' => [
                'data_index' => 6,
                'data_labels' => [
                    6 => 'N<sub>B2</sub>'
                ]
            ],
            'Seminar internasional' => [
                'data_index' => 7,
                'data_labels' => [
                    6 => 'N<sub>B3</sub>'
                ]
            ],
            'Tulisan di media massa wilayah/provinsi' => [
                'data_index' => 8,
                'data_labels' => [
                    6 => 'N<sub>C1</sub>'
                ]
            ],
            'Tulisan di media massa nasional' => [
                'data_index' => 9,
                'data_labels' => [
                    6 => 'N<sub>C2</sub>'
                ]
            ],
            'Tulisan di media massa internasional' => [
                'data_index' => 10,
                'data_labels' => [
                    6 => 'N<sub>C3</sub>'
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

    public function get123($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Judul Luaran Penelitian/PkM',
                'input' => 'input',
            ],
            [
                'label' => 'Tahun',
                'input' => 'input|number',
            ],
            [
                'label' => 'Keterangan',
                'input' => 'textarea',
            ],
        ];

        $row = [
            '<b>
                Paten <sup>1)</sup>:
            </b>
            <ol type="a" style="margin-left:16px;">
                <li>Paten</li>
                <li>Paten Sederhana</li>
            </ol>' => [
                'data_index' => 1,
                'is_raw_html' => true,
                '_footer_' => [
                    'label' => 'Jumlah',
                    'function' => 'COUNT',
                    'indexes' => [3],
                    'colspan' => 2,
                    'indexes_label' => [3 => 'N<sub>A</sub>']
                ]
            ],
            '<b>
                HKI <sup>1)</sup>:
            </b>
            <ol type="a" style="margin-left:16px;">
                <li>Hak Cipta,</li>
                <li>Desain Produk Industri,</li>
                <li>Perlindungan Varietas Tanaman
                <br>(Sertifikat Perlindungan Varietas
                Tanaman, Sertifikat Pelepasan Varietas,
                Sertifikat Pendaftaran Varietas),</li>
                <li>Desain Tata Letak Sirkuit Terpadu,</li>
                <li>dll.)</li>
            </ol>' => [
                'data_index' => 2,
                'is_raw_html' => true,
                '_footer_' => [
                    'label' => 'Jumlah',
                    'function' => 'COUNT',
                    'indexes' => [3],
                    'colspan' => 2,
                    'indexes_label' => [3 => 'N<sub>B</sub>']
                ]
            ],
            'Teknologi Tepat Guna, Produk (Produk Terstandarisasi, Produk Tersertifikasi), Karya Seni, Rekayasa Sosial' => [
                'data_index' => 3,
                '_footer_' => [
                    'label' => 'Jumlah',
                    'function' => 'COUNT',
                    'indexes' => [3],
                    'colspan' => 2,
                    'indexes_label' => [3 => 'N<sub>C</sub>']
                ]
            ],
            'Buku ber-ISBN, Book Chapter' => [
                'data_index' => 4,
                '_footer_' => [
                    'label' => 'Jumlah',
                    'function' => 'COUNT',
                    'indexes' => [3],
                    'colspan' => 2,
                    'indexes_label' => [3 => 'N<sub>D</sub>']
                ]
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

        return $this->buildTable($table, $row, $footer, $data);
    }

    public function get124($table_key, $id = null)
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
                'input' => 'input',
            ],
            [
                'label' => 'Rekognisi dan Bukti Pendukung',
                'input' => 'input',
            ],
            [
                'label' => 'Tingkat',
                'childs' => [
                    [
                        'label' => 'Wilayah',
                        'input' => 'checkbox|#tingkat',
                    ],
                    [
                        'label' => 'Nasional',
                        'input' => 'checkbox|#tingkat',
                    ],
                    [
                        'label' => 'Internasional',
                        'input' => 'checkbox|#tingkat',
                    ],
                ],
            ],
            [
                'label' => 'Tahun',
                'input' => 'input|number',
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

    public function get125($table_key, $id = null)
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
                'input' => 'input',
            ],
            [
                'label' => 'Nama Mahasiswa',
                'input' => 'input',
            ],
            [
                'label' => 'Judul Tesis/ Disertasi <sup>1)</sup>',
                'input' => 'input',
            ],
            [
                'label' => 'Tahun',
                'input' => 'input|number',
            ]
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

    public function get126($table_key, $id = null)
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
                'input' => 'input',
            ],
            [
                'label' => 'Jumlah Sitasi',
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

    public function get31($table_key, $id = null)
    {
        $table = [
            [
                'label' => '<i>Stakeholder</i>',
                'input' => 'readonly',
            ],
            [
                'label' => 'Instrumen',
                'childs' => [
                    [
                        'label' => 'Ada',
                        'input' => 'checkbox|#presence',
                    ],
                    [
                        'label' => 'Tidak Ada',
                        'input' => 'checkbox|#presence',
                    ],
                ]
            ],
            [
                'label' => 'Jumlah Responden',
                'childs' => [
                    [
                        'label' => 'TS-2',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'TS-1',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'TS',
                        'input' => 'input|number',
                    ],
                ]
            ],
            [
                'label' => 'Persentase keterwakilan responden (%)',
                'childs' => [
                    [
                        'label' => 'TS-2',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'TS-1',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'TS',
                        'input' => 'input|number',
                    ],
                ]
            ],
            [
                'label' => 'Persentase yang menjawab layanan (SB=sangat baik, B=baik, C=cukup, KB=kurang baik) (%)',
                'childs' => [
                    [
                        'label' => 'SB',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'B',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'C',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'KB',
                        'input' => 'input|number',
                    ],
                ]
            ],
            [
                'label' => 'Tindak Lanjut',
                'input' => 'input|text',
            ],
        ];

        $dataUnits = [
            8 => '%',
            9 => '%',
            10 => '%',
            11 => '%',
            12 => '%',
            13 => '%',
            14 => '%',
        ];

        $row = [
            'Mahasiswa' => [
                'data_index' => 1,
                'data_units' => $dataUnits,
            ],
            'Dosen' => [
                'data_index' => 2,
                'data_units' => $dataUnits,

            ],
            'Tenaga Kependidikan' => [
                'data_index' => 3,
                'data_units' => $dataUnits,

            ],
            'Mitra' => [
                'data_index' => 4,
                'data_units' => $dataUnits,

            ],
            'Lulusan (*)' => [
                'data_index' => 5,
                'data_units' => $dataUnits,

            ],
            'Pengguna Lulusan (**)' => [
                'data_index' => 6,
                'data_units' => $dataUnits,

            ],
        ];

        $this->isHasAction = false;

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

    public function get41($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Rekognisi',
                'input' => 'input',
            ],
            [
                'label' => 'Lembaga Pemberi Rekognisi',
                'input' => 'input',
            ],
            [
                'label' => 'Bukti Pendukung',
                'input' => 'input',
            ],
            [
                'label' => 'Tingkat',
                'childs' => [
                    [
                        'label' => 'Wilayah',
                        'input' => 'checkbox|#tingkat',
                    ],
                    [
                        'label' => 'Nasional',
                        'input' => 'checkbox|#tingkat',
                    ],
                    [
                        'label' => 'Internasional',
                        'input' => 'checkbox|#tingkat',
                    ],
                ],
            ],
            [
                'label' => 'Tahun',
                'input' => 'input|number',
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

    public function getXREF1($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Jumlah Mahasiswa Aktif',
                'childs' => [
                    [
                        'label' => 'TS',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'TS-1',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'TS-2',
                        'input' => 'input|number',
                    ],
                    [
                        'label' => 'TS-3',
                        'input' => 'input|number',
                    ],
                ],
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

    public function getXREF2($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'NIM',
                'input' => 'input'
            ],
            [
                'label' => 'Nama Lulusan',
                'input' => 'input'
            ],
            [
                'label' => 'Tahun Lulus',
                'input' => 'input|number'
            ],
            [
                'label' => 'Status Setelah Lulus',
                'input' => 'input'
            ],
            [
                'label' => 'Waktu Tunggu Lulusan (Bulan)',
                'input' => 'input|number'
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

    public function getXREF3($table_key, $id = null)
    {
        $table = [
            [
                'label' => 'Nama Lengkap',
                'input' => 'select',
                'lazy_load' => true,
                'api_url' => '/v2/spmi/search-dosen',
            ],
            [
                'label' => 'NIP',
                'input' => 'input'
            ],
            [
                'label' => 'Tema Penelitian',
                'input' => 'input'
            ],
            [
                'label' => 'Judul Penelitian',
                'input' => 'input'
            ],
            [
                'label' => 'Tahun Penelitian',
                'input' => 'input|number'
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
}
