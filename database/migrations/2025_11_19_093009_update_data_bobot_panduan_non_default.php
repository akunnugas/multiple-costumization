<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\IndikatorBobot;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $penilaianPanduanNonDefaul = PenilaianPanduan::where('apakah_data_default', false)->get();
        foreach ($penilaianPanduanNonDefaul as $panduan) {
            IndikatorBobot::where('id_penilaian_panduan', $panduan->id)
                ->update(['persentase' => 100]);
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
