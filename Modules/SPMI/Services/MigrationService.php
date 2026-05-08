<?php

namespace Modules\SPMI\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Cstr;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\LembagaAkreditasi;
use Modules\SPMI\Helpers\AccreditationSync;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\AkreditasiPeringkat;
use Modules\SPMI\Models\AkreditasiStandar;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;
use Modules\SPMI\Models\AkreditasiSyarat;
use Modules\SPMI\Models\DataPengisianLK;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\JenisStandar;
use Modules\SPMI\Models\MappingLK;
use Modules\SPMI\Models\PengisianIndikator;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianKlaster;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksPredikat;
use Modules\SPMI\Models\PenilaianMatriksReferensi;
use Modules\SPMI\Models\PenilaianPanduan;

class MigrationService
{
    /**
     * Untuk kebutuhan migrate matriks Penilaian
     */
    public static function migratePenilaianMatrix($kodePenilaianPanduan, $dataPenilaianMatriks = [])
    {
        $msg = 'Success to sync data from SPMI API. And Sync data saved';
        $err = false;

        $panduanPenilaian = PenilaianPanduan::where('kode_penilaian_panduan', $kodePenilaianPanduan)->first();
        $idPanduanPenilaian = $panduanPenilaian->id;

        $mapField = [
            'nobutirspmi' => 'nomor_penilaian',
            'kodespmi' => 'id_penilaian_panduan',
            'parentbutirspmi' => 'id_parent',
            'namabutirspmi' => 'pertanyaan_penilaian',
            'bobot' => 'bobot_penilaian',
            'keterangan' => 'deskripsi',
            'idkategori' => 'kategori_penilaian',
            'idklaster' => 'id_penilaian_klaster',
            'kodestandar' => 'id_akreditasi_standar',
            'idsyaratakreditasi' => 'syarat_terakreditasi',
            'idstandarpt' => 'standar_perguruan_tinggi',
            'isaktif' => 'apakah_aktif',
            'idjenispenilaian' => 'jenis_penilaian',
            'idsumberreferensi' => 'referensi_penilaian',
            'istampilkandinilaiakhir' => 'apakah_nilai_ditampilkan',
            'butirlk' => 'indikator_laporan_kinerja',
            'butirled' => 'indikator_evaluasi_diri',
            'level' => 'info_level',
            'infoleft' => 'info_left',
            'inforight' => 'info_right',
            'pilihanskor' => 'scores',
        ];

        $savedMatrices = [];

        if (empty($dataPenilaianMatriks)) {
            $err = true;
            $msg = 'Data penilaian matriks tidak ditemukan';
            return [$err, $msg];
        }

        $skorMatriksPredikatPenilaian = SkorMatriksPredikatPenilaian::where('id_penilaian_panduan', $idPanduanPenilaian)->pluck('id', 'nilai')->toArray();

        // Referensi Kluster Penilaian
        $mappedPenilaianKlaster = array_map(fn($item) => $item['idklaster'], $dataPenilaianMatriks);
        $refPenilaianKlaster = PenilaianKlaster::whereIn('kode_klaster', $mappedPenilaianKlaster)->pluck('id', 'kode_klaster')->toArray();

        // Referensi butir LK
        $mappedIndikatorLaporanKinerja = array_map(fn($item) => $item['butirlk'], $dataPenilaianMatriks);
        $mappedIndikatorLaporanKinerja = array_filter($mappedIndikatorLaporanKinerja, fn($item) => !empty($item));
        $mappedIndikatorLaporanKinerja = array_unique(Arr::flatten($mappedIndikatorLaporanKinerja));
        $refIndikatorLaporanKinerja = IndikatorLaporanKinerja::where('id_pengisian_panduan', $panduanPenilaian->id_laporan_kinerja)->whereIn('nomor_indikator', $mappedIndikatorLaporanKinerja)->pluck('id', 'nomor_indikator')->toArray();

        // Referensi butir LED
        $panduanPengisianLED = PengisianPanduan::where('id', $panduanPenilaian->id_laporan_kinerja)->value('id_pengisian_panduan') ?? null;
        $mappedIndikatorEvaluasiDiri = array_map(fn($item) => $item['butirled'], $dataPenilaianMatriks);
        $mappedIndikatorEvaluasiDiri = array_filter($mappedIndikatorEvaluasiDiri, fn($item) => !empty($item));
        $mappedIndikatorEvaluasiDiri = array_unique(Arr::flatten($mappedIndikatorEvaluasiDiri));
        $refIndikatorEvaluasiDiri = IndikatorEvaluasiDiri::where('id_pengisian_panduan', $panduanPengisianLED)
            ->whereIn('nomor_indikator', $mappedIndikatorEvaluasiDiri)->pluck('id', 'nomor_indikator')->toArray();

        // Ambil standar akfreditasi sesuai jenis standar panduan
        $mappedStandarAkreditasi = array_map(
            function ($item) {
                if (substr($item['kodestandar'], -1) == '.') {
                    $item['kodestandar'] = substr($item['kodestandar'], 0, -1);
                }

                return $item['kodestandar'];
            },
            $dataPenilaianMatriks
        );

        $accreditationStandarts = AkreditasiStandar::whereIn('kode_standar', $mappedStandarAkreditasi)->get();

        foreach ($dataPenilaianMatriks as $PenilaianMatriks) {
            $mappedData = [];

            foreach ($mapField as $key => $selfKey) {
                $value = $PenilaianMatriks[$key] ?? null;

                // Sanitasi html tag di pertanyaan penilaian
                if ($key == 'namabutirspmi') {
                    $mappedData[$selfKey] = Cstr::stripHTMLTags($value);
                    continue;
                }

                if ($key == 'kodespmi') {
                    $mappedData[$selfKey] = $idPanduanPenilaian;
                    continue;
                }

                // Sementara di nonaktifkan
                // if ($key == 'keterangan') {
                //     $mappedData[$selfKey] = Cstr::stripHTMLTags($value);
                //     continue;
                // }

                // Mapping parent matrix
                if ($key == 'parentbutirspmi' && !empty($value)) {
                    $parentMatrix = array_filter($savedMatrices, fn($item) => $item['nomor_penilaian'] == $value);
                    $parentMatrix = array_values($parentMatrix);
                    $mappedData[$selfKey] = $parentMatrix[0]['id'] ?? null;
                    continue;
                }

                if ($key == 'idklaster') {
                    $mappedData[$selfKey] = $refPenilaianKlaster[$value] ?? null;
                    continue;
                }

                if ($key == 'idsumberreferensi') {
                    $mappedData[$selfKey] = $value == 'BA' ? 'pr' : ($value == 'GB' ? 'me' : 'se');
                    continue;
                }

                if ($key == 'kodestandar') {
                    if (empty($value)) {
                        continue;
                    }

                    $standartCode = $value;
                    if (substr($value, -1) == '.') {
                        $standartCode = substr($value, 0, -1);
                    }

                    $mappedData[$selfKey] = $accreditationStandarts->where('kode_standar', $standartCode)->first()->id ?? null;
                    continue;
                }

                // Mapping skor matrix
                if ($key == 'pilihanskor' && !empty($value) && is_array($value)) {
                    $mappedData[$selfKey] = array_map(function ($item) {
                        return [
                            'nilai' => $item['skor'],
                            'deskripsi' => $item['uraian'],
                            'apakah_nonaktif' => $item['isdisable'],
                        ];
                    }, $value);
                    continue;
                }

                // Jika ada butir LK maka diambil dari referensi LK
                if ($key == 'butirlk' && !empty($value)) {
                    $mappedData[$selfKey] = array_map(function ($item) use ($refIndikatorLaporanKinerja) {
                        return [
                            'jenis_referensi' => PenilaianMatriks::REFERENCE_PERFORMANCE_REPORT,
                            'id_butir_referensi' => $refIndikatorLaporanKinerja[$item['nobutir']] ?? null,
                        ];
                    }, $value);
                    continue;
                }

                // Jika ada butir LED maka diambil dari referensi LED
                if ($key == 'butirled' && !empty($value)) {
                    $mappedData[$selfKey] = array_map(function ($item) use ($refIndikatorEvaluasiDiri) {
                        return [
                            'jenis_referensi' => PenilaianMatriks::REFERENCE_SELF_EVALUATION,
                            'id_butir_referensi' => $refIndikatorEvaluasiDiri[$item['kodeled']] ?? null,
                        ];
                    }, $value);
                    continue;
                }

                $mappedData[$selfKey] = $value;
            }

            // Jika katagori penilaian adalah element
            if (
                ($mappedData['kategori_penilaian'] == PenilaianMatriks::CATEGORY_ELEMENT) &&
                ($mappedData['jenis_penilaian']) == PenilaianMatriks::TYPE_FINAL_SCORE
            ) {
                $mappedData['bobot_penilaian'] = null;
            }

            $toStoreMatrix = Arr::except($mappedData, ['indikator_laporan_kinerja', 'indikator_evaluasi_diri', 'scores']);

            try {
                $savedMatrix = PenilaianMatriks::create([
                    ...$toStoreMatrix,
                    'butir_indikator_spme' => true,
                    'butir_indikator_iku' => true,
                    'apakah_data_default' => true,
                ])
                    ->toArray();
            } catch (\Exception $e) {
                $err = true;
                $msg = $e->getMessage();
                break;
            }

            // Simpan referensi butir LK dan LED
            $indikatorLaporanKinerja = $mappedData['indikator_laporan_kinerja'] ?? [];
            $indikatorEvaluasiDiri = $mappedData['indikator_evaluasi_diri'] ?? [];

            if (!empty($indikatorLaporanKinerja)) {
                $indikatorLaporanKinerja = array_map(fn($item) => [
                    'id_penilaian_matriks' => $savedMatrix['id'],
                    ...$item,
                    'waktu_dibuat' => Carbon::now(),
                    'waktu_diubah' => Carbon::now(),
                ], $indikatorLaporanKinerja);

                try {
                    PenilaianMatriksReferensi::upsert(
                        $indikatorLaporanKinerja,
                        ['id_penilaian_matriks', 'id_butir_referensi'],
                        ['waktu_diubah']
                    );
                } catch (\Exception $e) {
                    $err = true;
                    $msg = $e->getMessage();
                    break;
                }
            }

            if (!empty($indikatorEvaluasiDiri)) {
                $indikatorEvaluasiDiri = array_map(fn($item) => [
                    'id_penilaian_matriks' => $savedMatrix['id'],
                    ...$item,
                    'waktu_dibuat' => Carbon::now(),
                    'waktu_diubah' => Carbon::now(),
                ], $indikatorEvaluasiDiri);

                try {
                    PenilaianMatriksReferensi::upsert(
                        $indikatorEvaluasiDiri,
                        ['id_penilaian_matriks', 'id_butir_referensi'],
                        ['waktu_diubah']
                    );
                } catch (\Exception $e) {
                    $err = true;
                    $msg = $e->getMessage();
                    break;
                }
            }

            // Simpan data skor matrix
            $scores = $mappedData['scores'] ?? [];
            if (!empty($scores)) {
                $scores = array_map(fn($item) => [
                    'id_penilaian_matriks' => $savedMatrix['id'],
                    ...$item,
                    'waktu_dibuat' => Carbon::now(),
                    'waktu_diubah' => Carbon::now(),
                ], $scores);

                foreach ($scores as $k => $score) {
                    $scores[$k]['id_skor_matriks_predikat_penilaian'] = $skorMatriksPredikatPenilaian[$score['nilai']] ?? null;
                    unset($scores[$k]['nilai']);

                    $penilaianMatriksPredikat = PenilaianMatriksPredikat::where([
                        'id_penilaian_matriks' => $savedMatrix['id'],
                        'id_skor_matriks_predikat_penilaian' => $scores[$k]['id_skor_matriks_predikat_penilaian'],
                    ])->first();
                    if ($penilaianMatriksPredikat) {
                        $penilaianMatriksPredikat->update([
                            'deskripsi' => $scores[$k]['deskripsi'],
                            'apakah_nonaktif' => $scores[$k]['apakah_nonaktif'],
                            'waktu_diubah' => $scores[$k]['waktu_diubah'],
                        ]);
                    } else {
                        PenilaianMatriksPredikat::create($scores[$k]);
                    }
                }
            }

            $savedMatrices[] = $savedMatrix;
        }

        $totalIndikatorMatriks = count(
            array_filter(
                $savedMatrices,
                fn($item) => $item['kategori_penilaian'] == PenilaianMatriks::CATEGORY_INDICATOR && $item['apakah_aktif'] == true
            )
        );

        PenilaianPanduan::find($idPanduanPenilaian)->update([
            'total_indikator_matriks' => $totalIndikatorMatriks,
        ]);

        return [$err, $msg];
    }

