<?php

namespace Modules\SPMI\Data\FillingGetData;

use Illuminate\Support\Arr;
use Modules\Core\Helpers\Cstr;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Data\Helper\SevimaGlossaryTrait;

class IAPS51Kriteria
{
    use SevimaGlossaryTrait;

    public function get11a1($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $periode = [$ts . '1', $ts . '2', $ts . '3'];

        $data = $this->glossary->rekapDosenPenghitungRasioKepegawaian([$param['id_unit']], $periode);
        $data = collect($data)->unique('id_pegawai')->values();

        $idUnit = $data->pluck('id_unit_dosen')->unique()->toArray();
        $sertifikasiDosen = $this->glossary->rekapSertifikasiPendidikProfesionalDosenKepegawaian([], $idUnit);
        $luaranDosen = $this->glossary->rekapLuaranKinerjaProfesionalDosenKepegawaian([], $idUnit);

        $listBiodata = Biodata::whereIn('ref_key_pegawai', $data->pluck('id_pegawai')->toArray())->pluck('id', 'ref_key_pegawai')->toArray();

        return $data->map(function ($item) use ($sertifikasiDosen, $luaranDosen, $listBiodata) {
            $sertifikat = array_filter($sertifikasiDosen, function ($s) use ($item) {
                return $s['id_pegawai'] == $item['id_pegawai'];
            });
            $luaran = array_filter($luaranDosen, function ($l) use ($item) {
                return $l['id_pegawai'] == $item['id_pegawai'];
            });

            $sertifikat = array_map(function ($s) {
                return $s['jenissertifikasi'] . ' - ' . $s['nosertifikasi'];
            }, $sertifikat);
            $luaran = array_map(function ($l) {
                return $l['namakegiatan'];
            }, $luaran);

            return [
                2 => $listBiodata[$item['id_pegawai']] ?? null,
                3 => $item['pendidikan_tertinggi'],
                4 => $item['keahlian_dosen'],
                5 => $item['jabatan_fungsional_terakhir'],
                6 => implode('; ', $sertifikat),
                7 => implode('; ', $luaran),
                8 => $item['dosen_dprps'],
                9 => !$item['dosen_dprps'],
            ];
        })->toArray();
    }

    public function get11a2($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $periode = [$ts . '1', $ts . '2', $ts . '3'];

        $data = $this->glossary->rekapDosenTidakTetapPerProgramStudiKepegawaian([$param['id_unit']], $periode);
        $data = collect($data)->unique('id_pegawai')->values();

        $listBiodata = Biodata::whereIn('ref_key_pegawai', $data->pluck('id_pegawai')->toArray())->pluck('id', 'ref_key_pegawai')->toArray();

        $idUnit = $data->pluck('id_unit_dosen')->unique()->toArray();
        $sertifikasiDosen = $this->glossary->rekapSertifikasiPendidikProfesionalDosenKepegawaian([], $idUnit);
        $luaranDosen = $this->glossary->rekapLuaranKinerjaProfesionalDosenKepegawaian([], $idUnit);

        return $data->map(function ($item) use ($sertifikasiDosen, $luaranDosen, $listBiodata) {
            $sertifikat = array_filter($sertifikasiDosen, function ($s) use ($item) {
                return $s['id_pegawai'] == $item['id_pegawai'];
            });
            $luaran = array_filter($luaranDosen, function ($l) use ($item) {
                return $l['id_pegawai'] == $item['id_pegawai'];
            });

            $sertifikat = array_map(function ($s) {
                return $s['jenissertifikasi'] . ' - ' . $s['nosertifikasi'];
            }, $sertifikat);
            $luaran = array_map(function ($l) {
                return $l['namakegiatan'];
            }, $luaran);

            return [
                2 => $listBiodata[$item['id_pegawai']] ?? null,
                3 => $item['pendidikan_tertinggi'],
                4 => $item['keahlian_dosen'],
                5 => $item['jabatan_akademik_terakhir'],
                6 => implode('; ', $sertifikat),
                7 => implode('; ', $luaran),
                8 => $item['dosen_dttps'],
                9 => !$item['dosen_dttps'],
            ];
        })->toArray();
    }

    public function get11a3($param = [])
    {
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit'], 3);

        $listIdPeriode = [];
        for ($tahun = $tahunMin; $tahun <= $tahunMax; $tahun++) {
            $listIdPeriode[] = $tahun . '1';;
            $listIdPeriode[] = $tahun . '2';
        }

        $data = $this->glossary->rekapDosenPraktisiKepegawaian([$param['id_unit']]);
        $dataPengajaran = $this->glossary->rekapMataKuliahPengajaranDosenAkademik(listIdUnitPegawai: [$param['id_unit']], listIdPeriode: $listIdPeriode);
        $dataSertifikasiDosen = $this->glossary->rekapSertifikasiPendidikProfesionalDosenKepegawaian(listIdUnit: [$param['id_unit']]);
        $idPegawai = array_column($data, 'id_pegawai');
        $dataPengajaran = array_filter($dataPengajaran, function ($item) use ($idPegawai) {
            return in_array($item['id_pegawai'], $idPegawai);
        });

        $listBiodata = Biodata::whereIn('ref_key_pegawai', array_column($data, 'id_pegawai'))->pluck('id', 'ref_key_pegawai')->toArray();

        // map data
        $mappedData = [];
        foreach ($dataPengajaran as $item) {
            if (!in_array($item['id_pegawai'], array_column($data, 'id_pegawai'))) {
                continue;
            }
            if (!isset($mappedData[$item['id_pegawai']])) {
                $dataPegawai = array_filter($data, function ($d) use ($item) {
                    return $d['id_pegawai'] == $item['id_pegawai'];
                });
                $dataPegawai = $dataPegawai[array_key_first($dataPegawai)];
                $mappedData[$item['id_pegawai']] = $item;
                $mappedData[$item['id_pegawai']]['keahlian_dosen'] = $dataPegawai['keahlian_dosen'];
                $mappedData[$item['id_pegawai']]['lembaga_asal'] = $dataPegawai['lembaga_asal'];
                $mappedData[$item['id_pegawai']]['pendidikan_tertinggi'] = $dataPegawai['pendidikan_tertinggi'];
            }
            $mappedData[$item['id_pegawai']]['list_mata_kuliah'][] = $item['nama_mata_kuliah'];
            $mappedData[$item['id_pegawai']]['list_sertifikasi'] =
                array_values(array_map(function ($s) {
                    return $s['jenissertifikasi'];
                }, array_filter($dataSertifikasiDosen, function ($s) use ($item) {
                    return $s['id_pegawai'] == $item['id_pegawai'];
                })));

            $mappedData[$item['id_pegawai']]['total_sks'] = ($mappedData[$item['id_pegawai']]['total_sks'] ?? 0) + $item['sks_mengajar'];
        }

        return array_map(function ($item) use ($listBiodata) {
            return [
                2 => $listBiodata[$item['id_pegawai']] ?? null,
                3 => null,
                4 => $item['lembaga_asal'],
                5 => $item['pendidikan_tertinggi'],
                6 => $item['keahlian_dosen'],
                7 => implode(', ', $item['list_sertifikasi'] ?? []),
                8 => implode(', ', $item['list_mata_kuliah'] ?? []),
                9 => $item['total_sks'] ?? 0
            ];
        }, array_values($mappedData));
    }

