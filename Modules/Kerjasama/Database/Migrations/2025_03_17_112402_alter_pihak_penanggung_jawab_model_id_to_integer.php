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
        SevimaSchema::table('kerjasama.pihak_penanggung_jawab', function (SevimaBlueprint $table) {
            $table->dropColumn('model_id');
        });

        SevimaSchema::table('kerjasama.pihak_penanggung_jawab', function (SevimaBlueprint $table) {
            $table->unsignedInteger('model_id');
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
