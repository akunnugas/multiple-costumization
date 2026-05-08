<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\JenjangPendidikan;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\MappingLK;
use Modules\SPMI\Models\PengisianPanduan;
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
        // MigrationService::migrationMappingLK(
        //     kodePengisian: 'IAPS9',
        //     kodeJenjang: 'D3',
        //     excludeMatrix: ['8c.2', '8c.3', '8c.4', '8d.1b', '8d.1c', '8d.2', '8e.2.Ref']
        // );
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
