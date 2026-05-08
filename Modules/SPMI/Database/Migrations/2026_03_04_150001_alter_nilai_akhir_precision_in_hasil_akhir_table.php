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
        DB::statement('ALTER TABLE spmi.hasil_akhir_audit ALTER COLUMN nilai_iku TYPE numeric(8,2)');
        DB::statement('ALTER TABLE spmi.hasil_akhir_audit ALTER COLUMN nilai_ikt TYPE numeric(8,2)');
        DB::statement('ALTER TABLE spmi.hasil_akhir_audit ALTER COLUMN nilai_akhir TYPE numeric(8,2)');
        DB::statement('ALTER TABLE spmi.hasil_akhir_audit ALTER COLUMN nilai_akhir_auditee TYPE numeric(8,2)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE spmi.hasil_akhir_audit ALTER COLUMN nilai_iku TYPE numeric(5,2)');
        DB::statement('ALTER TABLE spmi.hasil_akhir_audit ALTER COLUMN nilai_ikt TYPE numeric(5,2)');
        DB::statement('ALTER TABLE spmi.hasil_akhir_audit ALTER COLUMN nilai_akhir TYPE numeric(5,2)');
        DB::statement('ALTER TABLE spmi.hasil_akhir_audit ALTER COLUMN nilai_akhir_auditee TYPE numeric(5,2)');
    }
};
