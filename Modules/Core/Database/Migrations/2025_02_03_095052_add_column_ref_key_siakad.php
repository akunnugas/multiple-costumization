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
        SevimaSchema::table('core.wilayah', function (SevimaBlueprint $table) {
            $table->string('ref_key_siakad');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('core.wilayah', function (SevimaBlueprint $table) {
            $table->dropColumn('ref_key_siakad');
        });
    }
};
