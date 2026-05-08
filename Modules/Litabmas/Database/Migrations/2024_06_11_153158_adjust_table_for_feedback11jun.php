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
        //set null apakah_lolos_nominasi on table pengajuan_pendanaan
        SevimaSchema::table('litabmas.pengajuan_pendanaan', function (SevimaBlueprint $table) {
            $table->boolean('apakah_lolos_nominasi')->nullable()->change();
        });
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
