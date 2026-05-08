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
        DB::statement('update spmi.penilaian_matriks set deskripsi = \'RASIO = JD / JP\' where id = (
            select pm.id from spmi.penilaian_matriks pm
                join spmi.penilaian_panduan pp on pp.id = pm.id_penilaian_panduan and pp.kode_penilaian_panduan = \'IAPS-D4\'
            where pm.nomor_penilaian = \'14.b\' limit 1
        )');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
