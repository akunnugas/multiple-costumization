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
        PenilaianMatriks::where('kategori_penilaian', PenilaianMatriks::CATEGORY_ELEMENT)
            ->where('id_penilaian_panduan', $penilaianPanduan->id)
            ->update([
                'jenis_penilaian' => PenilaianMatriks::TYPE_FINAL_SCORE,
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