    /**
     * Untuk kebutuhan mapping LK
     */
    public static function migrationMappingLK($kodePengisian, $kodeJenjang, $excludeMatrix = [])
    {
        $dataPengisian = PengisianPanduan::where('kode_pengisian_panduan', $kodePengisian)->first();

        if (empty($dataPengisian)) {
            return;
        }

        $dataLK = IndikatorLaporanKinerja::where('id_pengisian_panduan', $dataPengisian->id)
            ->where('apakah_data_default', true)->get();
        $jenjangPendidikan = JenjangPendidikan::where('kode_jenjang', $kodeJenjang)->first();

        if (empty($jenjangPendidikan)) {
            return;
        }

        DB::beginTransaction();

        foreach ($dataLK as $lk) {
            if (in_array($lk->nomor_indikator, $excludeMatrix)) {
                MappingLK::where('id_indikator_laporan_kinerja', $lk->id)
                    ->where('id_jenjang_pendidikan', $jenjangPendidikan->id)
                    ->delete();

                continue;
            }

            MappingLK::firstOrCreate([
                'id_indikator_laporan_kinerja' => $lk->id,
                'id_jenjang_pendidikan' => $jenjangPendidikan->id
            ]);
        }

        DB::commit();
    }

    public static function setActivePanduanPenilaian($kodePanduan, $apakahTargetAktif = false, $apakahPenilaianAktif)
    {
        $msg = 'Success to set active panduan penilaian';
        $err = false;

        $panduanPenilaian = PenilaianPanduan::where('kode_penilaian_panduan', $kodePanduan);

        if (!$panduanPenilaian->exists()) {
            $err = true;
            $msg = 'Data panduan tidak ada';
            return [$err, $msg];
        }


        $panduanPenilaian->update([
            'apakah_target_aktif' => $apakahTargetAktif,
            'apakah_aktif' => $apakahPenilaianAktif,
        ]);
    }

