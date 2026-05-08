<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Mahasiswa;
use Modules\Kerjasama\Models\Kegiatan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('kerjasama.pelaksana_kegiatan', function (SevimaBlueprint $table) {
            $table->id();

            $table->foreignIdTo(Kegiatan::class, 'id_kegiatan');
            $table->foreignIdTo(Mahasiswa::class, 'id_mahasiswa');

            $table->string('program_studi')->nullable();
            $table->text('informasi_tambahan')->nullable();

            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('kerjasama.pelaksana_kegiatan');
    }
};
