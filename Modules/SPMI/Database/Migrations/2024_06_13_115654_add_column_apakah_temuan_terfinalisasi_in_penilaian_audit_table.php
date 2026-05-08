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
        SevimaSchema::table('spmi.penilaian_audit', function (SevimaBlueprint $table) {
            $table->boolean('apakah_temuan_terfinalisasi')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('spmi.penilaian_audit', function (SevimaBlueprint $table) {
            $table->dropColumn('apakah_temuan_terfinalisasi');
        });
    }
};
