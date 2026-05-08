<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Models\PengisianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // update foreign key
        SevimaSchema::table('spmi.jadwal_audit_unit', function (Blueprint $table) {
            $table->foreign('id_pengisian_panduan')->references('id')->on('spmi.pengisian_panduan');
            $table->foreign('id_penilaian_panduan')->references('id')->on('spmi.penilaian_panduan');
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
