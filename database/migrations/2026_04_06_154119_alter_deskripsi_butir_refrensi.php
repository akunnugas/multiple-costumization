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
        $pengisianPanduan = PengisianPanduan::where(['kode_pengisian_panduan' => 'IAPS5.1', 'apakah_data_default' => true])->first();
        IndikatorLaporanKinerja::where([
            'id_pengisian_panduan' => $pengisianPanduan->id,
            'nomor_indikator' => 'XREF.3'
        ])->update([
            'deskripsi' => '<div>Tambahkan data penelitian dosen penghitung rasio program studi dalam 3 tahun terakhir. Data ini akan digunakan sebagai landasan auditor untuk memberikan penilaian.</div><div>Tabel Referensi:&nbsp;3. Penelitian Dosen Penghitung Rasio</div>'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