    public function get11a4($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $periode = [($ts . '1'), ($ts . '2')];

        $data = $this->glossary->rekapDosenPenghitungRasioKepegawaian(listIdUnit: [$param['id_unit']], listIdPeriode: $periode);
        $dataBebanPengajaran = $this->glossary->rekapBebanPengajaranDosenSKSAkademik(listIdUnit: [$param['id_unit']], listIdPeriode: $periode);

        $listBiodata = Biodata::whereIn('ref_key_pegawai', array_column($data, 'id_pegawai'))->pluck('id', 'ref_key_pegawai')->toArray();

        $mappedData = [];
        foreach ($data as $index => $item) {
            if (!isset($mappedData[$item['id_pegawai']])) {
                $mappedData[$item['id_pegawai']] = $item;
            }
            $mappedData[$item['id_pegawai']]['list_beban_pengajaran'] = array_filter($dataBebanPengajaran, function ($b) use ($item) {
                return $b['id_pegawai'] == $item['id_pegawai'];
            });
            $mappedData[$item['id_pegawai']]['total_sks_ps_diakreditasi'] = array_reduce($mappedData[$item['id_pegawai']]['list_beban_pengajaran'], function ($carry, $b) {
                return $carry + $b['sks_ps_diakreditasi'];
            }, 0);
            $mappedData[$item['id_pegawai']]['total_sks_ps_lain'] = array_reduce($mappedData[$item['id_pegawai']]['list_beban_pengajaran'], function ($carry, $b) {
                return $carry + $b['sks_ps_lain'];
            }, 0);
        }

        return array_map(function ($item) use ($listBiodata) {
            return [
                2 => $listBiodata[$item['id_pegawai']] ?? null,
                3 => $item['dosen_dprps'],
                4 => $item['total_sks_ps_diakreditasi'],
                5 => $item['total_sks_ps_lain'],
                6 => null,
                7 => null,
                8 => null,
                9 => null,
                10 => null,
                11 => null,
            ];
        }, array_values($mappedData));
    }

    public function get11a5($param = [])
    {
        $listIdJabatan = ['K1', 'K3', 'K5'];
        $data = $this->glossary->rekapSertifikasiPelatihanTenagaKependidikanKepegawaian($listIdJabatan, [$param['id_unit']]);

        $sertifikasiTendik = [];
        foreach ($data as $item) {
            $sertifikasiTendik[$item['id_fungsional']][] = $item['jenissertifikasi'] . ' - ' . $item['nosertifikasi'];
        }

        $data = $this->glossary->rekapKualifikasiPendidikanTendikKepegawaian($listIdJabatan);
        $jumlahKualifikasi = [];

        foreach ($data as $item) {
            foreach($item as $key => $value) {
                if($key == 'id_fungsional') continue;
                $jumlahKualifikasi[$item['id_fungsional']][$key] = $value;
            }
        }

        $dataTendik = [];
        foreach($listIdJabatan as $id) {
            $dataTendik[$id] = [
                3 => implode('; ', $sertifikasiTendik[$id] ?? []),
                4 => $jumlahKualifikasi[$id]['s1'] ?? null,
                5 => $jumlahKualifikasi[$id]['s2'] ?? null,
                6 => $jumlahKualifikasi[$id]['s3'] ?? null,
                7 => $jumlahKualifikasi[$id]['d4'] ?? null,
                8 => $jumlahKualifikasi[$id]['d3'] ?? null,
            ];
        }

        return [
            1 => [$dataTendik['K5']],
            2 => [$dataTendik['K3']],
            5 => [$dataTendik['K1']],
        ];
    }

    public function get11b61($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $periode = [($ts . '1'), ($ts . '2')];

        $data = $this->glossary->rekapAktivitasPembelajaranMahasiswa(listIdUnit: [$param['id_unit']], listIdPeriode: $periode, apakahMBKM: 1);

        return array_map(function ($item) {
            return [
                2 => $item['nama_jenis_kegiatan'] . ' (' . $item['nim'] . ' - ' . $item['nama'] . ')',
                3 => $item['perusahaan'] ?? $item['universitas'],
                4 => $item['total_sks_mk'],
                5 => null,
            ];
        }, $data);
    }

