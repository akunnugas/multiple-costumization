<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $panduanPenilaian = PenilaianPanduan::where('kode_penilaian_panduan', 'IAPS-S1')->first('id');

        if (empty($panduanPenilaian)) {
            return;
        }

        DB::beginTransaction();

        $matriksPenilaianNo57 = PenilaianMatriks::where('id_penilaian_panduan', $panduanPenilaian->id)
            ->where('nomor_penilaian', '57')->first();

        $matriksPenilaianNo57->update([
            'deskripsi' => 
                "PTW = Persentase kelulusan tepat waktu (NL / ND).\nND = Mahasiswa Diterima\nNL = Mahasiswa Lulus"
        ]);

        $matriksPenilaianNo29 = PenilaianMatriks::where('id_penilaian_panduan', $panduanPenilaian->id)
            ->where('nomor_penilaian', '29')->first();

        $matriksPenilaianNo29->update([
            'deskripsi' => 
                "RLP = (2 x (NA + NB + NC) + ND) / NDTPS\nNA = Jumlah luaran penelitian/PkM yang mendapat pengakuan HKI (Paten, Paten Sederhana)\nNB = Jumlah luaran penelitian/PkM yang mendapat pengakuan HKI (Hak Cipta, Desain Produk Industri, Perlindungan Varietas Tanaman, Desain Tata Letak Sirkuit Terpadu, dll.)\nNC = Jumlah luaran penelitian/PkM dalam bentuk Teknologi Tepat Guna, Produk (Produk Terstandarisasi, Produk Tersertifikasi), Karya Seni, Rekayasa Sosial.\nND = Jumlah luaran penelitian/PkM yang diterbitkan dalam bentuk Buku ber-ISBN, Book Chapter.\nNDTPS = Jumlah dosen tetap yang ditugaskan sebagai pengampu mata kuliah dengan bidang keahlian yang sesuai dengan kompetensi inti program studi yang diakreditasi."
        ]);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