    public static function tarikPanduanAkreditasidanLED($kodeLembaga, $kodePengisianPanduan = null)
    {
        $service = new AccreditationSync();

        $data_panduan = $service->getGuide($kodeLembaga);

        // transaction
        DB::beginTransaction();

        // create panduan
        $is_error_panduan = false;
        foreach ($data_panduan['data'] as $rp) {
            try {
                $payload = [
                    'kode_pengisian_panduan' => $rp['kodebanpt'],
                    'nama_pengisian_panduan' => $rp['namapanduan'],
                    'nama_singkat' => $rp['namasingkat'],
                    'id_lembaga_akreditasi' => LembagaAkreditasi::where('kode_lembaga', $kodeLembaga)->value('id'),
                    'kode_level_akses' => $rp['idlevelakses'],
                    'id_akreditasi_buku' => AkreditasiBuku::where('kode_buku', $rp['idbukuakreditasi'])->value('id'),
                    'id_jenis_standar' => JenisStandar::where('kode_jenis_standar', $rp['idstandarakreditasi'])->value('id'),
                    'tipe_edisi' => $rp['idedisi'] == 'ED' ? AkreditasiBuku::SELF_EVALUATION : AkreditasiBuku::PERFORMANCE_REPORT,
                    'apakah_aktif' => false,
                    'tanggal_edisi' => $rp['tgledisi'],
                    'tanggal_efektif' => $rp['tglberlaku'],
                    'tanggal_kadaluwarsa' => $rp['tglberakhir'],
                    'apakah_sapto' => $rp['issapto'] == '1' ? true : false,
                ];

                if (!empty($rp['idled'])) {
                    $payload['id_pengisian_panduan'] = PengisianPanduan::where('kode_pengisian_panduan', $rp['idled'])->value('id');
                }

                $pengisianPanduan = PengisianPanduan::where('kode_pengisian_panduan', $rp['kodebanpt'])->first();
                if ($pengisianPanduan) {
                    unset($payload['apakah_aktif']);
                }

                PengisianPanduan::updateOrCreate(
                    ['kode_pengisian_panduan' => $rp['kodebanpt']],
                    $payload
                );
            } catch (\Exception $e) {
                DB::rollBack();
                $is_error_panduan = true;
                echo $e->getMessage();
            }
        }

        // sync butir LED
        if (!empty($kodePengisianPanduan) && !$is_error_panduan) {
            list($is_error_panduan, $msg) = AccreditationSyncManagementService::syncButirLEDPanduanNew($kodePengisianPanduan);
        }

        // commit transaction
        if ($is_error_panduan) {
            DB::rollBack();
            echo "Gagal tarik panduan akreditasi dan LED: $kodeLembaga";
        } else {
            echo "Berhasil tarik panduan akreditasi dan LED: $kodeLembaga";
            DB::commit();
        }
    }

