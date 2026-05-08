<?php

use Illuminate\Database\Migrations\Migration;
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
        //     excludeMatrix: ['8c.2', '8c.3', '8c.4', '8d.1b', '8d.1c']
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
