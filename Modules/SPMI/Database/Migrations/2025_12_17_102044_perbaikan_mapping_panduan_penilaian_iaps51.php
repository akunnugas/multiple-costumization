<?php

use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\MappingPanduan;
use Illuminate\Database\Schema\Blueprint;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianPanduan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $panduanPengisianIAPS = PengisianPanduan::where([
            "kode_pengisian_panduan" => "IAPS5.1",
            "apakah_data_default" => true
        ])->first();

        $penilaianPanduanIAPS51 = PenilaianPanduan::where([
            "kode_penilaian_panduan" => 'IAPS5.1-S1-Akre',
            "apakah_data_default" => true
        ])->first();
        
        MappingPanduan::create([
            "id_pengisian_panduan" => $panduanPengisianIAPS->id,
            "id_penilaian_panduan" => $penilaianPanduanIAPS51->id
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
