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
        SevimaSchema::table('spmi.jadwal_audit_unit', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pengisian_panduan')->nullable();
            $table->unsignedBigInteger('id_penilaian_panduan')->nullable();
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
