<?php

use Modules\SPMI\Models\TarikDataLK;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;
use Illuminate\Database\Migrations\Migration;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\PenilaianMatriksReferensi;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pengisianPanduan = PengisianPanduan::where([
            'kode_pengisian_panduan' => 'IAPS5.1',
            'apakah_data_default' => true,
        ])->first();

        $indikatorLK = IndikatorLaporanKinerja::where([
            'id_pengisian_panduan' => $pengisianPanduan->id,
            'nomor_indikator' => '1.1.a.1',
            'apakah_data_default' => true,
        ])->first();

        $penilaianPanduan = PenilaianPanduan::where(['kode_penilaian_panduan' => 'IAPS5.1-S2-Akre', 'apakah_data_default' => true])->first();
        $matriksPenilaians = PenilaianMatriks::where('id_penilaian_panduan', $penilaianPanduan->id)
            ->whereIn('nomor_penilaian', ['B106D', 'B219B'])->get();

        if ($matriksPenilaians && $indikatorLK) {
            foreach ($matriksPenilaians as $matriksPenilaian) {
                $isExists = PenilaianMatriksReferensi::where([
                    'id_penilaian_matriks' => $matriksPenilaian->id,
                    'id_butir_referensi' => $indikatorLK->id,
                    'jenis_referensi' => PenilaianMatriks::REFERENCE_PERFORMANCE_REPORT,
                ])->exists();

                if (!$isExists) {
                    PenilaianMatriksReferensi::insert([
                        'id_penilaian_matriks' => $matriksPenilaian->id,
                        'id_butir_referensi' => $indikatorLK->id,
                        'jenis_referensi' => PenilaianMatriks::REFERENCE_PERFORMANCE_REPORT,
                    ]);
                }
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
