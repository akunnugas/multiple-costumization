<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\LembagaAkreditasi;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('spmi.pengisian_panduan', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_pengisian_panduan');
            $table->string('kode_pengisian_panduan', 10);
            $table->string('nama_singkat');
            $table->foreignIdTo(JenjangPendidikan::class, nullable: true);
            $table->string('kode_level_akses', 10);
            $table->unsignedBigInteger('id_akreditasi_buku');
            $table->foreignIdTo(LembagaAkreditasi::class);
            $table->unsignedBigInteger('id_jenis_standar');
            $table->unsignedBigInteger('id_pengisian_panduan')->nullable();
            $table->bigInteger("id_dokumen")->nullable();
            $table->bigInteger("id_tipe")->nullable();
            $table->enum('tipe_edisi', ['pr', 'se'])->comment('pr = performance report | se = self evaluation');
            $table->string('deskripsi')->nullable();
            $table->boolean('apakah_aktif')->default(true);
            $table->date('tanggal_edisi')->nullable();
            $table->date('tanggal_efektif')->nullable();
            $table->date('tanggal_kadaluwarsa')->nullable();
            $table->boolean('apakah_sapto')->default(false);

            // create foreign key
            $table->foreign('id_dokumen')->references('id')->on('dms.dokumen');
            $table->foreign('id_akreditasi_buku')->references('id')->on('spmi.akreditasi_buku');
            $table->foreign('id_pengisian_panduan')->references('id')->on('spmi.pengisian_panduan');
            $table->foreign('id_jenis_standar')->references('id')->on('spmi.jenis_standar');

            // create index
            $table->index([
                'id_dokumen', 'id_tipe', 'id_akreditasi_buku',
                'id_pengisian_panduan', 'id_jenis_standar'
            ]);

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
        SevimaSchema::dropIfExists('spmi.pengisian_panduan');
    }
};
