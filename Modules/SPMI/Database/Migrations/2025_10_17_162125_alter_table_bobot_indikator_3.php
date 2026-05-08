<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorBobot;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // update foreign key constraints
        SevimaSchema::table('spmi.indikator_bobot', function (Blueprint $table) {
            $table->foreign('id_unit')->references('id')->on('core.unit_kerja');
            $table->foreign('id_penilaian_panduan')->references('id')->on('spmi.penilaian_panduan');
        });

        // alter make required
        SevimaSchema::table('spmi.indikator_bobot', function (Blueprint $table) {
            $table->unsignedBigInteger("id_unit")->nullable(false)->change();
            $table->unsignedBigInteger("id_penilaian_panduan")->nullable(false)->change();
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
