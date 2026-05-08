<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Kerjasama\Models\PihakPenanggungJawab;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('kerjasama.penanggung_jawab', function (SevimaBlueprint $table) {
            $table->foreignIdTo(PihakPenanggungJawab::class);
        });
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('kerjasama.penanggung_jawab', function (SevimaBlueprint $table) {
            $table->dropForeignIdFor(PihakPenanggungJawab::class);
        });
    }
};
