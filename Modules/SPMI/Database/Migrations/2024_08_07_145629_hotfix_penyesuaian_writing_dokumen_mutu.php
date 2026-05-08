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
            'nama_spmi_jenis_dokumen' => 'Kebijakan SPMI (Tetap)',
        ]);

        SpmiJenisDokumen::where('nama_spmi_jenis_dokumen', 'Manual SPMI')->update([
            'nama_spmi_jenis_dokumen' => 'Pedoman Penerapan Siklus PPEPP Standar Dikti',
        ]);

        SpmiJenisDokumen::where('nama_spmi_jenis_dokumen', 'Standar SPMI')->update([
            'nama_spmi_jenis_dokumen' => 'Standar, kriteria, norma, acuan mutu',
        ]);

        SpmiJenisDokumen::where('nama_spmi_jenis_dokumen', 'Formulir SPMI')->update([
            'nama_spmi_jenis_dokumen' => 'Tata cara pendokumentasian implementasi SPMI',
            'deskripsi_singkat' => 'Tambahkan Formulir, Catatan, Dokumen, Video, atau Rekaman digital lain, agar dapat merekam dan mengelola informasi dengan mudah.'
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
