<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::dropIfExists('litabmas.pengumuman_pendanaan');

        SevimaSchema::create('litabmas.pengumuman_pendanaan', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('judul', 255)->comment('Judul Pengumuman');
            $table->text('informasi')->comment('Informasi Pengumuman');
            $table->foreignIdTo(\Modules\DMS\Models\Dokumen::class, 'id_dokumen_lampiran', true);
            $table->string('status_pengumuman')->default('draft')->comment('Status');
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
        SevimaSchema::dropIfExists('litabmas.pengumuman_pendanaan');

        SevimaSchema::create('litabmas.pengumuman_pendanaan', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\SumberPendanaan::class);
            $table->foreignIdTo(\Modules\DMS\Models\Dokumen::class, 'id_dokumen_thumbnail', true);
            $table->foreignIdTo(\Modules\Litabmas\Models\DokumenPetunjukTeknis::class);
            $table->string('status_pengumuman')->default('draft')->comment('Status');
            $table->logs();
        });
    }
};
