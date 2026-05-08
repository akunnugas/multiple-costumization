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
        // MigrationService::migrationMappingLK(
        //     kodePengisian: 'IAPS9',
        //     kodeJenjang: 'S3',
        //     excludeMatrix: ['3a.5', '3b.4b', '3b.6', '7', '8b.2', '8c.1', '8c.2', '8c.3', '8d', '8d.1', '8d.1a', '8d.1b', '8d.1c', '8d.2', '8e', '8e.1', '8e.2.Ref', '8e.2', '8f.1b', '8f.3']
        // );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
