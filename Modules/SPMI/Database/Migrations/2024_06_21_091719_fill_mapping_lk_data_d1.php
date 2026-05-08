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
        //     kodeJenjang: 'D1',
        //     excludeMatrix: ['3a.5', '3b.4b', '3b.6', '6b', '8c.1', '8c.3', '8c.4', '8d.1a', '8d.1c', '8f.1b', '8f.2']
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
