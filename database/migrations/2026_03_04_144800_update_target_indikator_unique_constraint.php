<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     */
    public function up(): void
    {
        // Drop the old unique constraint
        DB::statement('ALTER TABLE spmi.target_indikator DROP CONSTRAINT IF EXISTS spmi_target_indikator_id_audit_periode_id_unit_id_penilaian_pan');

        // Create new unique constraint including id_jadwal_audit
        DB::statement('ALTER TABLE spmi.target_indikator ADD CONSTRAINT spmi_target_indikator_periode_unit_panduan_jadwal_unique UNIQUE (id_audit_periode, id_unit, id_penilaian_panduan, id_jadwal_audit)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE spmi.target_indikator DROP CONSTRAINT IF EXISTS spmi_target_indikator_periode_unit_panduan_jadwal_unique');
        DB::statement('ALTER TABLE spmi.target_indikator ADD CONSTRAINT spmi_target_indikator_id_audit_periode_id_unit_id_penilaian_pan UNIQUE (id_audit_periode, id_unit, id_penilaian_panduan)');
    }
};