    public static function tarikPanduanPengisian($kodeLembaga, $kodePengisianPanduan = null)
    {
        $service = new AccreditationSync();

        $data_panduan = $service->getGuide($kodeLembaga);

        if (!empty($kodePengisianPanduan)) {
            $data_panduan['data'] = array_filter($data_panduan['data'], function ($item) use ($kodePengisianPanduan) {
                return $item['kodebanpt'] == $kodePengisianPanduan;
            });
        }

        // transaction
        DB::beginTransaction();

        // create panduan
        $idsAkreditasiBuku = AkreditasiBuku::pluck('id', 'kode_buku')->toArray();
        $is_error_panduan = false;
        foreach ($data_panduan['data'] as $rp) {
            try {
                $payload = [
                    'kode_pengisian_panduan' => $rp['kodebanpt'],
                    'nama_pengisian_panduan' => $rp['namapanduan'],
                    'nama_singkat' => $rp['namasingkat'],
                    'id_lembaga_akreditasi' => LembagaAkreditasi::where('kode_lembaga', $kodeLembaga)->value('id'),
                    'kode_level_akses' => $rp['idlevelakses'],
                    'id_akreditasi_buku' => AkreditasiBuku::where('kode_buku', $rp['idbukuakreditasi'])->value('id'),
                    'id_jenis_standar' => JenisStandar::where('kode_jenis_standar', $rp['idstandarakreditasi'])->value('id'),
                    'tipe_edisi' => $rp['idedisi'] == 'ED' ? AkreditasiBuku::SELF_EVALUATION : AkreditasiBuku::PERFORMANCE_REPORT,
                    'id_akreditasi_buku' => $rp['idedisi'] == 'ED' ? $idsAkreditasiBuku['LED'] : $idsAkreditasiBuku['LKPS'],
                    'apakah_aktif' => false,
                    'tanggal_edisi' => $rp['tgledisi'],
                    'tanggal_efektif' => $rp['tglberlaku'],
                    'tanggal_kadaluwarsa' => $rp['tglberakhir'],
                    'apakah_sapto' => $rp['issapto'] == '1' ? true : false,
                ];

                if (!empty($rp['idled'])) {
                    $payload['id_pengisian_panduan'] = PengisianPanduan::where('kode_pengisian_panduan', $rp['idled'])->value('id');
                }

                PengisianPanduan::updateOrCreate(
                    ['kode_pengisian_panduan' => $rp['kodebanpt']],
                    $payload
                );
            } catch (\Exception $e) {
                DB::rollBack();
                $is_error_panduan = true;
                echo $e->getMessage();
            }
        }

        // commit transaction
        if ($is_error_panduan) {
            DB::rollBack();
            echo "Gagal tarik panduan akreditasi dan LED: $kodeLembaga";
        } else {
            echo "Berhasil tarik panduan akreditasi dan LED: $kodeLembaga";
            DB::commit();
        }
    }

