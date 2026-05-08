<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{

    protected const TABLES = [
        'kerjasama.sasaran_kinerja',
        'kerjasama.indikator_sasaran'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (self::TABLES as $tableName) {
            SevimaSchema::table($tableName, function (SevimaBlueprint $table) {
                $table->boolean('isian_default')->default(false);
            });
        }
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (self::TABLES as $tableName) {
            SevimaSchema::table($tableName, function (SevimaBlueprint $table) {
                $table->dropColumn('isian_default');
            });
        }
    }
};
