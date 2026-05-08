<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('litabmas.pengajuan_pendanaan_publikasi_artikel', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\PengajuanPendanaan::class, 'id_pengajuan_pendanaan');
            $table->string('judul_artikel', 255)->comment('Judul artikel');
            $table->string('situs_publikasi_jurnal', 255)->comment('Platform publikasi penelitian');
            $table->string('volume_dan_nomor_terbitan', 255)->comment('Volume & nomor terbitan');
            $table->string('url_artikel', 255)->comment('URL artikel');

            $table->logs();
        });

        // create unique index
        DB::statement('CREATE UNIQUE INDEX pppa_pengajuan_pendanaan_judul_artikel_unique ON litabmas.pengajuan_pendanaan_publikasi_artikel (
            id_pengajuan_pendanaan, judul_artikel
        ) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan_publikasi_artikel');
    }
};
