<?php

namespace Modules\SPMI\Data\FillingGetData;

use Modules\Core\Helpers\Cstr;
use Modules\Core\Helpers\SiakadV1;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\Pegawai;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Helpers\KarirLinkAPI;

class IAPS9Kriteria
{
    /**
     * Normalize jabatan akademik by removing the parenthetical number
     * e.g., "Lektor (200)" -> "Lektor", "Guru Besar (850)" -> "Guru Besar"
     */
    private function normalizeJabatanAkademik($jabatan)
    {
        if (empty($jabatan)) {
            return $jabatan;
        }
        return trim(preg_replace('/\s*\(\d+\)$/', '', $jabatan));
    }

    // 2a, 8c. 1, 8c.2
    public function get2a($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit'], 5);

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahunajaran between' => [$tahunMin, $tahunMax],
        ];

        list($err, $arrMhsAktif) = (new SiakadV1)->getDataFromView('akreditasicloud.v_mahasiswaaktif', $filters);
        list($err, $arrKuotaPendaftar) = (new SiakadV1)->getDataFromView('akreditasicloud.v_kuotapendaftar', $filters);
        list($err, $arrPendaftar) = (new SiakadV1)->getDataFromView('akreditasicloud.v_pendaftar', $filters);

        // mapping data sesuai prodi dan periode
        $arrDayaTampung = Cstr::toMap('tahunajaran', 'kuota', $arrKuotaPendaftar);
        $arrDataPendaftar = Cstr::toMapObject('tahunajaran', $arrPendaftar);
        $arrDataMahasiswa = [];
        foreach ($arrMhsAktif as $row) {
            $arrDataMahasiswa[$row['idunit']][$row['tahunajaran']]['nontransfer'][] = $row['reguler'];
            $arrDataMahasiswa[$row['idunit']][$row['tahunajaran']]['transfer'][] = $row['transfer'];
            $arrDataMahasiswa[$row['idunit']][$row['tahunajaran']]['asing'][] = $row['asing'];
            $arrDataMahasiswa[$row['idunit']][$row['tahunajaran']]['mahasiswa'][] = ($row['reguler'] + $row['transfer'] + $row['asing']);
            $arrDataMahasiswa[$row['idunit']][$row['tahunajaran']]['regulertransfer'][] = ($row['reguler'] + $row['transfer']);
            $arrDataMahasiswa[$row['idunit']][$row['tahunajaran']]['regulerasing'][] = ($row['reguler'] + $row['asing']);

            if ($row['angkatan'] == $row['tahunajaran']) {
                $arrDataMahasiswa[$row['idunit']][$row['tahunajaran']]['baru_nontransfer'][] = $row['reguler'];
                $arrDataMahasiswa[$row['idunit']][$row['tahunajaran']]['baru_transfer'][] = $row['transfer'];
                $arrDataMahasiswa[$row['idunit']][$row['tahunajaran']]['baru_asing'][] = $row['asing'];
            }
        }

        $i = 1;
        for ($ts = $tahunMin; $ts <= $tahunMax; $ts++) {
            $arrMahasiswa = $arrDataMahasiswa[$studyProgram['kode_unit']][$ts] ?? null;

            $arrData[$i][0][2] = $arrDayaTampung[$ts] ?? null;
            $arrData[$i][0][3] = $arrDataPendaftar[$ts]['pendaftar'] ?? null;
            $arrData[$i][0][4] = $arrDataPendaftar[$ts]['lulus'] ?? null;
            $arrData[$i][0][5] = isset($arrMahasiswa['baru_nontransfer']) ? array_sum($arrMahasiswa['baru_nontransfer']) : null;
            $arrData[$i][0][6] = isset($arrMahasiswa['baru_transfer']) ? array_sum($arrMahasiswa['baru_transfer']) : null;
            $arrData[$i][0][7] = isset($arrMahasiswa['nontransfer']) ? array_sum($arrMahasiswa['nontransfer']) : null;
            $arrData[$i][0][8] = isset($arrMahasiswa['transfer']) ? array_sum($arrMahasiswa['transfer']) : null;

            $i++;
        }

