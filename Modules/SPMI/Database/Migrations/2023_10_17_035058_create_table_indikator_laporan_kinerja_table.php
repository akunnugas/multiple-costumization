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
        SevimaSchema::create('spmi.indikator_laporan_kinerja', function (SevimaBlueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pengisian_panduan');
            $table->string('nomor_indikator');
            $table->text('nama_indikator_laporan_kinerja');
            $table->text('deskripsi')->nullable();
            $table->text('informasi')->nullable();

            // form type
            $table->enum('jenis_form', ['FR','FC'])->comment('Form Row | Form Column')->nullable();
            $table->boolean('apakah_layout_fixed')->nullable();
            $table->boolean('apakah_menggunakan_kategori')->nullable();
            $table->boolean('apakah_memasukkan_kategori_manual')->nullable();
            $table->boolean('apakah_menggunakan_ts')->nullable()->comment('TS = Tahun studi');
            $table->enum('jenis_layout', ['L','P'])->comment('L = Landscape | P = Portrait')->nullable();
            $table->enum('sumber_data', ['IN','AK','SDM','SA','AC'])->comment('sumber data')->nullable();
            $table->text('deskripsi_sumber_data')->nullable();
            $table->boolean('apakah_data_default')->default(false);
            $table->boolean('apakah_subfooter')->default(false);

            // settings form
            $table->boolean('apakah_import_excel')->nullable()->default(false);
            $table->boolean('dapat_dilihat_pada_laporan')->default(true);
            $table->boolean('dapat_lihat_nama_pada_laporan')->default(true);
            $table->boolean('apakah_parent')->default(false);
            $table->boolean('apakah_aktif')->default(true);

            // create tree struckture
            $table->unsignedBigInteger('id_parent')->nullable();
            $table->unsignedInteger('info_level')->nullable();
            $table->integer('info_left')->nullable();
            $table->integer('info_right')->nullable();

            // foreign key
            $table->foreign('id_pengisian_panduan')->references('id')->on('spmi.pengisian_panduan');

            $table->logs(true);

            // create index
            $table->index(['id_parent','info_left','info_right']);
        });

        SevimaSchema::table('spmi.indikator_laporan_kinerja', function (SevimaBlueprint $table) {
            $table->foreign('id_parent')->references('id')->on('spmi.indikator_laporan_kinerja');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('spmi.indikator_laporan_kinerja');
    }
};
