<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksPredikat;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $configPanduan = [
            "LAMDIK-S1-3.0",
            "INFOKOM2.1S1",
            "LAMTEKNIKS1-25",
        ];

        foreach ($configPanduan as $kodePengisianPanduan) {
            $penilaianPanduan = PenilaianPanduan::where('kode_penilaian_panduan', $kodePengisianPanduan)
                ->where('apakah_data_default', true)
                ->first();
            $idsPenilaianMatriks = PenilaianMatriks::where('id_penilaian_panduan', $penilaianPanduan->id)
                ->pluck('id')
                ->toArray();
            PenilaianMatriksPredikat::whereIn('id_penilaian_matriks', $idsPenilaianMatriks)
                ->update(['apakah_nonaktif' => false]);
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
