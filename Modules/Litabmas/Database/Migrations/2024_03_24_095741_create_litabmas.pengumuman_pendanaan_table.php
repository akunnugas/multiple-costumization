<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('litabmas.pengumuman_pendanaan', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\SumberPendanaan::class);
            $table->foreignIdTo(\Modules\DMS\Models\Dokumen::class, 'id_dokumen_thumbnail', true);

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
    }
};
