<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\PenilaianMatriks;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Clean html tag spmi.penilaian_matriks
        PenilaianMatriks::withTrashed()->get()->each(function ($item) {
                $item->pertanyaan_penilaian = strip_tags($item->pertanyaan_penilaian);
                $item->saveQuietly();
        });

        IndikatorLaporanKinerja::withTrashed()->get()->each(function ($item) {
            $item->nama_indikator_laporan_kinerja = strip_tags($item->nama_indikator_laporan_kinerja);
            $item->saveQuietly();
        });

        IndikatorEvaluasiDiri::withTrashed()->get()->each(function ($item) {
            $item->nama_indikator_evaluasi_diri = strip_tags($item->nama_indikator_evaluasi_diri);
            $item->saveQuietly();
        });
        
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
