<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\PenilaianMatriks;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();
        // Update parent matriks penilaian butir nomor C.10 jika ada
        $matriksPenilaianElemenKriteria = PenilaianMatriks::where('nomor_penilaian', "C")->first();
        $matriksPenilaianElemenTambahan = PenilaianMatriks::where('nomor_penilaian', "C.10")->first();

        if (empty($matriksPenilaianElemenKriteria) || empty($matriksPenilaianElemenTambahan)) {
            DB::rollback();
            return;
        }

        if ($matriksPenilaianElemenKriteria && $matriksPenilaianElemenTambahan) {
            $matriksPenilaianElemenTambahan->id_parent = $matriksPenilaianElemenKriteria->id;
            $matriksPenilaianElemenTambahan->save();
        }

        // Jika tidak ada, buat matriks penilaian butir nomor C.10
        if (!$matriksPenilaianElemenTambahan) {
            $matriksPenilaianElemenTambahan = new PenilaianMatriks();
            $matriksPenilaianElemenTambahan->id_penilaian_panduan = 1;
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

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
