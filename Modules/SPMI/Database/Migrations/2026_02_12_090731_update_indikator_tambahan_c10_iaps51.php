<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $penilaianPanduan = PenilaianPanduan::where('kode_penilaian_panduan', 'IAPS5.1-S1-Akre')->first();
        $penilaianMatriks = PenilaianMatriks::where('id_penilaian_panduan', $penilaianPanduan->id)
            ->where('nomor_penilaian', 'C.10')->first();
        if ($penilaianMatriks) {
            $penilaianMatriks->update([
                'nomor_penilaian' => 'IKT',
                'pertanyaan_penilaian' => 'Indikator Tambahan',
            ]);
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
