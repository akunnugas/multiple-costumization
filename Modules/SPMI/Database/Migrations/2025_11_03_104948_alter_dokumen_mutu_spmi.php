<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\SpmiJenisDokumen;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SpmiJenisDokumen::where('nama_spmi_jenis_dokumen', 'Kebijakan SPMI')->update([
            'deskripsi_singkat' => 'Dokumen kebijakan utama SPMI yang menjadi acuan seluruh kegiatan penjaminan mutu di perguruan tinggi Anda. Pastikan kebijakan ini selalu diperbarui sesuai regulasi dan kebutuhan internal',
        ]);

        SpmiJenisDokumen::where('nama_spmi_jenis_dokumen', 'Standar, kriteria, norma, acuan mutu')->update([
            'nama_spmi_jenis_dokumen' => 'Standar dan Kriteria Mutu',
            'deskripsi_singkat' => 'Kumpulan standar dan kriteria mutu berdasarkan PPEPP Standar Dikti yang menjadi acuan bagi setiap unit kerja di perguruan tinggi',
        ]);

        SpmiJenisDokumen::where('nama_spmi_jenis_dokumen', 'Tata cara pendokumentasian implementasi SPMI')->update([
            'nama_spmi_jenis_dokumen' => 'Dokumentasi Implementasi SPMI',
            'deskripsi_singkat' => 'Dokumen pelengkap yang berisi tata cara pendokumentasian pelaksanaan SPMI, termasuk formulir, catatan, atau dokumentasi digital hasil kegiatan audit mutu internal',
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
