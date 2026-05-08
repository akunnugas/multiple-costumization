<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $listPenilaianPanduan = PenilaianPanduan::where('apakah_data_default', true)->get();

        foreach ($listPenilaianPanduan as $penilaianPanduan) {
            $c10 = PenilaianMatriks::where('id_penilaian_panduan', $penilaianPanduan->id)
                ->where('nomor_penilaian', 'C.10')
                ->first();

            $matriksIKT = PenilaianMatriks::where('id_penilaian_panduan', $penilaianPanduan->id)
                ->where('apakah_data_default', false);

            if ($c10) {
                $matriksIKT->where('id', '!=', $c10->id)->update(['id_parent' => $c10->id]);
            } elseif ($matriksIKT->exists()) {
                $c10 = PenilaianMatriks::create([
                    'id_penilaian_panduan' => $penilaianPanduan->id,
                    'id_parent' => null,
                    'nomor_penilaian' => 'C.10',
                    'kategori_penilaian' => PenilaianMatriks::CATEGORY_ELEMENT,
                    'pertanyaan_penilaian' => 'C.10. Indikator Tambahan',
                    'jenis_penilaian' => PenilaianMatriks::TYPE_FINAL_SCORE,
                    'apakah_nilai_ditampilkan' => true,
                    'apakah_data_default' => false,
                    'apakah_aktif' => 1,
                ]);

                $matriksIKT->update(['id_parent' => $c10->id]);
            }
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
