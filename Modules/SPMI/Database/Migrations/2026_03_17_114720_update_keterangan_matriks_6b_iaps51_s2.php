<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $penilaianPanduan = PenilaianPanduan::where('apakah_data_default', true)
            ->whereIn('kode_penilaian_panduan', [
                'IAPS5.1-S2-Akre',
                'IAPS5.1-S2-Ung',
            ])
            ->pluck('id');

        PenilaianMatriks::whereIn('id_penilaian_panduan', $penilaianPanduan)
            ->where('nomor_penilaian', 'B106B')
            ->update(['deskripsi' => 'DPRD = Dosen Penghitung Rasio Doktor']);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
