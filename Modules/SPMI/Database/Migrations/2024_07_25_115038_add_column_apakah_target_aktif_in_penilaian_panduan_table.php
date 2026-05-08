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
        SevimaSchema::table('spmi.penilaian_panduan', function (SevimaBlueprint $table) {
            $table->boolean('apakah_target_aktif')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('spmi.penilaian_panduan', function (SevimaBlueprint $table) {
            $table->boolean('apakah_target_aktif')->default(true);
        });
    }
};
