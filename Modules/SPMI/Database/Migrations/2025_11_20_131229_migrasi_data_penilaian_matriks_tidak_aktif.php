<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\MappingPenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriks;
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
        $listMatriksNonAktif = PenilaianMatriks::where('apakah_aktif', false)->pluck('id');
        $mappinganNonAktif = MappingPenilaianMatriks::whereIn('id_penilaian_matriks', $listMatriksNonAktif)->pluck('id');

        TinjauanTemuan::whereIn('id_penilaian_matriks', $listMatriksNonAktif)->delete();
        PenilaianSkor::whereIn('id_penilaian_matriks', $listMatriksNonAktif)->delete();
        TargetSkor::whereIn('id_penilaian_matriks', $listMatriksNonAktif)->delete();

        MappingPenilaianMatriks::whereIn('id', $mappinganNonAktif)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
