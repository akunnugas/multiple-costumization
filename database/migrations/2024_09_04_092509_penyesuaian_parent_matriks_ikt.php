<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\PenilaianMatriks;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();
        $count = 0;
        $listMatriksKriteria = PenilaianMatriks::where('nomor_penilaian', "C")->get();
        foreach ($listMatriksKriteria as $matriksPenilaianElemenKriteria) {
            // check if exists ikt broken
            if(DB::table('spmi.penilaian_matriks')
                ->where('id_penilaian_panduan', $matriksPenilaianElemenKriteria->id_penilaian_panduan)
                ->where('apakah_data_default', false)
                ->where('id_parent', null)
                ->exists()) {
                    $matriksPenilaianElemenTambahan = PenilaianMatriks::where('id_parent', $matriksPenilaianElemenKriteria->id)->where('nomor_penilaian', "C.10")->first();
                    if (empty($matriksPenilaianElemenTambahan)) {
                        $matriksPenilaianElemenTambahan = new PenilaianMatriks();
                        $matriksPenilaianElemenTambahan->id_penilaian_panduan = $matriksPenilaianElemenKriteria->id_penilaian_panduan;
                        $matriksPenilaianElemenTambahan->id_parent = $matriksPenilaianElemenKriteria->id;
                        $matriksPenilaianElemenTambahan->nomor_penilaian = "C.10";
                        $matriksPenilaianElemenTambahan->kategori_penilaian = "E";
                        $matriksPenilaianElemenTambahan->pertanyaan_penilaian = "C.10. Indikator Tambahan";
                        $matriksPenilaianElemenTambahan->jenis_penilaian = "SA";
                        $matriksPenilaianElemenTambahan->apakah_nilai_ditampilkan = true;
                        $matriksPenilaianElemenTambahan->apakah_data_default = false;
                        $matriksPenilaianElemenTambahan->apakah_aktif = 1;
                        $matriksPenilaianElemenTambahan->save();
                    }

                    $listInfoLeft = [];
                    $listInfoRight = [];

                    $listIktBroken = DB::table('spmi.penilaian_matriks')
                        ->where('id_penilaian_panduan', $matriksPenilaianElemenKriteria->id_penilaian_panduan)
                        ->where('apakah_data_default', false)
                        ->where('id_parent', null)
                        ->get();

                    foreach ($listIktBroken as $iktBroken) {
                        $listInfoLeft[] = $iktBroken->info_left;
                        $listInfoRight[] = $iktBroken->info_right;
                        $iktBroken->id_parent = $matriksPenilaianElemenTambahan->id;
                        DB::table('spmi.penilaian_matriks')
                            ->where('id', $iktBroken->id)
                            ->update(['id_parent' => $matriksPenilaianElemenTambahan->id]);
                        $count += 1;
                    }

                    sort($listInfoLeft);
                    rsort($listInfoRight);

                    $infoLeft = $listInfoLeft[0] ?? null;
                    $infoRight = $listInfoRight[0] ?? null;

                    if ($infoLeft) {
                        $infoLeft -= 1;
                    }

                    if ($infoRight) {
                        $infoRight += 1;
                    }

                    if ($infoLeft || $infoRight) {
                        $matriksPenilaianElemenTambahan->info_left = $infoLeft;
                        $matriksPenilaianElemenTambahan->info_right = $infoRight;
                        $matriksPenilaianElemenTambahan->save();
                    }
                }
        }
        echo "Total data IKT broken yang diupdate: $count\n";

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
