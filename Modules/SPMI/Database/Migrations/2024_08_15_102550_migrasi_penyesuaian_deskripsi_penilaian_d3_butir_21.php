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
        $penilaianMatrikPredikats = DB::select('select * from spmi.penilaian_matriks_predikat where id_penilaian_matriks = (
            select pm.id from spmi.penilaian_matriks pm
                join spmi.penilaian_panduan pp on pm.id_penilaian_panduan = pp.id
            where pp.kode_penilaian_panduan = \'IAPS-D3\' and nomor_penilaian = \'21\' limit 1
        )');

        foreach ($penilaianMatrikPredikats as $penilaianMatrikPredikat) {
            if ($penilaianMatrikPredikat->nilai == 4) {
                DB::table('spmi.penilaian_matriks_predikat')->where('id', $penilaianMatrikPredikat->id)->update([
                    'deskripsi' => '<b>Kelompok Sains Teknologi</b>
                        <br>Jika 10 ≤ RMD ≤ 20, maka Skor = 4<br>
                        <br><b>Kelompok Sosial Humaniora</b>
                        <br>Jika 15 ≤ RMD ≤ 25, maka Skor = 4',
                ]);
            } elseif ($penilaianMatrikPredikat->nilai == 3 || $penilaianMatrikPredikat->nilai == 2 || $penilaianMatrikPredikat->nilai == 1) {
                DB::table('spmi.penilaian_matriks_predikat')->where('id', $penilaianMatrikPredikat->id)->update([
                    'deskripsi' => '<b>Kelompok Sains Teknologi</b>
                        <br>Jika RMD < 10, maka Skor = (2 x RMD) / 5
                        <br>Jika 20 < RMD ≤ 30, maka Skor = (60 - (2 x RMD)) / 5<br>
                        <br><b>Kelompok Sosial Humaniora</b>
                        <br>Jika RMD < 15, maka Skor = (4 x RMD) / 15
                        <br>Jika 25 < RMD ≤ 35, maka Skor = (70 - (2 x RMD)) / 5',
                ]);
            } elseif ($penilaianMatrikPredikat->nilai == 0) {
                DB::table('spmi.penilaian_matriks_predikat')->where('id', $penilaianMatrikPredikat->id)->update([
                    'deskripsi' => '<b>Kelompok Sains Teknologi</b>
                        <br>Jika RMD > 30, maka Skor = 0<br>
                        <br><b>Kelompok Sosial Humaniora</b>
                        <br>Jika RMD > 35, maka Skor = 0',
                ]);
            }
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
