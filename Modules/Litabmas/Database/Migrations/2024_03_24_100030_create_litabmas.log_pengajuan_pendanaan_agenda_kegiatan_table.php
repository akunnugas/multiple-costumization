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
        SevimaSchema::create('litabmas.log_pengajuan_pendanaan_agenda_kegiatan', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\PengajuanPendanaan::class);
            $table->foreignIdTo(\Modules\Litabmas\Models\AgendaKegiatan::class, 'id_agenda_sebelumnya');
            $table->foreignIdTo(\Modules\Litabmas\Models\AgendaKegiatan::class, 'id_agenda_sekarang');

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
        SevimaSchema::dropIfExists('litabmas.log_pengajuan_pendanaan_agenda_kegiatan');
    }
};
