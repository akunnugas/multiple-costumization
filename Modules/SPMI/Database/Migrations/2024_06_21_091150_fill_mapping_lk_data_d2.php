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
        //     kodeJenjang: 'D2',
        //     excludeMatrix: [
        //         '2b', '3a.5', '3b.4', '3b.4a', '3b.4b', '3b.5', '3b.6', '6', '6a', '6b', '7', '8b.2', '8c',
        //         '8c.1', '8c.2', '8c.3', '8c.4', '8d', '8d.1', '8d.1a', '8d.1b', '8d.1c', '8d.2', '8e.2.Ref'
        //     ]
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
