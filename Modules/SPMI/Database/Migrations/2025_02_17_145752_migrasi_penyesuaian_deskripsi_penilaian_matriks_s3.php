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
        $listSql = [
            "update spmi.penilaian_matriks_predikat set deskripsi = 'Jika MS ≤ 2, maka skor 0' where id = (
                select id from spmi.penilaian_matriks_predikat pmp where id_penilaian_matriks = (
                    select id from spmi.penilaian_matriks where id_penilaian_panduan = 5 and nomor_penilaian = '52'
                ) and nilai = '0'
            );",
            "update spmi.penilaian_matriks set pertanyaan_penilaian = 'Masa studi Tabel 8.c LKPS' where id = (
                select id from spmi.penilaian_matriks where id_penilaian_panduan = 5 and nomor_penilaian = '52'
            );",
            "update spmi.penilaian_matriks_predikat set deskripsi = 'Jika PPDM < 50% , maka Skor = 1 + (6 x PPDM)' where id = (
                select id from spmi.penilaian_matriks_predikat pmp where id_penilaian_matriks = (
                    select id from spmi.penilaian_matriks where id_penilaian_panduan = 5 and nomor_penilaian = '47'
                ) and nilai = '1'
            );",
            "update spmi.penilaian_matriks_predikat set deskripsi = 'Jika PPDM < 50% , maka Skor = 1 + (6 x PPDM)' where id = (
                select id from spmi.penilaian_matriks_predikat pmp where id_penilaian_matriks = (
                    select id from spmi.penilaian_matriks where id_penilaian_panduan = 5 and nomor_penilaian = '47'
                ) and nilai = '2'
            );",
            "update spmi.penilaian_matriks_predikat set deskripsi = 'Jika PPDM < 50% , maka Skor = 1 + (6 x PPDM)' where id = (
                select id from spmi.penilaian_matriks_predikat pmp where id_penilaian_matriks = (
                    select id from spmi.penilaian_matriks where id_penilaian_panduan = 5 and nomor_penilaian = '47'
                ) and nilai = '3'
            );",
            "update spmi.penilaian_matriks_predikat set deskripsi = 'Jika PPDM ≥ 50%, maka Skor = 4' where id = (
                select id from spmi.penilaian_matriks_predikat pmp where id_penilaian_matriks = (
                    select id from spmi.penilaian_matriks where id_penilaian_panduan = 5 and nomor_penilaian = '47'
                ) and nilai = '4'
            );"
        ];

        foreach ($listSql as $sql) {
            DB::statement($sql);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
