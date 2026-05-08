<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $panduanPenilaian = PenilaianPanduan::where('kode_penilaian_panduan', 'IAPS-D3')->first();

        if ($panduanPenilaian) {
            DB::beginTransaction();

            // Update parent matriks penilaian butir nomor C.10 jika ada
            $matriksPenilaianElemenKriteria = PenilaianMatriks::where('id_penilaian_panduan', $panduanPenilaian->id)
                ->where('nomor_penilaian', "C")->first();
            $matriksPenilaianElemenTambahan = PenilaianMatriks::where('id_penilaian_panduan', $panduanPenilaian->id)
                ->where('nomor_penilaian', "C.10")->first();

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
                $matriksPenilaianElemenTambahan->id_penilaian_panduan = $panduanPenilaian->id;
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
