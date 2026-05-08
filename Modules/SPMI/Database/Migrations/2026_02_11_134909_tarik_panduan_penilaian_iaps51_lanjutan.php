<?php

use Modules\Core\Models\LembagaAkreditasi;
use Modules\SPMI\Services\NewAkreditasiManagementServices;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\JenjangPendidikan;
use Modules\SPMI\Models\JenisStandar;
use Modules\SPMI\Models\MappingPanduan;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $services = new NewAkreditasiManagementServices();

        $listPanduanBaru = $services->getPanduanPenilaian([
            "IAPS5.1-S1-Ung",
            "IAPS5.1-S2-Akre",
            "IAPS5.1-S2-Ung"
        ]);
        $listPanduanBaru = json_decode(json_encode($listPanduanBaru), true);

        $panduanPengisian = PengisianPanduan::where([
            "kode_pengisian_panduan" => "IAPS5.1",
            "apakah_data_default" => true
        ])->first();

        $temp = [];
        foreach ($listPanduanBaru as $panduanRaw) {
            $standarKriteria = JenisStandar::where("kode_jenis_standar", "4S")->first();
            $jenjang = JenjangPendidikan::where(
                "kode_jenjang",
                $panduanRaw["idjenjang"]
            )->first();

            $penilaianPanduanNew = PenilaianPanduan::create([
                "kode_penilaian_panduan" => $panduanRaw["kodespmi"],
                "nama_penilaian_panduan" => $panduanRaw["namapanduan"],
                "id_laporan_kinerja" => $panduanPengisian->id,
                "nama_singkat" => $panduanRaw["namasingkat"],
                "tanggal_edisi" => $panduanRaw["tgledisi"],
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
                "apakah_data_default" => true,
                "apakah_iku_kualitatif" => false
            ]);

            $temp[] = $penilaianPanduanNew;
        }
        foreach ($temp as $panduanPenilaian) {
            $listSkors = [
                0 => "Tidak Memenuhi",
                1 => "Memenuhi"
            ];

            foreach ($listSkors as $num => $label) {
                SkorMatriksPredikatPenilaian::create([
                    "id_penilaian_panduan" => $panduanPenilaian->id,
                    "nilai" => $num,
                    "deskripsi" => $label
                ]);
            }

            MappingPanduan::create([
                "id_pengisian_panduan" => $panduanPengisian->id,
                "id_penilaian_panduan" => $panduanPenilaian->id
            ]);

            $services->syncButirMatriks($panduanPenilaian->kode_penilaian_panduan);

            // $syaratPerlu = $services->getSyaratPerlu($panduanPenilaian->kode_penilaian_panduan);
            // if (!empty($syaratPerlu)) {
            //     $services->syncSyaratPerlu($panduanPenilaian->kode_penilaian_panduan, $syaratPerlu);
            // }
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
