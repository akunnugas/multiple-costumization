<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Wilayah;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('kerjasama.mitra', function (SevimaBlueprint $table) {
            $table->id();

            $table->string('nama_mitra');

            $table->enum('tingkat_mitra', ['R','N','I']);

            $table->unsignedBigInteger('id_kriteria_mitra');
            $table->foreign('id_kriteria_mitra')->references('id')->on('kerjasama.kriteria_mitra');
            $table->index(['id_kriteria_mitra']);

            $table->string('website')->nullable();

            $table->foreignIdTo(Wilayah::class, 'id_negara', true);
            $table->foreignIdTo(Wilayah::class, 'id_provinsi', true);
            $table->foreignIdTo(Wilayah::class, 'id_kota', true);

            $table->string('alamat')->nullable();
            $table->string('telepon', 20)->nullable();

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
        SevimaSchema::dropIfExists('kerjasama.mitra');
    }
};
