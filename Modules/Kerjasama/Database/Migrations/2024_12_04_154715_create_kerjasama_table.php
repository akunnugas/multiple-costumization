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
        SevimaSchema::create('kerjasama.kerjasama', function (SevimaBlueprint $table) {
            $table->id();

            $table->text('judul_kerjasama');


            $table->unsignedBigInteger('id_mitra');
            $table->foreign('id_mitra')->references('id')->on('kerjasama.mitra');
            $table->index(['id_mitra']);

            $table->unsignedBigInteger('id_unit_kerja');
            $table->foreign('id_unit_kerja')->references('id')->on('core.unit_kerja');
            $table->index(['id_unit_kerja']);

            $table->text('deskripsi');

            $table->date('tanggal_mulai_berlaku');
            $table->date('tanggal_akhir_berlaku')->nullable();


            $table->unsignedBigInteger('id_jenis_dokumen');
            $table->foreign('id_jenis_dokumen')->references('id')->on('kerjasama.jenis_dokumen');
            $table->index(['id_jenis_dokumen']);

            $table->string('nomor_dokumen')->nullable();

            $table->unsignedBigInteger('id_dokumen')->nullable();
            $table->foreign('id_dokumen')->references('id')->on('dms.dokumen');

            $table->unsignedBigInteger('id_status_kerjasama');
            $table->foreign('id_status_kerjasama')->references('id')->on('kerjasama.status_kerjasama');
            $table->index(['id_status_kerjasama']);

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
        SevimaSchema::dropIfExists('kerjasama.kerjasama');
    }
};
