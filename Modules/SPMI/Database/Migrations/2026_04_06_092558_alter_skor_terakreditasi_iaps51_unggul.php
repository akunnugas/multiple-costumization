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
        $idPenilaianPanduanIAPS51S1Unggul = PenilaianPanduan::where('kode_penilaian_panduan', 'IAPS5.1-S1-Ung')
            ->where('apakah_data_default', true)
            ->first()
            ->id;
        $idPenilaianPanduanIAPS51S2Unggul = PenilaianPanduan::where('kode_penilaian_panduan', 'IAPS5.1-S2-Ung')
            ->where('apakah_data_default', true)
            ->first()
            ->id;

        // IAPS 5.1 S1 Unggul
        AkreditasiStatus::where('id_penilaian_panduan', $idPenilaianPanduanIAPS51S1Unggul)
            ->where('kode_status', 'M')
            ->update([
                'nama_status' => 'Terakreditasi Unggul',
            ]);
        AkreditasiStatus::where('id_penilaian_panduan', $idPenilaianPanduanIAPS51S1Unggul)
            ->where('kode_status', 'TM')
            ->update([
                'nama_status' => 'Terakreditasi',
            ]);

        // IAPS 5.1 S2 Unggul
        AkreditasiStatus::where('id_penilaian_panduan', $idPenilaianPanduanIAPS51S2Unggul)
            ->where('kode_status', 'M')
            ->update([
                'nama_status' => 'Terakreditasi Unggul',
            ]);
        AkreditasiStatus::where('id_penilaian_panduan', $idPenilaianPanduanIAPS51S2Unggul)
            ->where('kode_status', 'TM')
            ->update([
                'nama_status' => 'Terakreditasi',
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
