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
        SevimaSchema::table('kerjasama.mitra', function(SevimaBlueprint $table) {
            $table->string('jenis_mitra')->nullable();
        });
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('kerjasama.mitra', function(SevimaBlueprint $table) {
            $table->dropColumn('jenis_mitra');
        });
    }
};
