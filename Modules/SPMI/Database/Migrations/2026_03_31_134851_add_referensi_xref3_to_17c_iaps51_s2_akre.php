<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksReferensi;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pengisianPanduan = PengisianPanduan::where(['kode_pengisian_panduan' => 'IAPS5.1', 'apakah_data_default' => true])->first();
        $butirLK = IndikatorLaporanKinerja::where([
            'id_pengisian_panduan' => $pengisianPanduan->id,
            'nomor_indikator' => 'XREF.3'
        ])->first();

        $penilaianPanduan = PenilaianPanduan::where(['kode_penilaian_panduan' => 'IAPS5.1-S2-Akre', 'apakah_data_default' => true])->first();
        $matriks17C = PenilaianMatriks::where('id_penilaian_panduan', $penilaianPanduan->id)
            ->where('nomor_penilaian', 'B217C')->first();

        $referensi = [
            'id_penilaian_matriks' => $matriks17C->id,
            'id_butir_referensi' => $butirLK->id,
            'jenis_referensi' => PenilaianMatriks::REFERENCE_PERFORMANCE_REPORT,
        ];

        $existingReferensi = PenilaianMatriksReferensi::where([
            'id_penilaian_matriks' => $referensi['id_penilaian_matriks'],
            'id_butir_referensi' => $referensi['id_butir_referensi'],
        ])->first();

        if ($existingReferensi) {
            $existingReferensi->update([
                'jenis_referensi' => $referensi['jenis_referensi'],
            ]);

            return;
        }

        PenilaianMatriksReferensi::query()->insert([$referensi]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
