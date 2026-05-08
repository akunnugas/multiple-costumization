<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
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
        SevimaSchema::create('core.unit_kerja', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_unit');
            $table->string('kode_unit', 10);
            $table->string('kode_dikti', 10)->nullable();
            $table->char('jenis_unit', 1)->nullable()->comment('U: Universitas, P: Program Studi, F: Fakultas');
            $table->foreignIdTo(JenjangPendidikan::class, nullable: true);
            $table->unsignedBigInteger('id_parent')->nullable();
            $table->unsignedInteger('info_level')->nullable();
            $table->integer('info_left')->nullable();
            $table->integer('info_right')->nullable();
            $table->boolean('apakah_akademik');
            $table->boolean('apakah_satker');
            $table->boolean('apakah_aktif')->default(true);
            $table->boolean('apakah_data_default')->default(false);
            $table->string('akreditasi')->nullable()->comment('Akreditasi');
            $table->foreignIdTo(LembagaAkreditasi::class, nullable: true);
            $table->string('kebutuhan_lulusan', 2)->nullable()->comment('HI: Tinggi, LW: Rendah');
            $table->string('kelompok_prodi', 2)->nullable()->comment('SH: Sosial Humaniora, ST: Sains Teknologi');
            $table->text('deskripsi_unit')->nullable();
            $table->unsignedBigInteger('id_file_foto')->nullable();
            $table->string('ref_key_siakad')->nullable();
            $table->logs(true);
            $table->foreign('id_file_foto')->references('id')->on('dms.dokumen');
            $table->index('id_parent');
            $table->index('info_left');
            $table->index('info_right');
            $table->index('id_file_foto');
        });

        SevimaSchema::table('core.unit_kerja', function (SevimaBlueprint $table) {
            $table->foreign('id_parent')->references('id')->on('core.unit_kerja');
            $table->uniqueIndex('kode_unit', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.unit_kerja');
    }
};