    public function get11c8a($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $ts1 = $ts - 1;
        $jenjangFilter = ['Prof', 'D1'];
        $tsFilter = [$ts1, $ts];

        $dataMahasiswaBaru = $this->glossary->rekapJumlahMahasiswaBaruPerTahun(listIdUnit: [$param['id_unit']], listTahunAkademik: $tsFilter, listJenjang: $jenjangFilter);
        $dataMahasiswaLulusan = $this->glossary->rekapJumlahLulusanMahasiswaProdiPerTahun(listIdUnit: [$param['id_unit']], listTahunAkademik: $tsFilter, listJenjang: $jenjangFilter);
        $dataMasaStudiLulusan = $this->glossary->rekapDataLulusanMahasiswaMasaStudiPerTahun(listIdUnit: [$param['id_unit']], listTahunAkademik: $tsFilter, listJenjang: $jenjangFilter);

        $ts1MahasiswaBaru = 0;
        $tsMahasiswaBaru = 0;
        foreach ($dataMahasiswaBaru as $item) {
            if ($item['tahun'] == $ts1) {
                $ts1MahasiswaBaru += $item['jml_mhs_baru'];
            } elseif ($item['tahun'] == $ts) {
                $tsMahasiswaBaru += $item['jml_mhs_baru'];
            }
        }
        $ts1MahasiswaLulusan = 0;
        $tsMahasiswaLulusan = 0;
        foreach ($dataMahasiswaLulusan as $item) {
            if ($item['tahun_akademik'] == $ts1) {
                $ts1MahasiswaLulusan += $item['jml_lulusan'];
            } elseif ($item['tahun_akademik'] == $ts) {
                $tsMahasiswaLulusan += $item['jml_lulusan'];
            }
        }
        $groupedTs1MasaStudiLulusan = [];
        $groupedTs0MasaStudiLulusan = [];
        foreach ($dataMasaStudiLulusan as $item) {
            if ($item['tahun_akademik'] == $ts1) {
                $groupedTs1MasaStudiLulusan[] = $item;
            } elseif ($item['tahun_akademik'] == $ts) {
                $groupedTs0MasaStudiLulusan[] = $item;
            }
        }

        // konversi ke bulan, karena masa studi singkat
        foreach ($groupedTs1MasaStudiLulusan as &$item) {
            $item['masa_studi'] = ($item['masa_studi'] * 12);
        }
        foreach ($groupedTs0MasaStudiLulusan as &$item) {
            $item['masa_studi'] = ($item['masa_studi'] * 12);
        }

        $sourceTs1 = array_column($groupedTs1MasaStudiLulusan, 'masa_studi');
        $sourceTs0 = array_column($groupedTs0MasaStudiLulusan, 'masa_studi');

        // Count students where 9 <= masa_studi <= 15 (bulan)
        $sumTs1MasaStudi9to15 = count(array_filter($sourceTs1, function ($v) {
            return $v >= 9 && $v <= 15;
        }));
        $sumTs0MasaStudi9to15 = count(array_filter($sourceTs0, function ($v) {
            return $v >= 9 && $v <= 15;
        }));

        // Count students where 15 < masa_studi <= 24 (bulan)
        $sumTs1MasaStudi15to24 = count(array_filter($sourceTs1, function ($v) {
            return $v > 15 && $v <= 24;
        }));
        $sumTs0MasaStudi15to24 = count(array_filter($sourceTs0, function ($v) {
            return $v > 15 && $v <= 24;
        }));

        return [
            1 => [
                [
                    2 => $ts1MahasiswaBaru,
                    3 => $ts1MahasiswaLulusan,
                    4 => $sumTs1MasaStudi9to15,
                    5 => $sumTs1MasaStudi15to24,
                ]
            ],
            2 => [
                [
                    2 => $tsMahasiswaBaru,
                    3 => $tsMahasiswaLulusan,
                    4 => $sumTs0MasaStudi9to15,
                    5 => $sumTs0MasaStudi15to24,
                ]
            ],
        ];
    }

    public function get11c7($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $lulusan = [];
        $x = 2;
        for($i = $x; $i >= 0; $i--) {
            $nMahasiswa = 0;

            $periode = $ts - $i . '1';
            $data = $this->glossary->rekapMahasiswaLulusanPerProdi($periode, $param['id_unit']);
            foreach($data as $item) {
                if($item['idunit'] == $param['id_unit']) {
                    $nMahasiswa += $item['jumlahlulusan'];
                }
            }

            $periode = $ts - $i . '2';
            $data = $this->glossary->rekapMahasiswaLulusanPerProdi($periode, $param['id_unit']);
            foreach($data as $item) {
                if($item['idunit'] == $param['id_unit']) {
                    $nMahasiswa += $item['jumlahlulusan'];
                }
            }

            $periode = $ts - $i . '3';
            $data = $this->glossary->rekapMahasiswaLulusanPerProdi($periode, $param['id_unit']);
            foreach($data as $item) {
                if($item['idunit'] == $param['id_unit']) {
                    $nMahasiswa += $item['jumlahlulusan'];
                }
            }

            $lulusan[$x] = $nMahasiswa;
            $x++;
        }

        $persentase = [];
        foreach($lulusan as $key => $value) {
            if(isset($lulusan[$key + 1])) {
                if ($lulusan[$key + 1] == 0) {
                    $lulusan[] = 0;
                    break;
                }
                $val = (($lulusan[$key + 1] - $value) / $value) * 100;
                if ($val < 0) {
                    $val = abs($val);
                } else {
                    $val = 0;
                }
                $persentase[] = $val;
            }
        }

        if(count($lulusan) < 4) {
            $rata2 = array_sum($persentase) / count($persentase);
            $lulusan[] = round($rata2, 2);
        }

        return [0 => $lulusan];
    }

