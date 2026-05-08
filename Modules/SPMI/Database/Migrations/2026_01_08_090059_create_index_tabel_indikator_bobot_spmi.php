<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sqlIndexBobot = "CREATE INDEX CONCURRENTLY idx_indikator_bobot_lookup
            ON spmi.indikator_bobot (
                id_audit_periode,
                jenis_indikator_bobot,
                id_unit,
                id_penilaian_panduan
            );";

        DB::statement($sqlIndexBobot);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            DROP INDEX CONCURRENTLY IF EXISTS spmi.idx_indikator_bobot_lookup
        ");
    }
};
