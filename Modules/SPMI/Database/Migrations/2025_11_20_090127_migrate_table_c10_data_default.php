<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\PenilaianSkor;
use Modules\SPMI\Models\TargetSkor;
use Modules\SPMI\Models\TinjauanTemuan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $listPanduanC10 = PenilaianPanduan::join('spmi.penilaian_matriks', 'spmi.penilaian_matriks.id_penilaian_panduan', '=', 'spmi.penilaian_panduan.id')
            ->where('spmi.penilaian_matriks.nomor_penilaian', 'C.10')
            ->where('spmi.penilaian_panduan.apakah_data_default', true)
            ->select('spmi.penilaian_panduan.id')
            ->get();

        foreach ($listPanduanC10 as $panduanC10) {
            $penilaianMatriks = PenilaianMatriks::updateOrCreate(
                [
                    'id_penilaian_panduan' => $panduanC10->id,
                    'nomor_penilaian' => 'C.10',
                ],
                [
                    'id_parent' => null,
                    'kategori_penilaian' => PenilaianMatriks::CATEGORY_ELEMENT,
                    'pertanyaan_penilaian' => 'C.10. Indikator Tambahan',
                    'jenis_penilaian' => PenilaianMatriks::TYPE_FINAL_SCORE,
                    'apakah_nilai_ditampilkan' => true,
                    'apakah_data_default' => false,
                    'apakah_aktif' => 1,
                ]
            );

            TinjauanTemuan::where('id_penilaian_matriks', $penilaianMatriks->id)->delete();
            PenilaianSkor::where('id_penilaian_matriks', $penilaianMatriks->id)->delete();
            TargetSkor::where('id_penilaian_matriks', $penilaianMatriks->id)->delete();
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
