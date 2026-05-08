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
        DB::table('spmi.penilaian_matriks')
            ->where('nomor_penilaian', '14.b')
            ->where('id_penilaian_panduan', function ($query) {
                $query->select('id')
                    ->from('spmi.penilaian_panduan')
                    ->where('kode_penilaian_panduan', 'IAPS-D4');
            })
            ->update(['deskripsi' => 'RASIO = JP / JD']);

        DB::table('spmi.penilaian_matriks')
            ->where('nomor_penilaian', '14.b')
            ->where('id_penilaian_panduan', function ($query) {
                $query->select('id')
                    ->from('spmi.penilaian_panduan')
                    ->where('kode_penilaian_panduan', 'IAPS-D3');
            })
            ->update(['deskripsi' => 'RASIO = JP / JD']);

        DB::table('spmi.penilaian_matriks')
            ->where('nomor_penilaian', '14')
            ->where('id_penilaian_panduan', function ($query) {
                $query->select('id')
                    ->from('spmi.penilaian_panduan')
                    ->where('kode_penilaian_panduan', 'IAPS-S1');
            })
            ->update(['deskripsi' => '<ol>
<li>Untuk program studi dengan
     jumlah kebutuhan lulusan tinggi berlaku perhitungan sebagai berikut:</li>
 <ul>
  <li>Jika Rasio &gt;= 5 ,&nbsp; maka Skor = 4</li>
  <li>Jika Rasio &lt; 5 ,&nbsp; maka Skor = (4 x Rasio) / 5</li>
 </ul>
 <li>Untuk program studi dengan
     jumlah kebutuhan lulusan rendah berlaku perhitungan sebagai berikut:</li>
 <ul>
  <li>Jika selalu ada mahasiswa
      baru terdaftar pada TS-4 s.d. TS, maka Skor = 4 </li>
  <li>Tidak ada skor antara 2
      dan 4</li>
  <li>Jika tidak selalu ada
      mahasiswa baru terdaftar pada TS-4 s.d. TS , maka Skor = 2 </li>
  <li>Tidak ada skor antara 0
      dan 2. Jika tidak ada mahasiswa baru terdaftar pada TS-4 s.d. TS , maka
      Skor = 0</li>
 </ul>
</ol><br><br>RASIO = JP / JD']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
