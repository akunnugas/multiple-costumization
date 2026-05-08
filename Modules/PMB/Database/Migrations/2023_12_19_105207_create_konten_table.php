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
        SevimaSchema::create('pmb.konten', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('judul_konten')->nullable();
            $table->text('isi_konten')->nullable();
            $table->string('informasi_tambahan')->nullable();
            $table->string('jenis_konten', 10)->comment('alumni, fasilitas, event');
            $table->unsignedBigInteger('id_file_gambar')->nullable();
            $table->logs(true);
            $table->foreign('id_file_gambar')->references('id')->on('dms.dokumen');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.konten');
    }
};
