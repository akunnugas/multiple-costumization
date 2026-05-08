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
        SevimaSchema::table('kerjasama.kriteria_mitra', function (SevimaBlueprint $tabble) {
            $tabble->boolean('isian_default')->default(false);
            $tabble->float('bobot')->nullable();
        });
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('kerjasama.kriteria_mitra', function (SevimaBlueprint $tabble) {
            $tabble->dropColumn('isian_default');
            $tabble->dropColumn('bobot');
        });
    }
};
