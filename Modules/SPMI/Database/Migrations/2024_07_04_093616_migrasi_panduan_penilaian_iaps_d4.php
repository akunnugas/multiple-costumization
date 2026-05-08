<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Models\JenjangPendidikan;
use Modules\SPMI\Models\JenisStandar;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Services\MigrationService;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        MigrationService::tarikPanduanPenilaianIAPSByJenjang('D4', 'MatriksPenilaianD4');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
