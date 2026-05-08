<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('litabmas.pengajuan_pendanaan_aktivitas_penelitian', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\PengajuanPendanaan::class, 'id_pengajuan_pendanaan');
            $table->foreignIdTo(\Modules\Litabmas\Models\JenisAktivitas::class, 'id_jenis_aktivitas');
            $table->string('nama_aktivitas_peneitian')->comment('Nama aktivitas penelitian');
            $table->date('tanggal_aktivitas_penelitian')->comment('Tanggal aktivitas penelitian');
            $table->string('lokasi_aktivitas_penelitian')->comment('Lokasi aktivitas penelitian');
            $table->foreignIdTo(\Modules\DMS\Models\Dokumen::class, 'id_dokumen_logbook');

            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan_aktivitas_penelitian');
    }
};
