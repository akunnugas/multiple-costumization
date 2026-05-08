<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.bahasa', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_bahasa', 3);
            $table->string('nama_bahasa');
            $table->logs(true);
        });

        SevimaSchema::table('core.bahasa', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_bahasa', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.bahasa');
    }
};
