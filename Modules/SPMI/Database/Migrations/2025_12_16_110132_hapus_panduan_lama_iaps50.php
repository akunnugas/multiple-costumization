<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        PengisianPanduan::whereIn('kode_pengisian_panduan', ['IAPS5.0', 'IAPSLED'])
            ->where('apakah_data_default', true)->delete();
        PenilaianPanduan::where(['kode_penilaian_panduan' => 'IAPS5-Akre-S1', 'apakah_data_default' => true])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
