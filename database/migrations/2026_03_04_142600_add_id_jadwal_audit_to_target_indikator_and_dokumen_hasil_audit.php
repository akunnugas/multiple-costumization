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
        // 1. target_indikator — add id_jadwal_audit
        DB::statement('ALTER TABLE spmi.target_indikator ADD COLUMN IF NOT EXISTS id_jadwal_audit BIGINT NULL');

        // Migrasi data lama via penilaian_panduan + unit + audit_periode → jadwal_audit_unit → jadwal_audit
        DB::statement("
            UPDATE spmi.target_indikator ti
            SET id_jadwal_audit = sub.id_jadwal_audit
            FROM (
                SELECT DISTINCT ON (ti2.id) ti2.id AS id_target_indikator, ja.id AS id_jadwal_audit
                FROM spmi.target_indikator ti2
                JOIN spmi.jadwal_audit ja ON ja.id_audit_periode = ti2.id_audit_periode AND ja.waktu_dihapus IS NULL
                JOIN spmi.jadwal_audit_unit jau ON jau.id_jadwal_audit = ja.id
                    AND jau.id_unit = ti2.id_unit
                    AND jau.id_penilaian_panduan = ti2.id_penilaian_panduan
                WHERE ti2.id_jadwal_audit IS NULL
                ORDER BY ti2.id, ja.id
            ) sub
            WHERE ti.id = sub.id_target_indikator
        ");

        // 2. dokumen_hasil_audit — add id_jadwal_audit
        DB::statement('ALTER TABLE spmi.dokumen_hasil_audit ADD COLUMN IF NOT EXISTS id_jadwal_audit BIGINT NULL');

        // Migrasi data lama via unit + audit_periode → jadwal_audit_unit → jadwal_audit
        DB::statement("
            UPDATE spmi.dokumen_hasil_audit dha
            SET id_jadwal_audit = sub.id_jadwal_audit
            FROM (
                SELECT DISTINCT ON (dha2.id) dha2.id AS id_dokumen_hasil_audit, ja.id AS id_jadwal_audit
                FROM spmi.dokumen_hasil_audit dha2
                JOIN spmi.jadwal_audit ja ON ja.id_audit_periode = dha2.id_audit_periode AND ja.waktu_dihapus IS NULL
                JOIN spmi.jadwal_audit_unit jau ON jau.id_jadwal_audit = ja.id
                    AND jau.id_unit = dha2.id_unit
                WHERE dha2.id_jadwal_audit IS NULL
                ORDER BY dha2.id, ja.id
            ) sub
            WHERE dha.id = sub.id_dokumen_hasil_audit
        ");

        // 4. Buat jadi not null
        // DB::statement('ALTER TABLE spmi.target_indikator ALTER COLUMN id_jadwal_audit SET NOT NULL');
        // DB::statement('ALTER TABLE spmi.dokumen_hasil_audit ALTER COLUMN id_jadwal_audit SET NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE spmi.target_indikator DROP COLUMN IF EXISTS id_jadwal_audit');
        DB::statement('ALTER TABLE spmi.dokumen_hasil_audit DROP COLUMN IF EXISTS id_jadwal_audit');
    }
};
