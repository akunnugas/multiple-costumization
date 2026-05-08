<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Models\JenjangPendidikan;
use Modules\SPMI\Models\JenisStandar;
use Modules\SPMI\Models\MappingPanduan;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;
use Modules\SPMI\Services\NewAkreditasiManagementServices;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $services = new NewAkreditasiManagementServices();

        $defaultScoreIAPS4 = [
            0 => "Sangat Kurang",
            1 => "Kurang",
            2 => "Cukup",
            3 => "Baik",
            4 => "Sangat Baik",
        ];

        $configPanduan = [
            "LAMDIK-S1-3.0" => [
                "kode_lk" => "LAMDIK5",
                "kode_led" => "EDIKS1",
                "kode_lembaga" => "LAMDIK",
                "kode_standar" => "LDK",
                "scores" => $defaultScoreIAPS4
            ],
            "INFOKOM2.1S1" => [
                "kode_lk" => "INFOKS1",
                "kode_led" => "DINFOS1",
                "kode_lembaga" => "LAMINFOKOM",
                "kode_standar" => "INF",
                "scores" => $defaultScoreIAPS4
            ],
            "LAMTEKNIKS1-25" => [
                "kode_lk" => "LKTEK25",
                "kode_led" => "LEDTT25",
                "kode_lembaga" => "LAMTEKNIK",
                "kode_standar" => "LKT",
                "scores" => $defaultScoreIAPS4
            ]
        ];

        $listPanduanBaru = $services->getPanduanPenilaian(array_keys($configPanduan));
        $listPanduanBaru = json_decode(json_encode($listPanduanBaru), true);

        foreach ($listPanduanBaru as $panduanRaw) {
            if (empty($panduanRaw["idjenjang"])) {
                echo "\n Panduan " .
                    $panduanRaw["namapanduan"] .
                    " di lewati karena tidak memiliki jenjang";
                continue; // skip panduan tanpa jenjang
            }

            [
                "kode_lk" => $kodeLK,
                "kode_led" => $kodeLED,
                "kode_lembaga" => $kodeLembaga,
                "kode_standar" => $kodeStandar,
                "scores" => $listSkors,
            ] = $configPanduan[$panduanRaw["kodespmi"]];

            $services->tarikPanduanPengisian($kodeLED, $kodeLembaga, true);
            $services->syncButirLEDPanduan($kodeLED);

            PengisianPanduan::where("kode_pengisian_panduan", $kodeLED)->where("apakah_data_default", true)->update([
                "apakah_iku_kualitatif" => true,
            ]);

            $services->tarikPanduanPengisian($kodeLK, $kodeLembaga, true);
            $services->syncButirLKPanduan($kodeLK);

            PengisianPanduan::where("kode_pengisian_panduan", $kodeLK)->where("apakah_data_default", true)->update([
                "apakah_iku_kualitatif" => true,
            ]);

            $services->syncStandarAkreditasi($kodeStandar);

            $panduanPengisian = PengisianPanduan::where([
                "kode_pengisian_panduan" => $kodeLK,
                "apakah_data_default" => true
            ])->first();

            $standarKriteria = JenisStandar::where(
                "kode_jenis_standar",
                $kodeStandar
            )->first();

            $jenjang = JenjangPendidikan::where(
                "kode_jenjang",
                $panduanRaw["idjenjang"]
            )->first();

            $penilaianPanduanNew = PenilaianPanduan::updateOrCreate(
                [
                    "kode_penilaian_panduan" => $panduanRaw["kodespmi"],
                    'apakah_data_default' => true
                ],
                [
                    "nama_penilaian_panduan" => $panduanRaw["namapanduan"],
                    "nama_singkat" => $panduanRaw["namasingkat"],
                    "tanggal_edisi" => $panduanRaw["tgledisi"],
                    "id_laporan_kinerja" => $panduanPengisian->id,
                    "id_jenjang_pendidikan" => $jenjang->id,
                    "id_jenis_standar" => $standarKriteria->id,
                    "id_jenis_perguruan_tinggi" => null,
                    "apakah_ptn" => false,
                    "deskripsi" => null,
                    "id_dokumen" => null,
                    "id_tipe" => null,
                    "apakah_aktif" => true,
                    "dapat_lihat_skor_akhir" => true,
                    "total_indikator_matriks" => 0,
                    "apakah_iku_kualitatif" => true
                ]
            );

            foreach ($listSkors as $num => $label) {
                $skorMatrik = SkorMatriksPredikatPenilaian::where([
                    "id_penilaian_panduan" => $penilaianPanduanNew->id,
                    "nilai" => $num,
                ])->first();
                if (!$skorMatrik) {
                    SkorMatriksPredikatPenilaian::create([
                        "id_penilaian_panduan" => $penilaianPanduanNew->id,
                        "nilai" => $num,
                        "deskripsi" => $label
                    ]);
                } else {
                    $skorMatrik->update([
                        "deskripsi" => $label
                    ]);
                }
            }

            $mappingPanduan = MappingPanduan::where([
                "id_pengisian_panduan" => $panduanPengisian->id,
                "id_penilaian_panduan" => $penilaianPanduanNew->id
            ])->first();
            if (!$mappingPanduan) {
                MappingPanduan::create([
                    "id_pengisian_panduan" => $panduanPengisian->id,
                    "id_penilaian_panduan" => $penilaianPanduanNew->id
                ]);
            }

            $services->syncButirMatriks($penilaianPanduanNew->kode_penilaian_panduan);

            $syaratPerlu = $services->getSyaratPerlu($penilaianPanduanNew->kode_penilaian_panduan);
            if (!empty($syaratPerlu)) {
                $services->syncSyaratPerlu($penilaianPanduanNew->kode_penilaian_panduan, $syaratPerlu);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
