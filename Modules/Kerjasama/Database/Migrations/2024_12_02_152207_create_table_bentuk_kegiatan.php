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
        SevimaSchema::create('kerjasama.bentuk_kegiatan', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_bentuk_kegiatan');

            $table->unsignedBigInteger('id_jenis_kegiatan');
            $table->foreign('id_jenis_kegiatan')->references('id')->on('kerjasama.jenis_kegiatan');
            $table->index(['id_jenis_kegiatan']);

            $table->text('keterangan')->nullable();
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
        SevimaSchema::dropIfExists('kerjasama.bentuk_kegiatan');
    }
};