    public function get11c8b($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $ts1 = $ts - 1;
        $ts2 = $ts - 2;
        $ts3 = $ts - 3;
        $jenjangFilter = ['D2', 'S2', 'MTr'];
        $tsFilter = [$ts1, $ts2, $ts3];

        $dataMahasiswaBaru = $this->glossary->rekapJumlahMahasiswaBaruPerTahun(listIdUnit: [$param['id_unit']], listTahunAkademik: $tsFilter, listJenjang: $jenjangFilter);
        $dataMahasiswaLulusan = $this->glossary->rekapJumlahLulusanMahasiswaProdiPerTahun(listIdUnit: [$param['id_unit']], listTahunAkademik: $tsFilter, listJenjang: $jenjangFilter);
        $dataMasaStudiLulusan = $this->glossary->rekapDataLulusanMahasiswaMasaStudiPerTahun(listIdUnit: [$param['id_unit']], listTahunAkademik: $tsFilter, listJenjang: $jenjangFilter);

        $ts1MahasiswaBaru = 0;
        $ts2MahasiswaBaru = 0;
        $ts3MahasiswaBaru = 0;
        foreach ($dataMahasiswaBaru as $item) {
            if ($item['tahun'] == $ts1) {
                $ts1MahasiswaBaru += $item['jml_mhs_baru'];
            } elseif ($item['tahun'] == $ts2) {
                $ts2MahasiswaBaru += $item['jml_mhs_baru'];
            } elseif ($item['tahun'] == $ts3) {
                $ts3MahasiswaBaru += $item['jml_mhs_baru'];
            }
        }
        $ts1MahasiswaLulusan = 0;
        $ts2MahasiswaLulusan = 0;
        $ts3MahasiswaLulusan = 0;
        foreach ($dataMahasiswaLulusan as $item) {
            if ($item['tahun_akademik'] == $ts1) {
                $ts1MahasiswaLulusan += $item['jml_lulusan'];
            } elseif ($item['tahun_akademik'] == $ts2) {
                $ts2MahasiswaLulusan += $item['jml_lulusan'];
            } elseif ($item['tahun_akademik'] == $ts3) {
                $ts3MahasiswaLulusan += $item['jml_lulusan'];
            }
        }
        $groupedTs1MasaStudiLulusan = [];
        $groupedTs2MasaStudiLulusan = [];
        $groupedTs3MasaStudiLulusan = [];
        foreach ($dataMasaStudiLulusan as $item) {
            if ($item['tahun_akademik'] == $ts1) {
                $groupedTs1MasaStudiLulusan[] = $item;
            } elseif ($item['tahun_akademik'] == $ts2) {
                $groupedTs2MasaStudiLulusan[] = $item;
            } elseif ($item['tahun_akademik'] == $ts3) {
                $groupedTs3MasaStudiLulusan[] = $item;
            }
        }

        $sourceTs1 = array_column($groupedTs1MasaStudiLulusan, 'masa_studi');
        $sourceTs2 = array_column($groupedTs2MasaStudiLulusan, 'masa_studi');
        $sourceTs3 = array_column($groupedTs3MasaStudiLulusan, 'masa_studi');

        // Count students where 1.5 <= masa_studi <= 2.5 (tahun)
        $sumTs1MasaStudi1_5to2_5 = count(array_filter($sourceTs1, function ($v) {
            return $v >= 1.5 && $v <= 2.5;
        }));
        $sumTs2MasaStudi1_5to2_5 = count(array_filter($sourceTs2, function ($v) {
            return $v >= 1.5 && $v <= 2.5;
        }));
        $sumTs3MasaStudi1_5to2_5 = count(array_filter($sourceTs3, function ($v) {
            return $v >= 1.5 && $v <= 2.5;
        }));

        // Count students where 2.5 < masa_studi <= 4 (tahun)
        $sumTs1MasaStudi2_5to4 = count(array_filter($sourceTs1, function ($v) {
            return $v > 2.5 && $v <= 4;
        }));
        $sumTs2MasaStudi2_5to4 = count(array_filter($sourceTs2, function ($v) {
            return $v > 2.5 && $v <= 4;
        }));
        $sumTs3MasaStudi2_5to4 = count(array_filter($sourceTs3, function ($v) {
            return $v > 2.5 && $v <= 4;
        }));

        return [
            1 => [
                [
                    2 => $ts3MahasiswaBaru,
                    3 => $ts3MahasiswaLulusan,
                    4 => $sumTs3MasaStudi1_5to2_5,
                    5 => $sumTs3MasaStudi2_5to4,
                ]
            ],
            2 => [
                [
                    2 => $ts2MahasiswaBaru,
                    3 => $ts2MahasiswaLulusan,
                    4 => $sumTs2MasaStudi1_5to2_5,
                    5 => $sumTs2MasaStudi2_5to4,
                ]
            ],
            3 => [
                [
                    2 => $ts1MahasiswaBaru,
                    3 => $ts1MahasiswaLulusan,
                    4 => $sumTs1MasaStudi1_5to2_5,
                    5 => $sumTs1MasaStudi2_5to4,
                ]
            ],
        ];
    }

    public function get11c8c($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $ts2 = $ts - 2;
        $ts3 = $ts - 3;
        $ts4 = $ts - 4;
        $ts5 = $ts - 5;
        $jenjangFilter = ['D3'];
        $tsFilter = [$ts2, $ts3, $ts4, $ts5];

        $dataMahasiswaBaru = $this->glossary->rekapJumlahMahasiswaBaruPerTahun(listIdUnit: [$param['id_unit']], listTahunAkademik: $tsFilter, listJenjang: $jenjangFilter);
        $dataMahasiswaLulusan = $this->glossary->rekapJumlahLulusanMahasiswaProdiPerTahun(listIdUnit: [$param['id_unit']], listTahunAkademik: $tsFilter, listJenjang: $jenjangFilter);
        $dataMasaStudiLulusan = $this->glossary->rekapDataLulusanMahasiswaMasaStudiPerTahun(listIdUnit: [$param['id_unit']], listTahunAkademik: $tsFilter, listJenjang: $jenjangFilter);

        $ts2MahasiswaBaru = 0;
        $ts3MahasiswaBaru = 0;
        $ts4MahasiswaBaru = 0;
        $ts5MahasiswaBaru = 0;
        foreach ($dataMahasiswaBaru as $item) {
            if ($item['tahun'] == $ts2) {
                $ts2MahasiswaBaru += $item['jml_mhs_baru'];
            } elseif ($item['tahun'] == $ts3) {
                $ts3MahasiswaBaru += $item['jml_mhs_baru'];
            } elseif ($item['tahun'] == $ts4) {
                $ts4MahasiswaBaru += $item['jml_mhs_baru'];
            } elseif ($item['tahun'] == $ts5) {
                $ts5MahasiswaBaru += $item['jml_mhs_baru'];
            }
        }

        $ts2MahasiswaLulusan = 0;
        $ts3MahasiswaLulusan = 0;
        $ts4MahasiswaLulusan = 0;
        $ts5MahasiswaLulusan = 0;
        foreach ($dataMahasiswaLulusan as $item) {
            if ($item['tahun_akademik'] == $ts2) {
                $ts2MahasiswaLulusan += $item['jml_lulusan'];
            } elseif ($item['tahun_akademik'] == $ts3) {
                $ts3MahasiswaLulusan += $item['jml_lulusan'];
            } elseif ($item['tahun_akademik'] == $ts4) {
                $ts4MahasiswaLulusan += $item['jml_lulusan'];
            } elseif ($item['tahun_akademik'] == $ts5) {
                $ts5MahasiswaLulusan += $item['jml_lulusan'];
            }
        }

        $groupedTs2MasaStudiLulusan = [];
        $groupedTs3MasaStudiLulusan = [];
        $groupedTs4MasaStudiLulusan = [];
        $groupedTs5MasaStudiLulusan = [];
        foreach ($dataMasaStudiLulusan as $item) {
            if ($item['tahun_akademik'] == $ts2) {
                $groupedTs2MasaStudiLulusan[] = $item;
            } elseif ($item['tahun_akademik'] == $ts3) {
                $groupedTs3MasaStudiLulusan[] = $item;
            } elseif ($item['tahun_akademik'] == $ts4) {
                $groupedTs4MasaStudiLulusan[] = $item;
            } elseif ($item['tahun_akademik'] == $ts5) {
                $groupedTs5MasaStudiLulusan[] = $item;
            }
        }

        $sourceTs2 = array_column($groupedTs2MasaStudiLulusan, 'masa_studi');
        $sourceTs3 = array_column($groupedTs3MasaStudiLulusan, 'masa_studi');
        $sourceTs4 = array_column($groupedTs4MasaStudiLulusan, 'masa_studi');
        $sourceTs5 = array_column($groupedTs5MasaStudiLulusan, 'masa_studi');

        // Count students where 2.5 <= masa_studi <= 3.5 (tahun)
        $sumTs2MasaStudi2_5to3_5 = count(array_filter($sourceTs2, function ($v) {
            return $v >= 2.5 && $v <= 3.5;
        }));
        $sumTs3MasaStudi2_5to3_5 = count(array_filter($sourceTs3, function ($v) {
            return $v >= 2.5 && $v <= 3.5;
        }));
        $sumTs4MasaStudi2_5to3_5 = count(array_filter($sourceTs4, function ($v) {
            return $v >= 2.5 && $v <= 3.5;
        }));
        $sumTs5MasaStudi2_5to3_5 = count(array_filter($sourceTs5, function ($v) {
            return $v >= 2.5 && $v <= 3.5;
        }));

        // Count students where 3.5 <= masa_studi <= 6 (tahun)
        $sumTs2MasaStudi3_5to6 = count(array_filter($sourceTs2, function ($v) {
            return $v >= 3.5 && $v <= 6;
        }));
        $sumTs3MasaStudi3_5to6 = count(array_filter($sourceTs3, function ($v) {
            return $v >= 3.5 && $v <= 6;
        }));
        $sumTs4MasaStudi3_5to6 = count(array_filter($sourceTs4, function ($v) {
            return $v >= 3.5 && $v <= 6;
        }));
        $sumTs5MasaStudi3_5to6 = count(array_filter($sourceTs5, function ($v) {
            return $v >= 3.5 && $v <= 6;
        }));

        return [
            1 => [
                [
                    2 => $ts5MahasiswaBaru,
                    3 => $ts5MahasiswaLulusan,
                    4 => $sumTs5MasaStudi2_5to3_5,
                    5 => $sumTs5MasaStudi3_5to6,
                ]
            ],
            2 => [
                [
                    2 => $ts4MahasiswaBaru,
                    3 => $ts4MahasiswaLulusan,
                    4 => $sumTs4MasaStudi2_5to3_5,
                    5 => $sumTs4MasaStudi3_5to6,
                ]
            ],
            3 => [
                [
                    2 => $ts3MahasiswaBaru,
                    3 => $ts3MahasiswaLulusan,
                    4 => $sumTs3MasaStudi2_5to3_5,
                    5 => $sumTs3MasaStudi3_5to6,
                ]
            ],
            4 => [
                [
                    2 => $ts2MahasiswaBaru,
                    3 => $ts2MahasiswaLulusan,
                    4 => $sumTs2MasaStudi2_5to3_5,
                    5 => $sumTs2MasaStudi3_5to6,
                ]
            ],
        ];
    }