        return (array) $arrData;
    }

    public function get2b($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit']);
        $tahunMin++;

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahunajaran between' => [$tahunMin, $tahunMax],
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('akreditasicloud.v_datamahasiswaaktif', $filters);

        $listUnits = [];
        $listTahun = [$tahunMin, $tahunMin + 1, $tahunMax];

        $arrMhsAktif = [];
        foreach ($data as $row) {
            $key = $row['idnegara'] == 'IDN' ? 'nonasing' : 'asing';

            if (!isset($arrMhsAktif[$key][$row['idunit']][$row['tahunajaran']])) {
                $arrMhsAktif[$key][$row['idunit']][$row['tahunajaran']] = 0;
            }

            $arrMhsAktif[$key][$row['idunit']][$row['tahunajaran']]++;

            if (!in_array($row['idunit'], $listUnits)) {
                $listUnits[] = $row['idunit'];
            }
        }

        $json = [];
        $temp = [];

        // merge data
        foreach ($listUnits as $idunit) {
            foreach ($listTahun as $tahun) {
                foreach ($arrMhsAktif as $key => $arr) {
                    $temp[$idunit][$tahun][$key] = $arr[$idunit][$tahun] ?? null;
                }
            }
        }

        // mapping data
        foreach ($temp as $idunit => $data) {
            $unit = UnitKerja::where('kode_unit', $idunit)->first()->toArray();
            $json[] = [
                2 => $unit['id'],
                3 => $data[$tahunMin]['nonasing'] ?? 0,
                4 => $data[$tahunMin + 1]['nonasing'] ?? 0,
                5 => $data[$tahunMax]['nonasing'] ?? 0,
                6 => $data[$tahunMin]['asing'] ?? 0,
                7 => $data[$tahunMin + 1]['asing'] ?? 0,
                8 => $data[$tahunMax]['asing'] ?? 0,
                9 => 0,
                10 => 0,
                11 => 0,
            ];
        }

        return $json;
    }

    public function get3a1($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        $ts = $param['tahun_audit'] - 1;

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahunajaran' => $ts,
        ];

        list($err, $raw) = (new SiakadV1)->getDataFromView('hr.v_pegawaitetapmengajar', $filters);

        $data = [];
        foreach ($raw as $index => $item) {
            $data[$item['nip']][$item['tahunajaran']][] = $item;
        }

        // mapping data
        $mapped = [];
        foreach ($data as $nip => $listtahun) {
            $issesuaibidang = 1;
            $mksesuai = [];
            $mktidaksesuai = [];
            foreach ($listtahun as $arr) {
                foreach ($arr as $obj) {
                    if ($obj['issesuaibidang'] == 0) {
                        $issesuaibidang = 0;
                        $mktidaksesuai[] = $obj['namamk'];
                    } else {
                        $mksesuai[] = $obj['namamk'];
                    }
                }
            }
            $mapped[] = [
                'idpegawai' => $obj['idpegawai'],
                'nip' => $nip,
                'nidn' => $obj['nidn'],
                'nidk' => $obj['nidk'],
                'gelardepan' => $obj['gelardepan'],
                'nama' => $obj['nama'],
                'gelarbelakang' => $obj['gelarbelakang'],
                'pendidikan' => $obj['pendidikan'],
                'jabatan_akademik' => $this->normalizeJabatanAkademik($obj['jabatan_akademik']),
                'sertifikasipendidik' => $obj['sertifikasipendidik'],
                'sertifikasiprofesi' => $obj['sertifikasiprofesi'],
                'issesuaibidang' => $issesuaibidang,
                'mksesuai' => implode(', ', $mksesuai),
                'mktidaksesuai' => implode(', ', $mktidaksesuai),
            ];
        }

        // copy to params
        $data = $mapped;

        // get all biodata
        $listBiodata = Biodata::whereIn('ref_key_pegawai', array_column($mapped, 'idpegawai'))->pluck('id', 'ref_key_pegawai')->toArray();

        $json = [];
        foreach ($data as $index => $item) {
            $pendidikan = $item['pendidikan'] ? json_decode($item['pendidikan'], true) : [];
            $json[$index][2] = $listBiodata[$item['idpegawai']] ?? null;
            $json[$index][3] = $item['nidn'] ?? $item['nidk'] ?? null;

            if ($pendidikan) {
                $m_magister = ['S2', 'MTr', 'Sp-1', 'Sp-2'];
                $m_doctor = ['S3', 'DTr'];

                $bidang = '';
                foreach ($pendidikan as $pend) {
                    if (in_array($pend['idjenjang'], $m_magister)) {
                        $json[$index][4] = $pend['prodi'];
                    } elseif (in_array($pend['idjenjang'], $m_doctor)) {
                        $json[$index][5] = $pend['prodi'];
                    }
                    $bidang = $pend['bidang'];
                }
                $json[$index][6] = $bidang;
            } else {
                $json[$index][4] = null;
                $json[$index][5] = null;
                $json[$index][6] = null;
            }
            $json[$index][7] = $item['issesuaibidang'];
            $json[$index][8] = $item['jabatan_akademik'];
            $json[$index][9] = $item['sertifikasipendidik'];
            $json[$index][10] = $item['sertifikasiprofesi'];
            $json[$index][11] = $item['mksesuai'];
            $json[$index][12] = $item['issesuaibidang'];
            $json[$index][13] = $item['mktidaksesuai'];
        }

        return $json;
    }

    public function get3a2($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit']);
        $tahunMin++;

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahunajaran between' => [$tahunMin, $tahunMax],
        ];

        list($err, $raw) = (new SiakadV1)->getDataFromView('hr.v_dospemtugasakhir', $filters);

        $mapped = [];
        foreach ($raw as $index => $item) {
            $mapped[$item['nip']]['nip'] = $item['nip'];
            $mapped[$item['nip']]['gelardepan'] = $item['gelardepan'];
            $mapped[$item['nip']]['nama'] = $item['nama'];
            $mapped[$item['nip']]['gelarbelakang'] = $item['gelarbelakang'];
            $mapped[$item['nip']]['idunit'] = $item['idunit'];
            $mapped[$item['nip']]['namaunit'] = $item['namaunit'];
            $mapped[$item['nip']]['data'][$item['tahunajaran']] = $item['jumlah_mahasiswa'];
        }

        $data = $mapped;

        // get all biodata
        $listBiodata = Pegawai::where('nip', array_column($data, 'nip'))->pluck('id_biodata', 'nip')->toArray();

        $json = [];
        foreach ($data as $index => $item) {
            $json[$index][2] = $listBiodata[$item['nip']] ?? null;
            $json[$index][3] = ($item['data'][$tahunMin] ?? null);
            $json[$index][4] = ($item['data'][($tahunMin + 1)] ?? null);
            $json[$index][5] = ($item['data'][$tahunMax] ?? null);
        }

        return $json;
    }

    public function get3a3($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        $ts = $param['tahun_audit'] - 1;

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahun' => $ts,
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('hr.v_skspengajaran', $filters);

        // group by idpegawai
        $mapped = [];
        foreach ($data as $d) {
            $mapped[$d['idpegawai']][] = $d;
        }

        // mapping
        $data = [];
        foreach ($mapped as $idpegawai => $arr) {
            $first = null;
            $t_psakreditasi = 0;
            $t_pslain = 0;
            foreach ($arr as $d) {
                if (!$first) {
                    $first = $d;
                }
                $t_psakreditasi += (double) $d['ps_diakreditasi'];
                $t_pslain += (double) $d['ps_lain'];
            }

            $first['ps_diakreditasi'] = "$t_psakreditasi";
            $first['ps_lain'] = "$t_pslain";
            $data[] = $first;
        }

        // get all biodata
        $listBiodata = Biodata::whereIn('ref_key_pegawai', array_column($data, 'idpegawai'))->pluck('id', 'ref_key_pegawai')->toArray();

        $json = [];
        foreach ($data as $index => $item) {
            $json[$index][2] = $listBiodata[$item['idpegawai']] ?? null;
            $json[$index][3] = $item['istetap'];
            $json[$index][4] = $item['ps_diakreditasi'];
            $json[$index][5] = $item['ps_lain'];
        }

        return $json;
    }

    public function get3a4($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        $ts = $param['tahun_audit'] - 1;

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahunajaran' => $ts,
        ];

        list($err, $raw) = (new SiakadV1)->getDataFromView('hr.v_pegawaitidaktetapmengajar', $filters);

        $data = [];
        foreach ($raw as $index => $item) {
            $data[$item['nip']][$item['tahunajaran']][] = $item;
        }

        // mapping data
        $mapped = [];
        foreach ($data as $nip => $listtahun) {
            $issesuaibidang = 1;
            $mksesuai = [];
            $mktidaksesuai = [];
            foreach ($listtahun as $arr) {
                foreach ($arr as $obj) {
                    if ($obj['issesuaibidang'] == 0) {
                        $issesuaibidang = 0;
                        $mktidaksesuai[] = $obj['namamk'];
                    } else {
                        $mksesuai[] = $obj['namamk'];
                    }
                }
            }
            $mapped[] = [
                'idpegawai' => $obj['idpegawai'],
                'nip' => $nip,
                'nidn' => $obj['nidn'],
                'nidk' => $obj['nidk'],
                'gelardepan' => $obj['gelardepan'],
                'nama' => $obj['nama'],
                'gelarbelakang' => $obj['gelarbelakang'],
                'pendidikan' => $obj['pendidikan'],
                'jabatan_akademik' => $this->normalizeJabatanAkademik($obj['jabatan_akademik']),
                'sertifikasipendidik' => $obj['sertifikasipendidik'],
                'sertifikasiprofesi' => $obj['sertifikasiprofesi'],
                'issesuaibidang' => $issesuaibidang,
                'mksesuai' => implode(', ', $mksesuai),
                'mktidaksesuai' => implode(', ', $mktidaksesuai),
            ];
        }

        $data = $mapped;

        // get all biodata
        $listBiodata = Biodata::whereIn('ref_key_pegawai', array_column($mapped, 'idpegawai'))->pluck('id', 'ref_key_pegawai')->toArray();

        $json = [];
        foreach ($data as $index => $item) {
            $pendidikan = $item['pendidikan'] ? json_decode($item['pendidikan'], true) : [];
            $json[$index][2] = $listBiodata[$item['idpegawai']] ?? null;
            $json[$index][3] = $item['nidn'] ?? $item['nidk'] ?? null;

            if ($pendidikan) {
                $bidang = '';
                $prodi = '';
                foreach ($pendidikan as $pend) {
                    $prodi = $pend['prodi'];
                    $bidang = $pend['bidang'];
                }
                $json[$index][4] = $prodi;
                $json[$index][5] = $bidang;
            } else {
                $json[$index][4] = null;
                $json[$index][5] = null;
            }
            $json[$index][6] = $item['jabatan_akademik'];
            $json[$index][7] = $item['sertifikasipendidik'];
            $json[$index][8] = $item['sertifikasiprofesi'];
            $json[$index][9] = $item['mksesuai'];
            $json[$index][10] = $item['issesuaibidang'];
        }

        return $json;
    }

    public function get3b2($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit']);
        $tahunMin++;

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahunkegiatan between' => [$tahunMin, $tahunMax],
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('hr.v_jumlahpenelitiandtps_bydana', $filters);

        $json = [];
        if (!empty($data)) {
            foreach ($data as $item) {
                $mapped['total_judul_pt'][$item['tahunkegiatan']] = 0;
                $mapped['total_judul_dnegeri'][$item['tahunkegiatan']] = 0;
                $mapped['total_judul_lnegeri'][$item['tahunkegiatan']] = 0;
            }

            foreach ($data as $item) {
                $mapped['total_judul_pt'][$item['tahunkegiatan']] += $item['total_judul_pt'];
                $mapped['total_judul_dnegeri'][$item['tahunkegiatan']] += $item['total_judul_dnegeri'];
                $mapped['total_judul_lnegeri'][$item['tahunkegiatan']] += $item['total_judul_lnegeri'];
            }

            $keys = ['total_judul_pt', 'total_judul_dnegeri', 'total_judul_lnegeri'];
            foreach ($keys as $index => $key) {
                $i = $index + 1;
                $json[$i][0][3] = $mapped[$key][$tahunMin] ?? null;
                $json[$i][0][4] = $mapped[$key][$tahunMin + 1] ?? null;
                $json[$i][0][5] = $mapped[$key][$tahunMax] ?? null;
            }
        }

        return $json;
    }

    public function get3b3($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit']);
        $tahunMin++;

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahunkegiatan between' => [$tahunMin, $tahunMax],
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('hr.v_jumlahpengabdiandtps_bydana', $filters);

        $json = [];
        if (!empty($data)) {
            foreach ($data as $item) {
                $mapped['total_judul_pt'][$item['tahunkegiatan']] = 0;
                $mapped['total_judul_dnegeri'][$item['tahunkegiatan']] = 0;
                $mapped['total_judul_lnegeri'][$item['tahunkegiatan']] = 0;
            }

            foreach ($data as $item) {
                $mapped['total_judul_pt'][$item['tahunkegiatan']] += $item['total_judul_pt'];
                $mapped['total_judul_dnegeri'][$item['tahunkegiatan']] += $item['total_judul_dnegeri'];
                $mapped['total_judul_lnegeri'][$item['tahunkegiatan']] += $item['total_judul_lnegeri'];
            }

            $keys = ['total_judul_pt', 'total_judul_dnegeri', 'total_judul_lnegeri'];
            foreach ($keys as $index => $key) {
                $i = $index + 1;
                $json[$i][0][3] = $mapped[$key][$tahunMin] ?? null;
                $json[$i][0][4] = $mapped[$key][$tahunMin + 1] ?? null;
                $json[$i][0][5] = $mapped[$key][$tahunMax] ?? null;
            }
        }

        return $json;
    }

    public function get3b4a($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit']);
        $tahunMin++;

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahun between' => [$tahunMin, $tahunMax]
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('hr.v_publikasidtps_byjenis', $filters);

        //mapping idpublikasi
        $mapped = [
            21 => 1,
            22 => 2,
            23 => 3,
            24 => 4,
        ];

        $json = [];
        $temp = [];
        $isHasData = false;
        foreach ($data as $item) {
            if (!isset($mapped[$item['idjenispublikasi']])) {
                continue;
            }
            $isHasData = true;
            $temp[$mapped[$item['idjenispublikasi']]][$item['tahun']] = $item['jumlah'];
        }

        foreach ($mapped as $value) {
            $json[$value][0][3] = 0;
            $json[$value][0][4] = 0;
            $json[$value][0][5] = 0;
        }

        foreach ($temp as $key => $items) {
            $json[$key][0][3] += $items[$tahunMin] ?? 0;
            $json[$key][0][4] += $items[$tahunMin + 1] ?? 0;
            $json[$key][0][5] += $items[$tahunMax] ?? 0;
        }

        // null
        if ($isHasData) {
            for ($i = 1; $i <= 10; $i++) {
                if (empty($json[$i][0][3])) {
                    $json[$i][0][3] = null;
                }

                if (empty($json[$i][0][4])) {
                    $json[$i][0][4] = null;
                }

                if (empty($json[$i][0][5])) {
                    $json[$i][0][5] = null;
                }
            }

            ksort($json);
        } else {
            $json = [];
        }

        return $json;
    }

    public function get3b4b($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit']);
        $tahunMin++;

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahun between' => [$tahunMin, $tahunMax]
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('hr.v_publikasidtps_byjenis', $filters);

        //mapping idpublikasi
        $mapped = [
            9998 => 8,
            9997 => 9,
            9996 => 10,
        ];

        $json = [];
        $temp = [];
        $isHasData = false;
        foreach ($data as $item) {
            if (!isset($mapped[$item['idjenispublikasi']])) {
                continue;
            }
            $isHasData = true;
            $temp[$mapped[$item['idjenispublikasi']]][$item['tahun']] = $item['jumlah'];
        }

        foreach ($mapped as $value) {
            $json[$value][0][3] = 0;
            $json[$value][0][4] = 0;
            $json[$value][0][5] = 0;
        }

        foreach ($temp as $key => $items) {
            $json[$key][0][3] += $items[$tahunMin] ?? 0;
            $json[$key][0][4] += $items[$tahunMin + 1] ?? 0;
            $json[$key][0][5] += $items[$tahunMax] ?? 0;
        }

        // null
        if ($isHasData) {
            for ($i = 1; $i <= 10; $i++) {
                if (empty($json[$i][0][3])) {
                    $json[$i][0][3] = null;
                }

                if (empty($json[$i][0][4])) {
                    $json[$i][0][4] = null;
                }

                if (empty($json[$i][0][5])) {
                    $json[$i][0][5] = null;
                }
            }

            ksort($json);
        } else {
            $json = [];
        }

        return $json;
    }

    public function get3b7_1($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit'], 3);

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahun between' => [$tahunMin, $tahunMax],
            'idjenispaten' => ['41', '42'],
            'ismhs' => 0
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('hr.v_paten', $filters);

        $json = [];
        foreach ($data as $index => $item) {
            $json[$index] = [
                2 => $item['judul'],
                3 => $item['tahun'],
                4 => $item['keterangan'],
            ];
        }

        return $json;
    }

    public function get3b7_2($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit'], 3);

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahun between' => [$tahunMin, $tahunMax],
            'idjenispaten' => ['43', '44'],
            'ismhs' => 0
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('hr.v_paten', $filters);

        $json = [];
        foreach ($data as $index => $item) {
            $json[$index] = [
                2 => $item['judul'],
                3 => $item['tahun'],
                4 => $item['keterangan'],
            ];
        }

        return $json;
    }

    public function get3b7_4($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit'], 3);

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahun between' => [$tahunMin, $tahunMax],
            'ismhs' => 0
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('hr.v_bukuisbn', $filters);

        $json = [];
        foreach ($data as $index => $item) {
            $json[$index] = [
                2 => $item['judul'],
                3 => $item['tahun'],
                4 => $item['keterangan'],
            ];
        }

        return $json;
    }

    public function get4($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit'], 3);

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahunkegiatan between' => [$tahunMin, $tahunMax],
            'jenis_dana' => 'perguruantinggi'
        ];

        list($err, $sumberdanapenelitian) = (new SiakadV1)->getDataFromView('hr.v_sumberdanapenelitian', $filters);
        list($err, $sumberdanapkm) = (new SiakadV1)->getDataFromView('hr.v_sumberdanapkm', $filters);

        $mappedpenelitian = [];
        foreach ($sumberdanapenelitian as $item) {
            $mappedpenelitian[$item['tahunkegiatan']] = $item['total_dana'];
        }

        $mappedpkm = [];
        foreach ($sumberdanapkm as $item) {
            $mappedpkm[$item['tahunkegiatan']] = $item['total_dana'];
        }

        $json = [];

        for ($i = 2; $i <= 6; $i++) {
            $ids = [3, 4, 5, 7, 8, 9];
            foreach ($ids as $id) {
                $json[$i][0][$id] = null;
            }
        }

        foreach ($sumberdanapenelitian as $key => $item) {
            $json[7][0][3] = $mappedpenelitian[$tahunMin] ?? null;
            $json[7][0][4] = $mappedpenelitian[$tahunMin + 1] ?? null;
            $json[7][0][5] = $mappedpenelitian[$tahunMax] ?? null;
            $json[7][0][6] = null;
            $json[7][0][7] = null;
            $json[7][0][8] = null;
        }

        $mappedpkm = [];
        foreach ($sumberdanapkm as $item) {
            $mappedpkm[$item['tahunkegiatan']] = $item['total_dana'];
        }

        foreach ($sumberdanapkm as $item) {
            $json[8][0][3] = $mappedpkm[$tahunMin] ?? null;
            $json[8][0][4] = $mappedpkm[$tahunMin + 1] ?? null;
            $json[8][0][5] = $mappedpkm[$tahunMax] ?? null;
            $json[8][0][6] = null;
            $json[8][0][7] = null;
            $json[8][0][8] = null;
        }

        for ($i = 9; $i <= 11; $i++) {
            $ids = [3, 4, 5, 7, 8, 9];
            foreach ($ids as $id) {
                $json[$i][0][$id] = null;
            }
        }


        return $json;
    }

    public function get5a($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'idkurikulum' => $param['id_kurikulum'],
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('akreditasicloud.v_matkulkurikulum', $filters, '*', 'semmk');

        $json = [];
        foreach ($data as $index => $mk) {
            $konversi = round(((($mk['sksmk'] * 50) + (($mk['sksseminar'] ?? 0) * 110) + ($mk['skspraktikum'] * 170)) * 14) / 60, 2);
            $unit = UnitKerja::where('kode_unit', $mk['idunit'])->first()->toArray();
            $json[$index][2] = $mk['semmk'];
            $json[$index][3] = $mk['idmk'];
            $json[$index][4] = $mk['namamk'];
            $json[$index][5] = $mk['ismkkompetensi'] . '';
            $json[$index][6] = $mk['sksmk'];
            $json[$index][7] = null;
            $json[$index][8] = $mk['skspraktikum'];
            $json[$index][9] = $konversi;
            $json[$index][10] = '0';
            $json[$index][11] = '0';
            $json[$index][12] = '0';
            $json[$index][13] = '0';
            $json[$index][14] = null;
            $json[$index][15] = $unit['nama_unit'];
        }

        return $json;
    }

    public function get6a($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit'], 3);

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahun between' => [$tahunMin, $tahunMax],
            'ismhs' => 1
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('hr.v_penelitian', $filters);

        // get all biodata
        $listBiodata = Biodata::whereIn('ref_key_pegawai', array_column($data, 'idpegawai'))->pluck('id', 'ref_key_pegawai')->toArray();

        $json = [];
        foreach ($data as $index => $item) {
            $item['namamhs'] = json_decode($item['namamhs'], true);
            $json[$index] = [
                2 => $listBiodata[$item['idpegawai']] ?? null,
                3 => null,
                4 => implode(', ', $item['namamhs']),
                5 => $item['judulpenelitian'],
                6 => $item['tahun'],
            ];
        }

        return $json;
    }


    public function get7($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit'], 3);

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahunkegiatan between' => [$tahunMin, $tahunMax],
            'ismhs' => 1
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('hr.v_pkm', $filters);

        // get all biodata
        $listBiodata = Biodata::whereIn('ref_key_pegawai', array_column($data, 'idpegawai'))->pluck('id', 'ref_key_pegawai')->toArray();

        $json = [];
        foreach ($data as $index => $item) {
            $item['namamhs'] = json_decode($item['namamhs'], true);
            $json[$index] = [
                2 => $listBiodata[$item['idpegawai']] ?? null,
                3 => null,
                4 => implode(', ', $item['namamhs']),
                5 => $item['namakegiatan'],
                6 => $item['tahunkegiatan'],
            ];
        }

        return $json;
    }

    public function get8a($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit'], 3);

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahunajaran between' => [$tahunMin, $tahunMax],
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('akreditasicloud.v_lulusan', $filters);

        usort($data, function ($a, $b) {
            return $a['tahunajaran'] <=> $b['tahunajaran'];
        });

        $json = [];
        foreach ($data as $index => $obj) {
            $key = $index + 1;
            $json[$key][0][2] = $obj['lulusan'];
            $json[$key][0][3] = $obj['minipk'];
            $json[$key][0][4] = $obj['avgipk'];
            $json[$key][0][5] = $obj['maxipk'];
        }

        return $json;
    }

    public function get8c1($param = [])
    {
        $maximal = empty($param['maximal']) ? 3 : $param['maximal'];
        $minimal = empty($param['minimal']) ? 5 : $param['minimal'];

        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        $tahunMax = $param['tahun_audit'] - $maximal;
        $tahunMin = $param['tahun_audit'] - $minimal;
        $periodemaximal = $tahunMin + $minimal - 1;

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'angkatan between' => [$tahunMin, $tahunMax],
            'tahunajaran between' => [$tahunMin, $periodemaximal],
        ];

        list($err, $arrMhsLulus) = (new SiakadV1)->getDataFromView('akreditasicloud.v_lulusanangkatan', $filters);
        // pendaftar
        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahunajaran between' => [$tahunMin, $tahunMax]
        ];
        list($err, $arrPendaftar) = (new SiakadV1)->getDataFromView('akreditasicloud.v_pendaftar', $filters);

        // mapping data sesuai angkatan
        $arrLulusan = [];
        foreach ($arrMhsLulus as $row) {
            $arrLulusan['prodi'][$row['idunit']][$row['tahunajaran']] = $row;
            if (!empty($row['angkatan']))
                $arrLulusan['angkatan'][$row['angkatan']][$row['tahunajaran']] = $row;
        }

        $arrPendaftarlulus = Cstr::toMap('tahunajaran', 'lulus', $arrPendaftar);

        $no = 1;
        $arrData = [];
        for ($ts = $tahunMin; $ts <= $tahunMax; $ts++) {
            $i = 2;
            $jumlahlulus = $masastudi = [];
            $lulusan = $arrLulusan['angkatan'][$ts] ?? null;

            $arrData[$no][0][$i++] = $arrPendaftarlulus[$ts] ?? null;
            for ($periode = $tahunMin; $periode <= $periodemaximal; $periode++) {
                $arrData[$no][0][$i++] = $lulusan[$periode]['lulusan'] ?? null;

                $jumlahlulus[] = $lulusan[$periode]['lulusan'] ?? null;
                $masastudi[] = $lulusan[$periode]['jumlahmasastudi'] ?? null;
            }

            $arrData[$no][0][$i++] = array_sum($jumlahlulus) != 0 ? array_sum($jumlahlulus)  : null;
            $arrData[$no][0][$i++] = CStr::devidedRound(array_sum($masastudi), array_sum($jumlahlulus));

            $no++;
        }

        return $arrData;
    }

    public function get8d1b($param = [])
    {
        $unit = UnitKerja::find($param['id_unit']);
        $jenjang = JenjangPendidikan::find($unit['id_jenjang_pendidikan']);

        $service = new KarirLinkAPI();

        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit']);

        $arrPeriode = [$tahunMin - 1, $tahunMin, $tahunMin + 1];

        $data = [];
        foreach ($arrPeriode as $period) {
            $res = $service->getData(KarirLinkAPI::WAKTU_TUNGGU, [
                'tahun_audit' => $period,
                'kode_jenjang' => $jenjang['kode_jenjang'],
                'kode_unit' => $unit['kode_unit']
            ]);

            if (empty($res['data'])) {
                continue;
            }

            $data[] = $res['data']['data'];
        }

        $json = [];
        foreach ($data as $i => $arr) {
            $index = $i + 1;
            $json[$index][0][2] = explode('/', $arr['comparison'])[1];
            $json[$index][0][3] = $arr['total'];
            $json[$index][0][4] = ($arr['data'][2]['total'] ?? 0);
            $json[$index][0][5] = ($arr['data'][4]['total'] ?? 0) + ($arr['data'][6]['total'] ?? 0);
            $json[$index][0][6] = ($arr['data'][7]['total'] ?? 0);
        }

        ksort($json);

        return $json;
    }

    public function get8f1a($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit']);
        $tahunMin++;

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahun between' => [$tahunMin, $tahunMax]
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('hr.v_publikasimhs_byjenis', $filters);

        //mapping idpublikasi
        $mapped = [
            21 => 1,
            22 => 2,
            23 => 3,
            24 => 4,
        ];

        $json = [];
        $temp = [];
        $isHasData = false;
        foreach ($data as $item) {
            if (!isset($mapped[$item['idjenispublikasi']])) {
                continue;
            }
            $isHasData = true;
            $temp[$mapped[$item['idjenispublikasi']]][$item['tahun']] = $item['jumlah'];
        }

        foreach ($mapped as $value) {
            $json[$value][0][3] = 0;
            $json[$value][0][4] = 0;
            $json[$value][0][5] = 0;
        }

        foreach ($temp as $key => $items) {
            $json[$key][0][3] += $items[$tahunMin] ?? 0;
            $json[$key][0][4] += $items[$tahunMin + 1] ?? 0;
            $json[$key][0][5] += $items[$tahunMax] ?? 0;
        }

        // null
        if ($isHasData) {
            for ($i = 1; $i <= 10; $i++) {
                if (empty($json[$i][0][3])) {
                    $json[$i][0][3] = null;
                }

                if (empty($json[$i][0][4])) {
                    $json[$i][0][4] = null;
                }

                if (empty($json[$i][0][5])) {
                    $json[$i][0][5] = null;
                }
            }

            ksort($json);
        } else {
            $json = [];
        }

        return $json;
    }

    public function get8f1b($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit']);
        $tahunMin++;

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahun between' => [$tahunMin, $tahunMax]
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('hr.v_publikasimhs_byjenis', $filters);

        //mapping idpublikasi
        $mapped = [
            9998 => 8,
            9997 => 9,
            9996 => 10,
        ];

        $json = [];
        $temp = [];
        $isHasData = false;
        foreach ($data as $item) {
            if (!isset($mapped[$item['idjenispublikasi']])) {
                continue;
            }
            $isHasData = true;
            $temp[$mapped[$item['idjenispublikasi']]][$item['tahun']] = $item['jumlah'];
        }

        foreach ($mapped as $value) {
            $json[$value][0][3] = 0;
            $json[$value][0][4] = 0;
            $json[$value][0][5] = 0;
        }

        foreach ($temp as $key => $items) {
            $json[$key][0][3] += $items[$tahunMin] ?? 0;
            $json[$key][0][4] += $items[$tahunMin + 1] ?? 0;
            $json[$key][0][5] += $items[$tahunMax] ?? 0;
        }

        // null
        if ($isHasData) {
            for ($i = 1; $i <= 10; $i++) {
                if (empty($json[$i][0][3])) {
                    $json[$i][0][3] = null;
                }

                if (empty($json[$i][0][4])) {
                    $json[$i][0][4] = null;
                }

                if (empty($json[$i][0][5])) {
                    $json[$i][0][5] = null;
                }
            }

            ksort($json);
        } else {
            $json = [];
        }

        return $json;
    }

    public function get8f4_1($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit'], 3);

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahun between' => [$tahunMin, $tahunMax],
            'idjenispaten' => ['41', '42'],
            'ismhs' => 1
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('hr.v_paten', $filters);

        $json = [];
        foreach ($data as $index => $item) {
            $json[$index] = [
                2 => $item['judul'],
                3 => $item['tahun'],
                4 => $item['keterangan'],
            ];
        }

        return $json;
    }

    public function get8f4_2($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit'], 3);

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahun between' => [$tahunMin, $tahunMax],
            'idjenispaten' => ['43', '44'],
            'ismhs' => 1
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('hr.v_paten', $filters);

        $json = [];
        foreach ($data as $index => $item) {
            $json[$index] = [
                2 => $item['judul'],
                3 => $item['tahun'],
                4 => $item['keterangan'],
            ];
        }

        return $json;
    }

    public function get8f4_4($param = [])
    {
        // study program
        $studyProgram = UnitKerja::find($param['id_unit']);
        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit'], 3);

        $filters = [
            'idunit' => $studyProgram['kode_unit'],
            'tahun between' => [$tahunMin, $tahunMax],
            'ismhs' => 1
        ];

        list($err, $data) = (new SiakadV1)->getDataFromView('hr.v_bukuisbn', $filters);

        $json = [];
        foreach ($data as $index => $item) {
            $json[$index] = [
                2 => $item['judul'],
                3 => $item['tahun'],
                4 => $item['keterangan'],
            ];
        }

        return $json;
    }

    public function get8e2Ref($param = [])
    {
        $unit = UnitKerja::find($param['id_unit']);
        $jenjang = JenjangPendidikan::find($unit['id_jenjang_pendidikan']);

        $service = new KarirLinkAPI();

        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit']);

        $arrPeriode = [$tahunMin - 1, $tahunMin, $tahunMin + 1];

        $data = [];
        foreach ($arrPeriode as $period) {
            $res = $service->getData(KarirLinkAPI::KESESUAIAN_BIDANG, [
                'tahun_audit' => $period,
                'kode_jenjang' => $jenjang['kode_jenjang'],
                'kode_unit' => $unit['kode_unit']
            ]);

            if (empty($res['data'])) {
                continue;
            }

            $data[] = $res['data']['data'];
        }

        $json = [];
        foreach ($data as $i => $arr) {
            $index = $i + 1;
            $json[$index][0][2] = explode('/', $arr['comparison'])[1];
            $json[$index][0][3] = $arr['total'];
        }

        ksort($json);

        return $json;
    }

    public function get8d2($param = [])
    {
        $unit = UnitKerja::find($param['id_unit']);
        $jenjang = JenjangPendidikan::find($unit['id_jenjang_pendidikan']);

        $service = new KarirLinkAPI();

        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit']);

        $arrPeriode = [$tahunMin - 1, $tahunMin, $tahunMin + 1];

        $data = [];
        foreach ($arrPeriode as $period) {
            $res = $service->getData(KarirLinkAPI::KESESUAIAN_BIDANG, [
                'tahun_audit' => $period,
                'kode_jenjang' => $jenjang['kode_jenjang'],
                'kode_unit' => $unit['kode_unit']
            ]);

            if (empty($res['data'])) {
                continue;
            }

            $data[] = $res['data']['data'];
        }

        $json = [];
        foreach ($data as $i => $arr) {
            $index = $i + 1;
            $json[$index][0][2] = explode('/', $arr['comparison'])[1];
            $json[$index][0][3] = $arr['total'];
            $json[$index][0][4] = $arr['section']['high']['total'];
            $json[$index][0][5] = $arr['section']['medium']['total'];
            $json[$index][0][6] = $arr['section']['low']['total'];
        }

        ksort($json);

        return $json;
    }

    public function get8e1($param = [])
    {
        $unit = UnitKerja::find($param['id_unit']);
        $jenjang = JenjangPendidikan::find($unit['id_jenjang_pendidikan']);

        $service = new KarirLinkAPI();

        list($tahunMin, $tahunMax) = Cstr::akademikYearMinMax($param['tahun_audit']);

        $arrPeriode = [$tahunMin - 1, $tahunMin, $tahunMin + 1];

        $data = [];
        foreach ($arrPeriode as $period) {
            $res = $service->getData(KarirLinkAPI::TINGKAT_TEMPAT_KERJA, [
                'tahun_audit' => $period,
                'kode_jenjang' => $jenjang['kode_jenjang'],
                'kode_unit' => $unit['kode_unit']
            ]);

            if (empty($res['data'])) {
                continue;
            }

            $data[] = $res['data'];
        }

        $json = [];
        foreach ($data as $i => $arr) {
            $index = $i + 1;
            $json[$index][0][2] = $arr['total_graduate'];
            $json[$index][0][3] = $arr['total'];
            $json[$index][0][4] = $arr['local'];
            $json[$index][0][5] = $arr['nasional'];
            $json[$index][0][6] = $arr['international'];
        }

        ksort($json);

        return $json;
    }
}
