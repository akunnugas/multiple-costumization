<?php

use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Extensions\SevimaBlueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\DMS\Models\Folder;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('dms.dokumen', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Folder::class, 'id_folder');
            $table->string('nama_dokumen', 255)->comment('Nama Dokumen');
            $table->uuid('slug')->comment('Slug Dokumen');
            $table->bigInteger('ukuran')->comment('Ukuran Dokumen');
            $table->string('kode_modul', 100)->comment('Kode Modul');
            $table->string('catatan', 255)->nullable()->comment('Catatan');
            $table->string('alamat_versi_terbaru')->comment('Path Versi Terakhir');
            $table->string('extension_versi_terbaru', 10)->comment('Ekstensi Versi Terakhir');
            $table->integer('versi_terbaru')->comment('Versi Terakhir');
            $table->tinyInteger('visibilitas')->comment('Visibilitas (1: Private, 2: Public, 3: Limited)');
            $table->logs(true);
        });

        SevimaSchema::table('dms.dokumen', function (SevimaBlueprint $table) {
            $table->index('slug');
            $table->index('nama_dokumen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('dms.dokumen');
    }
};
