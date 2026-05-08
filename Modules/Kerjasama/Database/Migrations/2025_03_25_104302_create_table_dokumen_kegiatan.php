<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\DMS\Models\Dokumen;
use Modules\Kerjasama\Models\Kegiatan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('kerjasama.dokumen_kegiatan', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Kegiatan::class, 'id_kegiatan');
            $table->foreignIdTo(Dokumen::class, 'id_dokumen');
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
        SevimaSchema::dropIfExists('kerjasama.dokumen_kegiatan');
    }
};
