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
        SevimaSchema::table('litabmas.klaster_pendanaan', function (SevimaBlueprint $table) {
            // change column type maksimal_anggaran from float to decimal 15,2
            $table->decimal('maksimal_anggaran', 15, 2)->change();
        });
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.klaster_pendanaan', function (SevimaBlueprint $table) {
            $table->float('maksimal_anggaran')->change();
        });
    }
};
