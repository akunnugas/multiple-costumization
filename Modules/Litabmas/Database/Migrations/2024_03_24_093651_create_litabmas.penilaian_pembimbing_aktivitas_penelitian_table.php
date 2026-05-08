<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\DMS\Models\Dokumen;
use Modules\Litabmas\Models\PengajuanPendanaanAktivitasPenelitian;
use Modules\Litabmas\Models\PengajuanPendanaanPembimbing;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('litabmas.penilaian_pembimbing_aktivitas_penelitian', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(PengajuanPendanaanPembimbing::class, 'id_pengajuan_pendanaan_pembimbing',
                foreignName: 'litabmas_ppap_id_pengajuan_pendanaan_pembimbing', indexName: 'ppap_id_pengajuan_pendanaan_pembimbing_foreign'
            );
            $table->foreignIdTo(PengajuanPendanaanAktivitasPenelitian::class, 'id_pengajuan_pendanaan_aktivitas_penelitian',
                foreignName: 'litabmas_ppap_id_pengajuan_pendanaan_aktivitas_penelitian', indexName: 'ppap_id_pengajuan_pendanaan_aktivitas_penelitian_foreign'
            );
            $table->text('feedback_logbook')->nullable()->comment('Feedback');
            $table->foreignIdTo(Dokumen::class, 'id_dokumen_feedback_logbook', true);

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
        SevimaSchema::dropIfExists('litabmas.penilaian_pembimbing_aktivitas_penelitian');
    }
};