    public function get11c8d($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $ts3 = $ts - 3;
        $ts4 = $ts - 4;
        $ts5 = $ts - 5;
        $ts6 = $ts - 6;
        $ts7 = $ts - 7;
        $jenjangFilter = ['S1'];
        $tsFilter = [$ts3, $ts4, $ts5, $ts6, $ts7];

        $dataMahasiswaBaru = $this->glossary->rekapJumlahMahasiswaBaruPerTahun(listIdUnit: [$param['id_unit']], listTahunAkademik: $tsFilter, listJenjang: $jenjangFilter);
        $dataMahasiswaLulusan = $this->glossary->rekapJumlahLulusanMahasiswaProdiPerTahun(listIdUnit: [$param['id_unit']], listTahunAkademik: $tsFilter, listJenjang: $jenjangFilter);
        $dataMasaStudiLulusan = $this->glossary->rekapDataLulusanMahasiswaMasaStudiPerTahun(listIdUnit: [$param['id_unit']], listTahunAkademik: $tsFilter, listJenjang: $jenjangFilter);

        $ts3MahasiswaBaru = 0;
        $ts4MahasiswaBaru = 0;
        $ts5MahasiswaBaru = 0;
        $ts6MahasiswaBaru = 0;
        $ts7MahasiswaBaru = 0;
        foreach ($dataMahasiswaBaru as $item) {
            if ($item['tahun'] == $ts3) {
                $ts3MahasiswaBaru += $item['jml_mhs_baru'];
            } elseif ($item['tahun'] == $ts4) {
                $ts4MahasiswaBaru += $item['jml_mhs_baru'];
            } elseif ($item['tahun'] == $ts5) {
                $ts5MahasiswaBaru += $item['jml_mhs_baru'];
            } elseif ($item['tahun'] == $ts6) {
                $ts6MahasiswaBaru += $item['jml_mhs_baru'];
            } elseif ($item['tahun'] == $ts7) {
                $ts7MahasiswaBaru += $item['jml_mhs_baru'];
            }
        }

        $ts3MahasiswaLulusan = 0;
        $ts4MahasiswaLulusan = 0;
        $ts5MahasiswaLulusan = 0;
        $ts6MahasiswaLulusan = 0;
        $ts7MahasiswaLulusan = 0;
        foreach ($dataMahasiswaLulusan as $item) {
            if ($item['tahun_akademik'] == $ts3) {
                $ts3MahasiswaLulusan += $item['jml_lulusan'];
            } elseif ($item['tahun_akademik'] == $ts4) {
                $ts4MahasiswaLulusan += $item['jml_lulusan'];
            } elseif ($item['tahun_akademik'] == $ts5) {
                $ts5MahasiswaLulusan += $item['jml_lulusan'];
            } elseif ($item['tahun_akademik'] == $ts6) {
                $ts6MahasiswaLulusan += $item['jml_lulusan'];
            } elseif ($item['tahun_akademik'] == $ts7) {
                $ts7MahasiswaLulusan += $item['jml_lulusan'];
            }
        }

        $groupedTs3MasaStudiLulusan = [];
        $groupedTs4MasaStudiLulusan = [];
        $groupedTs5MasaStudiLulusan = [];
        $groupedTs6MasaStudiLulusan = [];
        $groupedTs7MasaStudiLulusan = [];
        foreach ($dataMasaStudiLulusan as $item) {
            if ($item['tahun_akademik'] == $ts3) {
                $groupedTs3MasaStudiLulusan[] = $item;
            } elseif ($item['tahun_akademik'] == $ts4) {
                $groupedTs4MasaStudiLulusan[] = $item;
            } elseif ($item['tahun_akademik'] == $ts5) {
                $groupedTs5MasaStudiLulusan[] = $item;
            } elseif ($item['tahun_akademik'] == $ts6) {
                $groupedTs6MasaStudiLulusan[] = $item;
            } elseif ($item['tahun_akademik'] == $ts7) {
                $groupedTs7MasaStudiLulusan[] = $item;
            }
        }

        $sourceTs3 = array_column($groupedTs3MasaStudiLulusan, 'masa_studi');
        $sourceTs4 = array_column($groupedTs4MasaStudiLulusan, 'masa_studi');
        $sourceTs5 = array_column($groupedTs5MasaStudiLulusan, 'masa_studi');
        $sourceTs6 = array_column($groupedTs6MasaStudiLulusan, 'masa_studi');
        $sourceTs7 = array_column($groupedTs7MasaStudiLulusan, 'masa_studi');

        // Count students where 3.5 <= masa_studi <= 6 (tahun)
        $sumTs3MasaStudi3_5to6 = count(array_filter($sourceTs3, function ($v) {
            return $v >= 3.5 && $v <= 6;
        }));
        $sumTs4MasaStudi3_5to6 = count(array_filter($sourceTs4, function ($v) {
            return $v >= 3.5 && $v <= 6;
        }));
        $sumTs5MasaStudi3_5to6 = count(array_filter($sourceTs5, function ($v) {
            return $v >= 3.5 && $v <= 6;
        }));
        $sumTs6MasaStudi3_5to6 = count(array_filter($sourceTs6, function ($v) {
            return $v >= 3.5 && $v <= 6;
        }));
        $sumTs7MasaStudi3_5to6 = count(array_filter($sourceTs7, function ($v) {
            return $v >= 3.5 && $v <= 6;
        }));

        // Count students where 6 <= masa_studi <= 8 (tahun)
        $sumTs3MasaStudi6to8 = count(array_filter($sourceTs3, function ($v) {
            return $v >= 6 && $v <= 8;
        }));
        $sumTs4MasaStudi6to8 = count(array_filter($sourceTs4, function ($v) {
            return $v >= 6 && $v <= 8;
        }));
        $sumTs5MasaStudi6to8 = count(array_filter($sourceTs5, function ($v) {
            return $v >= 6 && $v <= 8;
        }));
        $sumTs6MasaStudi6to8 = count(array_filter($sourceTs6, function ($v) {
            return $v >= 6 && $v <= 8;
        }));
        $sumTs7MasaStudi6to8 = count(array_filter($sourceTs7, function ($v) {
            return $v >= 6 && $v <= 8;
        }));

        return [
            1 => [
                [
                    2 => $ts7MahasiswaBaru,
                    3 => $ts7MahasiswaLulusan,
                    4 => $sumTs7MasaStudi3_5to6,
                    5 => $sumTs7MasaStudi6to8,
                ]
            ],
            2 => [
                [
                    2 => $ts6MahasiswaBaru,
                    3 => $ts6MahasiswaLulusan,
                    4 => $sumTs6MasaStudi3_5to6,
                    5 => $sumTs6MasaStudi6to8,
                ]
            ],
            3 => [
                [
                    2 => $ts5MahasiswaBaru,
                    3 => $ts5MahasiswaLulusan,
                    4 => $sumTs5MasaStudi3_5to6,
                    5 => $sumTs5MasaStudi6to8,
                ]
            ],
            4 => [
                [
                    2 => $ts4MahasiswaBaru,
                    3 => $ts4MahasiswaLulusan,
                    4 => $sumTs4MasaStudi3_5to6,
                    5 => $sumTs4MasaStudi6to8,
                ]
            ],
            5 => [
                [
                    2 => $ts3MahasiswaBaru,
                    3 => $ts3MahasiswaLulusan,
                    4 => $sumTs3MasaStudi3_5to6,
                    5 => $sumTs3MasaStudi6to8,
                ]
            ],
        ];
    }

