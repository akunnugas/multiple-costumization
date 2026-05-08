<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('alter table spmi.target_indikator drop constraint spmi_target_indikator_id_audit_periode_id_unit_unique');

        Schema::table('spmi.target_indikator', function (Blueprint $table) {
            $table->unique(['id_audit_periode', 'id_unit', 'id_penilaian_panduan']);
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
