<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Models\JenjangPendidikan;
use Modules\SPMI\Models\JenisStandar;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Services\MigrationService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        MigrationService::tarikPanduanPenilaianIAPSByJenjang('S3', 'MatriksPenilaianS3');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
