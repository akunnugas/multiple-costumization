<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Helpers\AccreditationSync;
use Modules\SPMI\Services\AccreditationSyncManagementService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        AccreditationSyncManagementService::syncButirLEDPanduanNew("LED5.1");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
