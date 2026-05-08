<?php

use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Extensions\SevimaBlueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\DMS\Models\Dokumen;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('dms.dokumen_versi', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Dokumen::class, 'id_dokumen');
            $table->integer('versi')->comment('Versi Dokumen');
            $table->string('alamat_berkas', 255)->comment('Path File');
            $table->string('extension', 10)->comment('Ekstensi File');
            $table->integer('ukuran')->comment('Ukuran File');
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
        SevimaSchema::dropIfExists('dms.dokumen_versi');
    }
};
