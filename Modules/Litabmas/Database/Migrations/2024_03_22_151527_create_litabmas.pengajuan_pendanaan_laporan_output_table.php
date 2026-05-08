<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaanOutputPenelitian;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('litabmas.pengajuan_pendanaan_laporan_output', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(PengajuanPendanaan::class, 'id_pengajuan_pendanaan',
                foreignName: 'litabmas_pplo_id_pengajuan_pendanaan', indexName: 'pplo_id_pengajuan_pendanaan_foreign'
            );
            $table->foreignIdTo(PengajuanPendanaanOutputPenelitian::class, 'id_pengajuan_pendanaan_output_penelitian',
                foreignName: 'litabmas_pplo_id_pengajuan_pendanaan_output_penelitian', indexName: 'pplo_id_pengajuan_pendanaan_output_penelitian_foreign'
            );
            $table->foreignIdTo(\Modules\DMS\Models\Dokumen::class, 'id_dokumen_output', true);
            $table->string('status_output', 25)->nullable()->comment('Status');

            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan_laporan_output');
    }
};
