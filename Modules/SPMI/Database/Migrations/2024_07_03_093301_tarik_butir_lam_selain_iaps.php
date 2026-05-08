<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Services\AccreditationSyncManagementService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        AccreditationSyncManagementService::syncButirPanduanNew('LKSAMA');
        AccreditationSyncManagementService::syncButirPanduanNew('EMBA9S');
        AccreditationSyncManagementService::syncButirPanduanNew('LKTEKNI');
        AccreditationSyncManagementService::syncButirPanduanNew('LAMDIK');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
