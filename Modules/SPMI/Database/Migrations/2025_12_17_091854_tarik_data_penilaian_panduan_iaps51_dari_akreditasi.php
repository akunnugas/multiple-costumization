<?php

use Modules\SPMI\Models\JenisStandar;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\Core\Models\JenjangPendidikan;
use Modules\SPMI\Models\AkreditasiStandar;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Modules\SPMI\Helpers\AccreditationSync;
use Modules\SPMI\Models\MappingPanduan;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $accreditationSync = new AccreditationSync();
        $penilaianPanduan = $accreditationSync->getPenilaianPanduan();
        $penilaianPanduan = Arr::first($penilaianPanduan['data'], function ($value, $key) {
            return $value['kodespmi'] == 'IAPS5.1-S1-Akre';
        });
        
        $standarKriteria = JenisStandar::where("kode_jenis_standar", "4S")->first();
        $jenjang = JenjangPendidikan::where("kode_jenjang", $penilaianPanduan['idjenjang'])->first();
        $panduanPengisianIAPS = PengisianPanduan::where([
            "kode_pengisian_panduan" => $penilaianPanduan['kodelk'],
            "apakah_data_default" => true
        ])->first();

        $penilaianPanduanIAPS51 = PenilaianPanduan::create([
            "kode_penilaian_panduan" => $penilaianPanduan['kodespmi'],
            "nama_penilaian_panduan" => $penilaianPanduan['namapanduan'],
            "id_laporan_kinerja" => $panduanPengisianIAPS->id,
            "nama_singkat" => $penilaianPanduan['namasingkat'],
            "tanggal_edisi" => $penilaianPanduan['tgledisi'],
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
            "apakah_data_default" => true
        ]);

        $listSkors = [
            0 => "Tidak Memenuhi",
            1 => "Memenuhi"
        ];
        foreach ($listSkors as $num => $label) {
            SkorMatriksPredikatPenilaian::create([
                "id_penilaian_panduan" => $penilaianPanduanIAPS51->id,
                "nilai" => $num,
                "deskripsi" => $label
            ]);
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
