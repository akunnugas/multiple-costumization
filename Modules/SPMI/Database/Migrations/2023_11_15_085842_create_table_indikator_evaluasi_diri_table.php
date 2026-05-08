<?php

use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Extensions\SevimaBlueprint;
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
        SevimaSchema::create('spmi.indikator_evaluasi_diri', function (SevimaBlueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pengisian_panduan')->comment('ID Panduan Pengisian');
            $table->string('nomor_indikator')->comment('Nomor Indikator');
            $table->text('nama_indikator_evaluasi_diri')->comment('Nama Indikator');
            $table->text('deskripsi')->nullable()->comment('Deskripsi Indikator');

            $table->boolean('apakah_komentar')->default(false)->comment('Komentar');
            $table->boolean('apakah_key_point')->default(false)->comment('Key Point');
            $table->boolean('apakah_aktif')->default(true)->comment('Aktif');
            $table->boolean('apakah_data_default')->default(false);
            $table->boolean('apakah_parent')->default(false);

            // create tree struckture
            $table->unsignedBigInteger('id_parent')->nullable();
            $table->unsignedInteger('info_level')->nullable();
            $table->integer('info_left')->nullable();
            $table->integer('info_right')->nullable();

            // create foreign key
            $table->foreign('id_pengisian_panduan')->references('id')->on('spmi.pengisian_panduan');

            $table->logs(true);

            // create index
            $table->index(['id_parent','info_left','info_right']);
        });

        SevimaSchema::table('spmi.indikator_evaluasi_diri', function (SevimaBlueprint $table) {
            $table->foreign('id_parent')->references('id')->on('spmi.indikator_evaluasi_diri');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('spmi.indikator_evaluasi_diri');
    }
};
