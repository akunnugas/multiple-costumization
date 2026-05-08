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
        $butirLK = IndikatorLaporanKinerja::where('id_pengisian_panduan', $pengisianPanduan->id)
            ->whereIn('nomor_indikator', ['1.1.a.1', '1.2.2', 'XREF.1', 'XREF.3'])->get();

        $indikatorLK = array_combine($butirLK->pluck('nomor_indikator')->toArray(), $butirLK->pluck('id')->toArray());

        $penilaianPanduan = PenilaianPanduan::where(['kode_penilaian_panduan' => 'IAPS5.1-S2-Ung', 'apakah_data_default' => true])->first();
        $matriksPenilaian = PenilaianMatriks::where('id_penilaian_panduan', $penilaianPanduan->id)
            ->whereIn('nomor_penilaian', ['B106D', 'B108C', 'B112A', 'B115', 'B217C', 'B219B', 'B323A', 'B323B'])->get();

        $indikatorMatriks = array_combine($matriksPenilaian->pluck('nomor_penilaian')->toArray(), $matriksPenilaian->pluck('id')->toArray());

        $mapping = [
            'B106D' => '1.1.a.1',
            'B108C' => 'XREF.1',
            'B112A' => '1.2.2',
            'B115' => '1.1.a.1',
            'B217C' => 'XREF.3',
            'B219B' => '1.1.a.1',
            'B323A' => '1.1.a.1',
            'B323B' => '1.1.a.1',
        ];

        $matriksReferensi = [];
        foreach ($mapping as $matriks => $indikator) {
            $matriksReferensi[] = [
                'id_penilaian_matriks' => $indikatorMatriks[$matriks],
                'id_butir_referensi' => $indikatorLK[$indikator],
                'jenis_referensi' => PenilaianMatriks::REFERENCE_PERFORMANCE_REPORT,
            ];
        }

        foreach ($matriksReferensi as $referensi) {
            $existingReferensi = PenilaianMatriksReferensi::where([
                'id_penilaian_matriks' => $referensi['id_penilaian_matriks'],
                'id_butir_referensi' => $referensi['id_butir_referensi'],
            ])->first();

            if ($existingReferensi) {
                $existingReferensi->update([
                    'jenis_referensi' => $referensi['jenis_referensi'],
                ]);
                continue;
            }

            PenilaianMatriksReferensi::query()->insert([$referensi]);
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