    public function get11d13($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $ts1 = $ts - 1;
        $ts2 = $ts - 2;
        $tsFilter = [$ts, $ts1, $ts2];

        $data = $this->glossary->rekapPrestasiMahasiswaBerdasarkanTahunAkademik($tsFilter, null, [$param['id_unit']]);

        return array_map(function ($item) use ($param) {
            $tanggal = explode('-', $item['tanggal_aktivitas']);
            return [
                2 => $item['nama_kegiatan'],
                3 => $tanggal[0],
                5 => $item['tingkat_prestasi'] == 'Internasional' ? true : false,
                4 => $item['tingkat_prestasi'] == 'Nasional' ? true : false,
                6 => $item['tingkat_prestasi'] != 'Nasional' && $item['tingkat_prestasi'] != 'Internasional' ? true : false,
                7 => $item['peringkat'],
            ];
        }, $data);
    }

    public function get11d11($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $unit = UnitKerja::where(['jenis_unit' => 'U', 'info_level' => '0', UnitKerja::DELETED_AT => null])->first();
        $kodeDikti = $unit->kode_unit;

        $this->initTracerStudyConnection();

        $data = $this->glossary->rekapKepuasanPenggunaLulusan($kodeDikti, [$ts], [$param['id_unit']]);
        $indikatorFilter = [
            'integritas / etika berperilaku / moral',
            'kinerja/keahlian berdasarkan bidang ilmu (profesionalisme / kompetensi utama)',
            'kemampuan berbahasa asing',
            'kemampuan penggunaan teknologi informasi',
            'kemampuan berkomunikasi',
            'kemampuan bekerjasama dalam tim',
            'pengembangan diri ',
        ];

        $kepuasan = [];
        foreach($indikatorFilter as $index => $indikator){
            $sample = array_filter($data, function ($item) use ($indikator) {
                return $item['indikator'] == $indikator;
            });

            $sample = Arr::mapWithKeys($sample, function ($value, $key) {
                return [$value['kategori'] => $value['total']];
            });

            $kepuasan[$index + 1] = [[
                3 => $sample['sangat baik'] ?? 0,
                4 => $sample['baik'] ?? 0,
                5 => $sample['cukup'] ?? 0,
                6 => $sample['kurang'] ?? 0,
            ]];
        }

        return $kepuasan;
    }

