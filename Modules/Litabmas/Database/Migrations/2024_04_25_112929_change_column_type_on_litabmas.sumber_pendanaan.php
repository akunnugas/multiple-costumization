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
        SevimaSchema::table('litabmas.sumber_pendanaan', function (SevimaBlueprint $table) {
            $table->decimal('total_pendanaan', 15, 2)->change();
        });
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.sumber_pendanaan', function (SevimaBlueprint $table) {
            $table->float('total_pendanaan')->change();
        });
    }
};
