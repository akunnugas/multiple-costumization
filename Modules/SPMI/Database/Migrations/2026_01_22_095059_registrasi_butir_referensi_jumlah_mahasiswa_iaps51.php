<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\TarikDataLK;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $indikatorLK = IndikatorLaporanKinerja::where('nomor_indikator', 'XREF.1')->first();
        TarikDataLK::create([
            'id_indikator_laporan_kinerja' => $indikatorLK->id,
            'apakah_kurikulum' => false,
            'sumber' => 'siakad',
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
