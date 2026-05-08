<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\PMB\Models\Pengumuman;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('pmb.pengumuman', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('judul_pengumuman');
            $table->string('link_pengumuman');
            $table->text('isi_pengumuman');
            $table->char('jenis_pengumuman', 1)->comment('U: Pengumuman, I: Informasi, B: Brosur');
            $table->boolean('apakah_aktif')->default(true);
            $table->unsignedBigInteger('id_file_gambar')->nullable();
            $table->logs(true);
            $table->foreign('id_file_gambar')->references('id')->on('dms.dokumen');
            $table->index('jenis_pengumuman');
        });

        SevimaSchema::table('pmb.pengumuman', function (SevimaBlueprint $table) {
            $table->uniqueIndex('link_pengumuman', true);
        });

        // files/attachments
        SevimaSchema::create('pmb.pengumuman_file', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Pengumuman::class);
            $table->unsignedBigInteger('id_file')->comment('Dokumen');
            $table->logs();
            $table->foreign('id_file')->references('id')->on('dms.dokumen');
        });

        SevimaSchema::table('pmb.pengumuman_file', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_pengumuman', 'id_file'], true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.pengumuman_file');
        SevimaSchema::dropIfExists('pmb.pengumuman');
    }
};
