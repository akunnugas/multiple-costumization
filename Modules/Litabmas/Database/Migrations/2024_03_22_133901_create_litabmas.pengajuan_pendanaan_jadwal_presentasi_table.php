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
        SevimaSchema::create('litabmas.pengajuan_pendanaan_jadwal_presentasi', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\PengajuanPendanaan::class, 'id_pengajuan_pendanaan');
            $table->string('nama_kegiatan', 255)->comment('Nama kegiatan');
            $table->string('tipe_kegiatan', 25)->comment('Tipe kegiatan');
            $table->timestampTz('waktu_pelaksanaan')->comment('Waktu pelaksanaan');
            $table->string('tempat_pelaksanaan', 255)->nullable()->comment('Tempat pelaksanaan');
            $table->string('link_presentasi_kegiatan', 255)->nullable()->comment('Link presentasi kegiatan');

            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan_jadwal_presentasi');
    }
};
