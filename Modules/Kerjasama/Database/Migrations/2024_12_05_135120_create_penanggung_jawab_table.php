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
        SevimaSchema::create('kerjasama.penanggung_jawab', function (SevimaBlueprint $table) {
            $table->id();

            $table->string('nama_penanggung_jawab');
            $table->string('email');
            $table->string('telepon', 20);
            $table->string('jabatan');

            $table->unsignedBigInteger('id_kerjasama');
            $table->foreign('id_kerjasama')->references('id')->on('kerjasama.kerjasama');
            $table->index(['id_kerjasama']);

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
        SevimaSchema::dropIfExists('kerjasama.penanggung_jawab');
    }
};
