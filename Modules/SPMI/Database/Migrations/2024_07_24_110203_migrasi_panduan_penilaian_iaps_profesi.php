<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Models\JenjangPendidikan;
use Modules\SPMI\Models\JenisStandar;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Services\MigrationService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $jenjangProf = JenjangPendidikan::where('kode_jenjang', 'Prof')->first();

        if ($jenjangProf) {
            $panduanPengisianIAPS = PengisianPanduan::where('kode_pengisian_panduan', 'IAPS9')->first();
            $panduanPengisianLEDPS = PengisianPanduan::where('kode_pengisian_panduan', 'LEDPS9')->first();
            $standar9Kriteria = JenisStandar::where('kode_jenis_standar', '9S')->first();

            $penilaianPandunaProfesi = PenilaianPanduan::create([
                "kode_penilaian_panduan" => "IAPS-PROF",
                "nama_penilaian_panduan" => "Instrumen Akreditasi Program Studi Versi 4.0 Matriks Penilaian Program Profesi",
                "id_laporan_kinerja" => $panduanPengisianIAPS->id,
                "id_panduan_evaluasi_diri" => $panduanPengisianLEDPS->id,
                "nama_singkat" => "IAPS 4.0 Penilaian Program Profesi",
                "tanggal_edisi" => null,
                "id_jenjang_pendidikan" => $jenjangProf->id,
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
            $penilaianMatriksS3 = json_decode(file_get_contents(base_path('Modules/SPMI/Data/Json/MatriksPenilaianProfesi.json')), true);

            [$err, $msg] = MigrationService::migratePenilaianMatrix($penilaianPandunaProfesi->kode_penilaian_panduan, $penilaianMatriksS3);

            if ($err) {
                throw new Exception($msg);
            }

            MigrationService::tarikPanduanPenilaianIAPSByJenjang('Prof', 'MatriksPenilaianProfesi');
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
