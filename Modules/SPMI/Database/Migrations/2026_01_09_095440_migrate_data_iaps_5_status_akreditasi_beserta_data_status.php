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
        $penilaianPanduan->update([
            'apakah_menggunakan_peringkat' => false,
        ]);

        AkreditasiStatus::create([
            'kode_status' => AkreditasiStatus::CODE_TIDAK_TERAKREDITASI,
            'nama_status' => AkreditasiStatus::CODES[AkreditasiStatus::CODE_TIDAK_TERAKREDITASI],
            'nilai_minimal' => 0,
            'nilai_maksimal' => 51,
            'deskripsi' => null,
            'id_penilaian_panduan' => $penilaianPanduan->id,
        ]);

        AkreditasiStatus::create([
            'kode_status' => AkreditasiStatus::CODE_TERAKREDITASI,
            'nama_status' => AkreditasiStatus::CODES[AkreditasiStatus::CODE_TERAKREDITASI],
            'nilai_minimal' => 51,
            'nilai_maksimal' => 63,
            'deskripsi' => null,
            'id_penilaian_panduan' => $penilaianPanduan->id,
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
