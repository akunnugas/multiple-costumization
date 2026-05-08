<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\AkreditasiSyarat;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $panduanPenilaianS1 = PenilaianPanduan::where('kode_penilaian_panduan', 'IAPS-S1')->first();

        $akreditasiSyarats = AkreditasiSyarat::where('id_penilaian_panduan', $panduanPenilaianS1->id)->orderBy('id', 'ASC')->get();
        $akreditasiSyarats = $akreditasiSyarats->take(7);

        AkreditasiSyarat::where('id_penilaian_panduan', $panduanPenilaianS1->id)
            ->whereNotIn('id', $akreditasiSyarats->pluck('id')->toArray())
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
