<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksReferensi;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pengisianPanduan = PengisianPanduan::where(['kode_pengisian_panduan' => 'IAPS5.1', 'apakah_data_default' => true])->first();
        $butirLK = IndikatorLaporanKinerja::where([
            'id_pengisian_panduan' => $pengisianPanduan->id,
            'nomor_indikator' => '1.1.a.1'
        ])->first();

        $penilaianPanduan = PenilaianPanduan::where(['kode_penilaian_panduan' => 'IAPS5.1-S1-Akre', 'apakah_data_default' => true])->first();
        $matriksPenilaian = PenilaianMatriks::where('id_penilaian_panduan', $penilaianPanduan->id)
            ->where('nomor_penilaian', 'B323A')->first();

        PenilaianMatriksReferensi::insert([
            'id_penilaian_matriks' => $matriksPenilaian->id,
            'id_butir_referensi' => $butirLK->id,
            'jenis_referensi' => PenilaianMatriks::REFERENCE_PERFORMANCE_REPORT,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