    public function get11d12($param = [])
    {
        $data = $this->glossary->rekapJumlahMahasiswaBaruNonAsingPerPeriode();
        $ts = $param['tahun_audit'] - 1;

        $jumlahMahasiswa = [];
        $x = 2;
        for($i = $x; $i >= 0; $i--) {
            $nMahasiswa = 0;

            $periode1 = $ts - $i . '1';
            $periode2 = $ts - ($i + 1) . '2';
            $mahasiswaBaru = array_filter($data, function ($item) use ($param, $periode1, $periode2) {
                return $item['idunit'] == $param['id_unit'] && ($item['periode_akademik'] == $periode1 || $item['periode_akademik'] == $periode2);
            });

            foreach($mahasiswaBaru as $item) {
                $nMahasiswa += $item['jml_mhs_baru'];
            }

            $jumlahMahasiswa[$x] = $nMahasiswa;
            $x++;
        }

        $persentase = [];
        foreach($jumlahMahasiswa as $key => $value) {
            if(isset($jumlahMahasiswa[$key + 1])) {
                if ($jumlahMahasiswa[$key + 1] == 0) {
                    $jumlahMahasiswa[] = 0;
                    break;
                }
                $val = (($jumlahMahasiswa[$key + 1] - $value) / $value) * 100;
                if ($val < 0) {
                    $val = abs($val);
                } else {
                    $val = 0;
                }
                $persentase[] = $val;
            }
        }

        if(count($jumlahMahasiswa) < 4) {
            $rata2 = array_sum($persentase) / count($persentase);
            $jumlahMahasiswa[] = round($rata2, 2);
        }

        return [0 => $jumlahMahasiswa];
    }

    public function get11d14($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $ts2 = $ts - 2;
        $ts3 = $ts - 3;
        $ts4 = $ts - 4;
        $tsFilter = [$ts2, $ts3, $ts4];

        $unit = UnitKerja::where(['jenis_unit' => 'U', 'info_level' => '0', UnitKerja::DELETED_AT => null])->first();
        $kodeDikti = $unit->kode_unit;

        $this->initTracerStudyConnection();
        $aData = $this->glossary->rekapWaktuTungguLulusan($kodeDikti, $tsFilter, [$param['id_unit']]);

        $data = [];
        $index = 3;
        foreach($tsFilter as $tahun) {
            $detail = array_filter($aData, function ($item) use ($tahun) {
                return $item['tahun_lulus'] == $tahun;
            });
            $detail = array_values($detail)[0] ?? [];

            if(!empty($detail)) {
                $data[$index] = [[
                    2 => $detail['jumlah_lulusan'],
                    3 => $detail['jumlah_lulusan_terlacak'],
                    4 => $detail['rerata_waktu_tunggu_bulan'],
                    5 => $detail['persentase_kesesuaian_bidang_kerja'],
                ]];
            }

            $index--;
        }

        ksort($data);

        return $data;
    }

    public function get121($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $ts1 = $ts - 1;
        $ts2 = $ts - 2;
        $tsFilter = [$ts2, $ts1, $ts];

        $data = $this->glossary->rekapPenelitianDPRPSDenganMahasiswaKepegawaian([$param['id_unit']], $tsFilter);

        $listBiodata = Biodata::whereIn('ref_key_pegawai', array_column($data, 'id_pegawai'))->pluck('id', 'ref_key_pegawai')->toArray();

        return array_map(function ($item) use ($listBiodata) {
            return [
                2 => $listBiodata[$item['id_pegawai']] ?? null,
                3 => $item['tema_penelitian'],
                4 => $item['nama_mahasiswa'],
                5 => $item['judul_penelitian'],
                6 => $item['tahun_penelitian'],
            ];
        }, $data);
    }

    public function get122($param = [])
    {
        $seminarMahasiswa = $this->glossary->rekapJumlahPublikasiSeminarMahasiswaPerTahun();
        $seminarPegawai = $this->glossary->rekapJumlahPublikasiSeminarPegawaiPerTahun();

        $ts = $param['tahun_audit'] - 1;
        $x = 5;
        for($tahun = $ts; $tahun >= $ts - 2; $tahun--) {
            $jurnalNasional[$x] =
                $this->glossary->jumlahJurnalNasionalMahasiswa($tahun, $param['id_unit']) +
                $this->glossary->jumlahJurnalNasionalPegawai($tahun, $param['id_unit']);

            $jurnalNasionalTerakreditasi[$x] =
                $this->glossary->jumlahJurnalNasionalMahasiswaTerakreditasi($tahun, $param['id_unit']) +
                $this->glossary->jumlahJurnalNasionalPegawaiTerakreditasi($tahun, $param['id_unit']);

            $jurnalInternasional[$x] =
                $this->glossary->jumlahJurnalInternasionalMahasiswa($tahun, $param['id_unit']) +
                $this->glossary->jumlahJurnalInternasionalPegawai($tahun, $param['id_unit']);

            $jurnalInternasionalBereputasi[$x] =
                $this->glossary->jumlahJurnalInternasionalMahasiswaBereputasi($tahun, $param['id_unit']) +
                $this->glossary->jumlahJurnalInternasionalPegawaiBereputasi($tahun, $param['id_unit']);

            $seminarMahasiswaFiltered = array_filter($seminarMahasiswa, function ($value) use ($tahun, $param) {
                return $value['tahun'] == $tahun && $value['idunit'] == $param['id_unit'];
            });
            $seminarPegawaiFiltered =  array_filter($seminarPegawai, function ($value) use ($tahun, $param) {
                return $value['tahun'] == $tahun && $value['idunit'] == $param['id_unit'];
            });

            $nSeminarMahasiswaNasional = 0;
            $nSeminarMahasiswaInternasional = 0;
            foreach($seminarMahasiswaFiltered as $item) {
                $nSeminarMahasiswaNasional += ($item['jml_prosiding_seminar_nasional'] + $item['jml_poster_seminar_nasional']);
                $nSeminarMahasiswaInternasional += ($item['jml_prosiding_seminar_internasional'] + $item['jml_poster_seminar_internasional']);
            }

            $nSeminarPegawaiNasional = 0;
            $nSeminarPegawaiInternasional = 0;
            foreach($seminarPegawaiFiltered as $item) {
                $nSeminarPegawaiNasional += ($item['jml_prosiding_seminar_nasional'] + $item['jml_poster_seminar_nasional']);
                $nSeminarPegawaiInternasional += ($item['jml_prosiding_seminar_internasional'] + $item['jml_poster_seminar_internasional']);
            }

            $seminarNasional[$x] = $nSeminarMahasiswaNasional + $nSeminarPegawaiNasional;
            $seminarInternasional[$x] = $nSeminarMahasiswaInternasional + $nSeminarPegawaiInternasional;

            $x--;
        }

        return [
            1 => [$jurnalNasional],
            2 => [$jurnalNasionalTerakreditasi],
            3 => [$jurnalInternasional],
            4 => [$jurnalInternasionalBereputasi],
            5 => [[]],
            6 => [$seminarNasional],
            7 => [$seminarInternasional],
            8 => [[]],
            9 => [[]],
            10 => [[]],
        ];
    }

