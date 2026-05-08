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
        SevimaSchema::create('spmi.akreditasi_standar', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_standar');
            $table->string('kode_standar', 5);
            $table->unsignedBigInteger('id_jenis_standar');
            $table->foreign('id_jenis_standar')->references('id')->on('spmi.jenis_standar');

            $table->index(['id_jenis_standar']);
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
        SevimaSchema::dropIfExists('spmi.akreditasi_standar');
    }
};
