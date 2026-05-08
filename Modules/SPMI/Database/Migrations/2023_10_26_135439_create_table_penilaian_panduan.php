<?php

use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Extensions\SevimaBlueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Core\Models\JenisPerguruanTinggi;
use Modules\Core\Models\JenjangPendidikan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('spmi.penilaian_panduan', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_penilaian_panduan', 15);
            $table->string('nama_penilaian_panduan');
            $table->unsignedBigInteger('id_laporan_kinerja');
            $table->unsignedBigInteger('id_panduan_evaluasi_diri')->nullable();
            $table->string('nama_singkat');
            $table->date('tanggal_edisi')->nullable();
            $table->foreignIdTo(JenjangPendidikan::class);
            $table->unsignedBigInteger('id_jenis_standar');
            $table->foreignIdTo(JenisPerguruanTinggi::class, nullable: true);
            $table->boolean('apakah_ptn')->default(false);
            $table->text('deskripsi')->nullable();
            $table->bigInteger("id_dokumen")->nullable();
            $table->bigInteger("id_tipe")->nullable();
            $table->boolean('apakah_aktif')->default(true);
            $table->boolean('dapat_lihat_skor_akhir')->default(false)->comment('is view final score');
            $table->integer('total_indikator_matriks')->nullable()
                ->after('description')
                ->comment('Total Matriks Indikator');

            // create foreign key
            $table->foreign('id_dokumen')->references('id')->on('dms.dokumen');
            $table->foreign('id_laporan_kinerja')->references('id')->on('spmi.pengisian_panduan');
            $table->foreign('id_panduan_evaluasi_diri')->references('id')->on('spmi.pengisian_panduan');
            $table->foreign('id_jenis_standar')->references('id')->on('spmi.jenis_standar');

            // create index
            $table->index(['id_dokumen', 'id_tipe', 'id_laporan_kinerja', 'id_panduan_evaluasi_diri']);

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
        SevimaSchema::dropIfExists('spmi.penilaian_panduan');
    }
};
