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
        SevimaSchema::create('litabmas.pengajuan_pendanaan_revisi_proposal', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\PengajuanPendanaan::class);
            $table->foreignIdTo(\Modules\DMS\Models\Dokumen::class, 'id_dokumen');
            $table->boolean('apakah_asli')->default(false)->comment('Apakah dokumen asli');

            $table->logs(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan_revisi_proposal');
    }
};
