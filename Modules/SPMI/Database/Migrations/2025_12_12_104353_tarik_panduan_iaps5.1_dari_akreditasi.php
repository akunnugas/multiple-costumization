<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Services\AccreditationSyncManagementService;
use Modules\SPMI\Services\MigrationService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        MigrationService::tarikPanduanPengisian("BANPT", "LED5.1");
        MigrationService::tarikPanduanPengisian("BANPT", "IAPS5.1");
        PengisianPanduan::where([
            'kode_pengisian_panduan' => 'LED5.1', 
            'kode_pengisian_panduan' => 'IAPS5.1',
            'apakah_data_default' => true
        ])->update(['apakah_aktif' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
