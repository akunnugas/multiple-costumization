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
        SevimaSchema::create('pmb.tautan_terkait', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_tautan');
            $table->string('link_tautan');
            $table->integer('urutan_tautan')->nullable();
            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.tautan_terkait');
    }
};
