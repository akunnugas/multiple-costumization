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
        // 1. Add the column
        DB::statement('ALTER TABLE spmi.pengisian_indikator ADD COLUMN IF NOT EXISTS id_jadwal_audit BIGINT NULL');
        DB::statement('COMMENT ON COLUMN spmi.pengisian_indikator.id_jadwal_audit IS \'FK ke jadwal_audit, untuk membedakan pengisian per jadwal\'');

        // 2. Migrasi data lama LK pengisian_indikator.id_pengisian_panduan = jadwal_audit_unit.id_pengisian_panduan
        DB::statement("
            UPDATE spmi.pengisian_indikator pi
            SET id_jadwal_audit = sub.id_jadwal_audit
            FROM (
                SELECT DISTINCT ON (pi2.id) pi2.id AS id_pengisian_indikator, ja.id AS id_jadwal_audit
                FROM spmi.pengisian_indikator pi2
                JOIN spmi.jadwal_audit ja ON ja.id_audit_periode = pi2.id_audit_periode AND ja.waktu_dihapus IS NULL
                JOIN spmi.jadwal_audit_unit jau ON jau.id_jadwal_audit = ja.id
                    AND jau.id_unit = pi2.id_unit
                    AND jau.id_pengisian_panduan = pi2.id_pengisian_panduan
                WHERE pi2.id_jadwal_audit IS NULL
                    AND pi2.jenis_indikator = 'pr'
                ORDER BY pi2.id, ja.id
            ) sub
            WHERE pi.id = sub.id_pengisian_indikator
        ");

        // 3. Migrasi data lama LED: pengisian_indikator.id_pengisian_panduan = parent of jadwal_audit_unit.id_pengisian_panduan
        DB::statement("
            UPDATE spmi.pengisian_indikator pi
            SET id_jadwal_audit = sub.id_jadwal_audit
            FROM (
                SELECT DISTINCT ON (pi2.id) pi2.id AS id_pengisian_indikator, ja.id AS id_jadwal_audit
                FROM spmi.pengisian_indikator pi2
                JOIN spmi.jadwal_audit ja ON ja.id_audit_periode = pi2.id_audit_periode AND ja.waktu_dihapus IS NULL
                JOIN spmi.jadwal_audit_unit jau ON jau.id_jadwal_audit = ja.id
                    AND jau.id_unit = pi2.id_unit
                JOIN spmi.pengisian_panduan pp ON pp.id = jau.id_pengisian_panduan
                    AND pp.id_pengisian_panduan = pi2.id_pengisian_panduan
                WHERE pi2.id_jadwal_audit IS NULL
                    AND pi2.jenis_indikator = 'se'
                ORDER BY pi2.id, ja.id
            ) sub
            WHERE pi.id = sub.id_pengisian_indikator
        ");

        // 4. Buat jadi not null
        // DB::statement('ALTER TABLE spmi.pengisian_indikator ALTER COLUMN id_jadwal_audit SET NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE spmi.pengisian_indikator DROP COLUMN IF EXISTS id_jadwal_audit');
    }
};

