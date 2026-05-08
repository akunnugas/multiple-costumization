<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\PengisianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pengisianPanduanIAPS51 = PengisianPanduan::where([
            "kode_pengisian_panduan" => "IAPS5.1",
            "apakah_data_default" => true
        ])->first();

        $idsParentLK = IndikatorLaporanKinerja::where('id_pengisian_panduan', $pengisianPanduanIAPS51->id)
            ->whereNotNull('id_parent')
            ->distinct()
            ->pluck('id_parent')
            ->toArray();
        IndikatorLaporanKinerja::whereIn('id', $idsParentLK)
            ->update(['apakah_parent' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
