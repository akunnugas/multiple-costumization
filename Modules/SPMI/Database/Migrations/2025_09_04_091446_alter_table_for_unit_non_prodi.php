<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\IndikatorLaporanKinerja;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // alter table
        SevimaSchema::table('spmi.pengisian_panduan', function (Blueprint $table) {
            $table->boolean('apakah_data_default')->default(true);
        });

        SevimaSchema::table('spmi.indikator_laporan_kinerja', function (Blueprint $table) {
            $table->string('jenis_mapping', 20)->nullable();
        });

        SevimaSchema::table('spmi.indikator_evaluasi_diri', function (Blueprint $table) {
            $table->string('jenis_mapping', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
