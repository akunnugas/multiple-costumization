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
        SevimaSchema::create('litabmas.pengajuan_pendanaan_laporan_progres', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\PengajuanPendanaan::class, 'id_pengajuan_pendanaan');
            $table->string('jenis_laporan_progres', 25)->comment('Jenis laporan');
            $table->foreignIdTo(\Modules\DMS\Models\Dokumen::class, 'id_dokumen_laporan_progres', true);
            $table->string('status_laporan_progres')->comment('Status laporan progres');

            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan_laporan_progres');
    }
};
