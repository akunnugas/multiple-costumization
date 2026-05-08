<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('core.unit_kerja', function (SevimaBlueprint $table) {
            if (!SevimaSchema::hasColumn('core.unit_kerja', 'apakah_data_default')) {
                $table->boolean('apakah_data_default')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('core.unit_kerja', function (SevimaBlueprint $table) {
            $table->dropColumn('apakah_data_default');
        });
    }
};
