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
            $table->dropColumn('maksimal_pendaftar');
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
            $table->unsignedTinyInteger('maksimal_pendaftar')->nullable()->comment('Maksimal Pendaftar');
        });
    }
};
