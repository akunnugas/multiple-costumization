<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('litabmas.sumber_pendanaan', function (SevimaBlueprint $table) {
            //sisa anggaran default value if null is using total_pendanaan
            $table->decimal('sisa_anggaran', 20, 2)->nullable(); //kalo null, maka sisa anggaran = total_pendanaan, kalo 0, maka sisa anggaran = 0
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
            $table->dropColumn('sisa_anggaran');
        });
    }
};
