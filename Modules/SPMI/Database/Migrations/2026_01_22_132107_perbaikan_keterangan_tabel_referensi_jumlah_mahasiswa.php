<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\PengisianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pengisianPanduan = PengisianPanduan::where([
            'kode_pengisian_panduan' => 'IAPS5.1',
            'apakah_data_default' => true,
        ])->first();

        $indikatorLK = IndikatorLaporanKinerja::where('nomor_indikator', 'XREF.1')
            ->where('id_pengisian_panduan', $pengisianPanduan->id)
            ->first();

        $indikatorLK->deskripsi = "<div>Tambahkan jumlah mahasiswa aktif program studi dalam 4 tahun terakhir. Data ini akan digunakan sebagai landasan auditor untuk memberikan penilaian.</div><div>Tabel Referensi:&nbsp;1. Mahasiswa Aktif Program Studi</div>";
        $indikatorLK->deskripsi_sumber_data = "Kolom 2 - 5 diambil dari <strong>Akademik</strong> menu <strong>Perkuliahan</strong> &gt; <strong>Administrasi</strong> &gt; <strong>Pembimbing Akademik (AKM)</strong>, dengan status mahasiswa tercentang <strong>Aktif</strong><br>";
        $indikatorLK->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