    public function get123($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $ts1 = $ts - 1;
        $ts2 = $ts - 2;
        $tsFilter = [$ts2, $ts1, $ts];

        $paten = $this->glossary->rekapLuaranPatenDPRPSBersamaMahasiswaKepegawaian([$param['id_unit']], $tsFilter);
        $paten = array_map(function ($item) {
            return [
                2 => $item['judul'],
                3 => $item['tahun'],
                4 => $item['keteranganpaten'],
            ];
        }, $paten);
        $paten = array_values(array_unique($paten, SORT_REGULAR));

        $hakCipta = $this->glossary->rekapLuaranHakCiptaDPRPSBersamaMahasiswaKepegawaian([$param['id_unit']], $tsFilter);
        $hakCipta = array_map(function ($item) {
            return [
                2 => $item['judul'],
                3 => $item['tahun'],
                4 => $item['keteranganpaten'],
            ];
        }, $hakCipta);
        $hakCipta = array_values(array_unique($hakCipta, SORT_REGULAR));

        $karya = $this->glossary->rekapLuaranKaryaDPRPSBersamaMahasiswaKepegawaian([$param['id_unit']], $tsFilter);
        $karya = array_map(function ($item) {
            return [
                2 => $item['judul'],
                3 => $item['tahun'],
                4 => $item['keteranganpaten'],
            ];
        }, $karya);
        $karya = array_values(array_unique($karya, SORT_REGULAR));

        $buku = $this->glossary->rekapLuaranBukuDPRPSBersamaMahasiswaKepegawaian([$param['id_unit']], $tsFilter);
        $buku = array_map(function ($item) {
            return [
                2 => $item['judul'],
                3 => $item['tahun'],
                4 => $item['keteranganpaten'],
            ];
        }, $buku);
        $buku = array_values(array_unique($buku, SORT_REGULAR));

        return [
            1 => $paten,
            2 => $hakCipta,
            3 => $karya,
            4 => $buku,
        ];
    }

    public function get124($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $ts1 = $ts - 1;
        $ts2 = $ts - 2;
        $tsFilter = [$ts2, $ts1, $ts];

        $data = $this->glossary->rekapRekognisiDPRPSKepegawaian([$param['id_unit']], $tsFilter);

        $listBiodata = Biodata::whereIn('ref_key_pegawai', array_column($data, 'id_pegawai'))->pluck('id', 'ref_key_pegawai')->toArray();

        return array_map(function ($item) use ($listBiodata) {
            return [
                2 => $listBiodata[$item['id_pegawai']] ?? null,
                3 => $item['keahlian_dosen'],
                4 => $item['deskripsi_rekognisi'],
                5 => in_array($item['kode_tingkat'], ['L', 'D', 'R']), // tingkat_wilayah
                6 => in_array($item['kode_tingkat'], ['N']), // tingkat_nasional
                7 => in_array($item['kode_tingkat'], ['I']), // tingkat_internasional
                8 => $item['tahun'],
            ];
        }, $data);
    }

    public function getXREF1($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $ts1 = $ts - 1;
        $ts2 = $ts - 2;
        $ts3 = $ts - 3;
        $tsFilter = [$ts, $ts1, $ts2, $ts3];

        $data = [];
        $index = 2;
        foreach ($tsFilter as $tahun) {
            $nMahasiswa = 0;

            $periode = $tahun . '1';
            $nMahasiswa += $this->glossary->jumlahMahasiswaAktif($periode, $param['id_unit']);
            $periode = $tahun . '2';
            $nMahasiswa += $this->glossary->jumlahMahasiswaAktif($periode, $param['id_unit']);
            $periode = $tahun . '3';
            $nMahasiswa += $this->glossary->jumlahMahasiswaAktif($periode, $param['id_unit']);

            $data[$index] = $nMahasiswa;
            $index++;
        }

        return [$data];
    }

    public function getXREF2($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $ts1 = $ts - 1;
        $ts2 = $ts - 2;
        $ts3 = $ts - 3;
        $ts4 = $ts - 4;
        $tsFilter = [$ts, $ts1, $ts2, $ts3, $ts4];

        $unit = UnitKerja::where(['jenis_unit' => 'U', 'info_level' => '0', UnitKerja::DELETED_AT => null])->first();
        $kodeDikti = $unit->kode_unit;

        $this->initTracerStudyConnection();
        $data = $this->glossary->rekapWaktuTungguLulusanPerMahasiswa($kodeDikti, $tsFilter, [$param['id_unit']]);

        $statusFilter = [
            'Bekerja',
            'Mencari Kerja',
            'Berwirausaha',
            'Wiraswasta',
            'Lanjut Studi',
        ];

        $data = array_values(array_filter($data, function ($item) use ($statusFilter) {
            return in_array($item['status_setelah_lulus'], $statusFilter);
        }));

        return array_map(function ($item) {
            return [
                2 => $item['nim'],
                3 => $item['nama_lulusan'],
                4 => $item['tahun_lulus'],
                5 => $item['status_setelah_lulus'],
                6 => $item['waktu_tunggu_bulan'],
            ];
        }, $data);
    }

    public function getXREF3($param = [])
    {
        $ts = $param['tahun_audit'] - 1;
        $ts1 = $ts - 1;
        $ts2 = $ts - 2;
        $tsFilter = [$ts, $ts1, $ts2];

        $data = $this->glossary->rekapPenelitianDPRKepegawaian([$param['id_unit']], $tsFilter);

        return array_map(function ($item) {
            return [
                2 => $item['nama_lengkap_dosen'],
                3 => $item['nip_dosen'],
                4 => $item['tema_penelitian'],
                5 => $item['judul_penelitian'],
                6 => $item['tahun_penelitian'],
            ];
        }, $data);
    }
}
