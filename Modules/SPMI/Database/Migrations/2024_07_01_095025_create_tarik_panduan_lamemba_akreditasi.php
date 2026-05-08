<?php

use Illuminate\Database\Migrations\Migration;
use Modules\SPMI\Services\MigrationService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        MigrationService::tarikPanduanAkreditasidanLED('LAMEMBA', 'DEDEMBA');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
