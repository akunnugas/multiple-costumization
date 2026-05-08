<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\MappingPanduan;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pengisianPanduan = PengisianPanduan::where('kode_pengisian_panduan', 'IAPS9')->first();
        $listPenilaianPanduan = PenilaianPanduan::where('apakah_aktif', true)->get();

        foreach ($listPenilaianPanduan as $penilaian) {
            MappingPanduan::create([
                'id_pengisian_panduan' => $pengisianPanduan->id,
                'id_penilaian_panduan' => $penilaian->id,
            ]);
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
