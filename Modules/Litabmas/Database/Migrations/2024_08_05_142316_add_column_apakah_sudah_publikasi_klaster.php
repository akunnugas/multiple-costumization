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
            $table->boolean('apakah_sudah_publikasi')->default(false)->comment('Apakah klaster sudah dipublikasi?');
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
            $table->dropColumn('apakah_sudah_publikasi');
        });
    }
};
