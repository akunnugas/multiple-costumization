<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\AkreditasiStatus;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $penilaianPanduan = PenilaianPanduan::where('kode_penilaian_panduan', 'IAPS5.1-S1-Akre')->first();

        $akreditasiStatusTerakreditasi = AkreditasiStatus::where('id_penilaian_panduan', $penilaianPanduan->id)
            ->where('kode_status', AkreditasiStatus::CODE_TERAKREDITASI)->first();

        if ($akreditasiStatusTerakreditasi) {
            $akreditasiStatusTerakreditasi->update([
                'nilai_minimal' => 51,
            ]);
        }

        $tidakTerakreditasi = AkreditasiStatus::where('id_penilaian_panduan', $penilaianPanduan->id)
            ->where('kode_status', AkreditasiStatus::CODE_TIDAK_TERAKREDITASI)->first();

        if ($tidakTerakreditasi) {
            $tidakTerakreditasi->update([
                'nilai_maksimal' => 50,
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
