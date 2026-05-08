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
        // update pengisian panduan
        DB::statement('update spmi.pengisian_panduan set apakah_aktif = false where id not in (
            select pp.id from spmi.pengisian_panduan pp
                join core.lembaga_akreditasi la on pp.id_lembaga_akreditasi = la.id
            where la.kode_lembaga = \'BANPT\'
        );');

        // hapus penilaian matriks referensi untuk IAPS-Prof (duplikat)
        DB::statement('delete from spmi.penilaian_matriks_referensi where id_penilaian_matriks in (
            select id from spmi.penilaian_matriks where id_penilaian_panduan = (
                select id from spmi.penilaian_panduan where kode_penilaian_panduan = \'IAPS-Prof\' limit 1
            )
        );');

        // hapus penilaian matriks predikat untuk IAPS-Prof (duplikat)
        DB::statement('delete from spmi.penilaian_matriks_predikat where id_penilaian_matriks in (
            select id from spmi.penilaian_matriks where id_penilaian_panduan = (
                select id from spmi.penilaian_panduan where kode_penilaian_panduan = \'IAPS-Prof\' limit 1
            )
        );');

        // hapus penilaian matriks untuk IAPS-Prof (duplikat)
        DB::statement('delete from spmi.penilaian_matriks where id_penilaian_panduan = (
            select id from spmi.penilaian_panduan where kode_penilaian_panduan = \'IAPS-Prof\' limit 1
        );');

        // hapus penilaian panduan untuk IAPS-Prof (duplikat)
        DB::statement('delete from spmi.penilaian_panduan where id = (
            select id from spmi.penilaian_panduan where kode_penilaian_panduan = \'IAPS-Prof\' limit 1
        );');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
