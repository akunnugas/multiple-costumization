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
        $penilaianPanduan = PenilaianPanduan::where(['apakah_data_default' => true, 'kode_penilaian_panduan' => 'IAPS5.1-S1-Ung'])->first();

        PenilaianMatriks::where(['id_penilaian_panduan' => $penilaianPanduan->id, 'nomor_penilaian' => 'B106B'])
            ->update(['deskripsi' => '<div>DPRPS = Jumlah dosen DPR yang relevan dengan MK diampu</div><div>DPRD Relevan = Jumlah dosen DPR Doktor yang relevan dengan MK diampu</div><div>Persentase Doktor = (DPRD / DPRPS) x 100%</div>']);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
