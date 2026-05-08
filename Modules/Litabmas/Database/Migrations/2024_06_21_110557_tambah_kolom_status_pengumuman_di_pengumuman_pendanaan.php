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
        SevimaSchema::table('litabmas.pengumuman_pendanaan', function (SevimaBlueprint $table) {
            $table->string('status_pengumuman')->default('draft')->comment('Status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.pengumuman_pendanaan', function (SevimaBlueprint $table) {
            $table->dropColumn('status_pengumuman');
        });
    }
};
