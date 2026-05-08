<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Helpers\Error;
use Modules\SPMI\Data\PenilaianMatriks\IAPSS1;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $migrateFormulaIAPSS1 = (new IAPSS1)->startMigrate();

        if (Error::isError($migrateFormulaIAPSS1)) {
            throw new \Exception($migrateFormulaIAPSS1->message);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
