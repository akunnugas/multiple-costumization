<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old unique partial index (id_unit, id_audit_periode) WHERE waktu_dihapus IS NULL
        DB::statement('DROP INDEX IF EXISTS spmi.dokumen_hasil_audit_id_unit_id_audit_periode_idx');

        // Create new unique partial index including id_jadwal_audit
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS dokumen_hasil_audit_unit_periode_jadwal_unique ON spmi.dokumen_hasil_audit (id_unit, id_audit_periode, id_jadwal_audit) WHERE (waktu_dihapus IS NULL)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS spmi.dokumen_hasil_audit_unit_periode_jadwal_unique');
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS dokumen_hasil_audit_id_unit_id_audit_periode_idx ON spmi.dokumen_hasil_audit (id_unit, id_audit_periode) WHERE (waktu_dihapus IS NULL)');
    }
};
