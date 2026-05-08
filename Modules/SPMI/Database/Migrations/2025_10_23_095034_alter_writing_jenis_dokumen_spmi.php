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
        SpmiJenisDokumen::where('nama_spmi_jenis_dokumen', 'Kebijakan SPMI (Tetap)')->update([
            'nama_spmi_jenis_dokumen' => 'Kebijakan SPMI',
            'deskripsi_singkat' => 'Buatlah panduan kebijakan SPMI, sehingga semua pihak yang terlibat proses SPMI mudah untuk memahami, merancang, dan mengimplementasikan SPMI secara efektif',
        ]);

        SpmiJenisDokumen::where('nama_spmi_jenis_dokumen', 'Pedoman Penerapan Siklus PPEPP Standar Dikti')->update([
            'nama_spmi_jenis_dokumen' => 'Manual/Pedoman Implementasi SPMI (Siklus PPEPP)',
            'deskripsi_singkat' => 'Pedoman penerapan siklus PPEPP (Penetapan, Pelaksanaan, Evaluasi, Pengendalian, dan Peningkatan) sesuai Standar Dikti untuk menjamin pelaksanaan SPMI berjalan sistematis dan terukur.',
        ]);

        SpmiJenisDokumen::where('nama_spmi_jenis_dokumen', 'Standar, Kriteria, Norma, Acuan Mutu')->update([
            'nama_spmi_jenis_dokumen' => 'Standar dan Kriteria Mutu',
            'deskripsi_singkat' => 'Kumpulan standar dan kriteria mutu berdasarkan PPEPP Standar Dikti yang menjadi acuan bagi setiap unit kerja di perguruan tinggi.',
        ]);

        SpmiJenisDokumen::where('nama_spmi_jenis_dokumen', 'Tata cara pendokumentasian implementasi SPMI')->update([
            'nama_spmi_jenis_dokumen' => 'Formulir/Tata cara pendokumentasian implementasi SPMI',
            'deskripsi_singkat' => 'Dokumen pelengkap yang berisi tata cara pendokumentasian pelaksanaan SPMI, termasuk formulir, catatan, atau dokumentasi digital hasil kegiatan audit mutu internal.',
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
