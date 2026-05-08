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
        SevimaSchema::table('core.jenjang_pendidikan', function (SevimaBlueprint $table) {
            $table->integer('urutan')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('core.jenjang_pendidikan', function (SevimaBlueprint $table) {
            $table->integer('urutan')->nullable(false)->change();
        });
    }
};
