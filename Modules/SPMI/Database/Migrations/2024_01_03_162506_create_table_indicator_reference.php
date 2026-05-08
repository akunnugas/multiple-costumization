<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.indikator_referensi', function (SevimaBlueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_indikator_evaluasi_diri')->comment('ID Indikator Evaluasi Diri');
            $table->unsignedBigInteger('id_pengisian_panduan')->comment('Panduan Indicator');
            $table->unsignedBigInteger('id_indikator_laporan_kinerja')->comment('ID Indikator Laporan Kinerja');

            // foreign key
            $table->foreign('id_indikator_evaluasi_diri')->references('id')->on('spmi.indikator_evaluasi_diri');
            $table->foreign('id_pengisian_panduan')->references('id')->on('spmi.pengisian_panduan');
            $table->foreign('id_indikator_laporan_kinerja')->references('id')->on('spmi.indikator_laporan_kinerja');

            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.indikator_referensi');
    }
};