    public static function tarikPanduanPenilaianIAPSByJenjang($kodeJenjang, $fileJson)
    {
        $jenjang = JenjangPendidikan::where('kode_jenjang', $kodeJenjang)->first();

        // Jika jenjang tidak ada maka skip terlebih dahulu
        if (empty($jenjang)) {
            return;
        }

        $panduanPengisianIAPS = PengisianPanduan::where('kode_pengisian_panduan', 'IAPS9')->first();
        $panduanPengisianLEDPS = PengisianPanduan::where('kode_pengisian_panduan', 'LEDPS9')->first();
        $standar9Kriteria = JenisStandar::where('kode_jenis_standar', '9S')->first();

        $penilaianPanduanD4 = PenilaianPanduan::create([
            "kode_penilaian_panduan" => "IAPS-$kodeJenjang",
            "nama_penilaian_panduan" => "Instrumen Akreditasi Program Studi Versi 4.0 Matriks Penilaian Program Pendidkan " . $jenjang->nama_jenjang,
            "id_laporan_kinerja" => $panduanPengisianIAPS->id,
            "id_panduan_evaluasi_diri" => $panduanPengisianLEDPS->id,
            "nama_singkat" => "IAPS 4.0 Penilaian Program $kodeJenjang",
            "tanggal_edisi" => null,
            "id_jenjang_pendidikan" => $jenjang->id,
            "id_jenis_standar" => $standar9Kriteria->id,
            "id_jenis_perguruan_tinggi" => null,
            "apakah_ptn" => false,
            "deskripsi" => null,
            "id_dokumen" => null,
            "id_tipe" => null,
            "apakah_aktif" => true,
            "dapat_lihat_skor_akhir" => true,
            "total_indikator_matriks" => 0,

        ]);

        // Get json file from Modules/SPMI/Data/Json
        $penilaianMatriks = json_decode(file_get_contents(base_path("Modules/SPMI/Data/Json/$fileJson.json")), true);

        [$err, $msg] = MigrationService::migratePenilaianMatrix($penilaianPanduanD4->kode_penilaian_panduan, $penilaianMatriks);

        if ($err) {
            throw new Exception($msg);
        }
    }

    public static function fixDataMatriksPenilaianIkt()
    {
        dump('Fixing matriks penilaian IKT');
        $listPanduanPenilaian = PenilaianPanduan::orderBy('id', 'asc')
            ->get();

        foreach ($listPanduanPenilaian as $panduanPenilaian) {
            DB::beginTransaction();
            $penilaianMatriksIkt = PenilaianMatriks::where('id_penilaian_panduan', $panduanPenilaian->id)
                ->where('apakah_data_default', false)
                ->get();

            if (empty($penilaianMatriksIkt)) {
                continue;
            }

            // Kosongkan info left dan right
            $matriksPenilaianElemenKriteria = PenilaianMatriks::where('id_penilaian_panduan', $panduanPenilaian->id)
                ->where('nomor_penilaian', "C")->first();

            $matriksPenilaianElemenTambahan = PenilaianMatriks::where('id_penilaian_panduan', $panduanPenilaian->id)->where('nomor_penilaian', "C.10")->first();

            try {
                // update matriks
                if (empty($matriksPenilaianElemenTambahan)) {
                    $matriksPenilaianElemenTambahan = new PenilaianMatriks();
                    $matriksPenilaianElemenTambahan->id_penilaian_panduan = $panduanPenilaian->id;
                    $matriksPenilaianElemenTambahan->id_parent = $matriksPenilaianElemenKriteria->id;
                    $matriksPenilaianElemenTambahan->nomor_penilaian = "C.10";
                    $matriksPenilaianElemenTambahan->kategori_penilaian = "E";
                    $matriksPenilaianElemenTambahan->pertanyaan_penilaian = "C.10. Indikator Tambahan";
                    $matriksPenilaianElemenTambahan->jenis_penilaian = "SA";
                    $matriksPenilaianElemenTambahan->apakah_nilai_ditampilkan = true;
                    $matriksPenilaianElemenTambahan->apakah_data_default = false;
                    $matriksPenilaianElemenTambahan->apakah_aktif = 1;
                    $matriksPenilaianElemenTambahan->save();
                } else {
                    $matriksPenilaianElemenTambahan->update([
                        'id_parent' => null
                    ]);

                    $matriksPenilaianElemenTambahan->update([
                        'id_parent' => $matriksPenilaianElemenKriteria->id
                    ]);
                }
            } catch (\Throwable $th) {
                DB::rollBack();
                throw $th;
            }

            foreach ($penilaianMatriksIkt as $matriks) {
                if ($matriks->id === $matriksPenilaianElemenTambahan->id) {
                    continue;
                }

                try {
                    $matriks->update([
                        'id_parent' => null
                    ]);

                    $matriks->update([
                        'id_parent' => $matriksPenilaianElemenTambahan->id
                    ]);
                    dump('Update matriks ' . $matriks->id . ' ke ' . $matriksPenilaianElemenTambahan->id);
                } catch (\Throwable $th) {
                    DB::rollBack();
                    throw $th;
                }
            }

            DB::commit();
        }
    }

    /**
     * Perbaikan tipe data pengisian indikator
     *
     * @param int $idPengisianIndikator
     * @param array $butirPengisian
     */
    public static function fixDataTypeNumericPengisianIndikator($idPengisianIndikator, $kodePengisian, $butirPengisian)
    {
        if (empty($idPengisianIndikator)) {
            throw new Exception('ID Pengisian Indikator tidak boleh kosong');
        }

        if (empty($kodePengisian)) {
            throw new Exception('Kode pengisian tidak boleh kosong');
        }

        if (empty($butirPengisian)) {
            throw new Exception('Data butir pengisian tidak boleh kosong');
        }

        $panduanPengisian = PengisianPanduan::where('kode_pengisian_panduan', $kodePengisian)->first();
        $indikatorLaporanKinerja = IndikatorLaporanKinerja::where('id_pengisian_panduan', $panduanPengisian->id)
            ->where('nomor_indikator', $butirPengisian)->first();

        $dataPengisianLk = DataPengisianLK::where('id_pengisian_indikator', $idPengisianIndikator)
            ->where('id_indikator_laporan_kinerja', $indikatorLaporanKinerja->id)
            ->first();

        $oldData = json_decode($dataPengisianLk->data_pengisian_lk, true);
        $newData = $oldData;

        foreach ($newData as $key => &$items) {
            dump('Memperbaiki baris ' . $key . ' dari ' . $idPengisianIndikator);
            foreach ($items as &$rowValue) {
                foreach ($rowValue as $columnKey => $columnValue) {
                    if (!is_string($columnValue)) {
                        continue;
                    }

                    $explodedString = explode('.', $columnValue);
                    $lastExplodedString = last($explodedString);

                    // Jika digit terakhir hanya 2 digit maka akan dihapus
                    if (strlen($lastExplodedString) == 2) {
                        $explodedString = array_slice($explodedString, 0, -1);
                    }

                    $implodeString = implode('', $explodedString);

                    // Jika hasil implode adalah angka maka akan diubah menjadi integer
                    if (is_numeric($implodeString)) {
                        $rowValue[$columnKey] = (int) $implodeString;
                    }
                }
            }
        }

        DB::beginTransaction();

        $dataPengisianLk->update([
            'data_pengisian_lk' => json_encode($newData),
            'data_pengisian_lk_awal' => json_encode($oldData),
        ]);

        DB::commit();
    }

    /**
     * Perbaikan tipe data pengisian indikator
     *
     * @param int $idPengisianIndikator
     * @param array $butirPengisian
     */
    public static function fixDataTypeTextareaPengisianIndikator3a1()
    {
        // Get all data pengisian indikator
        $sql =
            "SELECT
                pi.id,
                ap.tahun_audit,
                jp.kode_jenjang || ' - ' || u.nama_unit nama_unit
            FROM spmi.pengisian_indikator pi
            JOIN core.unit_kerja u ON u.id = pi.id_unit AND u.waktu_dihapus IS NULL
            JOIN core.jenjang_pendidikan jp ON jp.id = u.id_jenjang_pendidikan AND jp.waktu_dihapus IS NULL
            JOIN spmi.audit_periode ap ON ap.id = pi.id_audit_periode AND ap.waktu_dihapus IS NULL";

        $listPengisianIndikator = DB::select($sql);
        $listPengisianIndikator = json_decode(json_encode($listPengisianIndikator), true);

        dump('Mulai memperbaiki data pengisian indikator\n');
        foreach ($listPengisianIndikator as $pengisianIndikator) {
            dump('Memperbaiki data pengisian indikator = ' . $pengisianIndikator['tahun_audit'] . ' ' . $pengisianIndikator['nama_unit']);

            $idPengisianIndikator = $pengisianIndikator['id'];

            $panduanPengisian = PengisianPanduan::where('kode_pengisian_panduan', 'IAPS9')->first();
            $indikatorLaporanKinerja = IndikatorLaporanKinerja::where('id_pengisian_panduan', $panduanPengisian->id)
                ->where('nomor_indikator', '3a.1')->first();

            $dataPengisianLk = DataPengisianLK::where('id_pengisian_indikator', $idPengisianIndikator)
                ->where('id_indikator_laporan_kinerja', $indikatorLaporanKinerja->id)
                ->first();

            if (empty($dataPengisianLk)) {
                dump('Data pengisian indikator = ' . $pengisianIndikator['tahun_audit'] . ' ' . $pengisianIndikator['nama_unit'] . ' tidak ditemukan');
                continue;
            }

            $oldData = json_decode($dataPengisianLk->data_pengisian_lk, true);
            $newData = $oldData;

            foreach ($newData as $key => &$items) {
                dump('Memperbaiki kolom ' . $key . ' dari ' . $idPengisianIndikator);
                $keys = [4, 5];
                foreach ($items as $key => &$rowValue) {
                    if (!in_array($key, $keys)) {
                        continue;
                    }

                    if ($rowValue === null) {
                        continue;
                    }

                    $boolValue = (bool) $rowValue;
                    if ($boolValue) {
                        $rowValue = 'Ya';
                    } else {
                        $rowValue = 'Tidak';
                    }
                }
            }

            DB::beginTransaction();

            try {
                $dataPengisianLk->update([
                    'data_pengisian_lk' => json_encode($newData),
                    'data_pengisian_lk_awal' => json_encode($oldData),
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                dump('Gagal memperbaiki data pengisian indikator = ' . $pengisianIndikator['tahun_audit'] . ' ' . $pengisianIndikator['nama_unit']);
            }

            DB::commit();
        }
    }

    public static function syncSyaratTerakreditasi()
    {
        $accreditationSync = new AccreditationSync;

        $assessmentGuideData = PenilaianPanduan::get();
        $assessmentGuideList = $assessmentGuideData->pluck('id', 'kode_penilaian_panduan')->toArray();

        foreach ($assessmentGuideList as $assessmentGuideCode => $assessmentGuideId) {
            $akreditasiSyarat = $accreditationSync->getAkreditasiSyarat($assessmentGuideCode);
            $akreditasiSyarat = $akreditasiSyarat['data'] ?? [];

            if (empty($akreditasiSyarat)) {
                continue;
            }

            dump("Start sync syarat terakreditasi $assessmentGuideCode");

            DB::beginTransaction();

            // hapus semua matriks penilaian berdasarkan panduan penilaian terlebih dahulu
            AkreditasiSyarat::where('id_penilaian_panduan', $assessmentGuideId)->delete();

            // Referensi Matriks Penilaian
            $mappedAssessmentMatrices = array_map(fn($item) => $item['nobutirspmi'], $akreditasiSyarat);
            $refAssessmentMatrices = PenilaianMatriks::where('id_penilaian_panduan', $assessmentGuideId)
                ->whereIn('nomor_penilaian', $mappedAssessmentMatrices)->pluck('id', 'nomor_penilaian')->toArray();

            // Referensi Peringkat Akreditasi
            $mappedAkreditasiPeringkats = array_map(fn($item) => $item['kodeakreditasi'], $akreditasiSyarat);
            $refAkreditasiPeringkats = AkreditasiPeringkat::whereIn('kode_peringkat', $mappedAkreditasiPeringkats)->pluck('id', 'kode_peringkat')->toArray();

            foreach ($akreditasiSyarat as $akreditasiSyarat) {
                $data = [
                    'id_penilaian_panduan' => $assessmentGuideId,
                    'id_penilaian_matriks' => $refAssessmentMatrices[$akreditasiSyarat['nobutirspmi']] ?? null,
                    'id_akreditasi_peringkat' => $refAkreditasiPeringkats[$akreditasiSyarat['kodeakreditasi']] ?? null,
                    'jenis_syarat_akreditasi' => $akreditasiSyarat['kodesyarat'],
                    'nilai_syarat_akreditasi' => $akreditasiSyarat['skor']
                ];

                try {
                    AkreditasiSyarat::updateOrCreate(
                        Arr::only($data, ['id_penilaian_panduan', 'jenis_syarat_akreditasi', 'id_akreditasi_peringkat', 'id_penilaian_matriks']),
                        $data
                    );
                } catch (\Exception $e) {
                    DB::rollback();
                    throw $e;
                }
            }

            DB::commit();
        }
    }
}
