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
        SevimaSchema::create('litabmas.pengajuan_pendanaan_publikasi_buku', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\PengajuanPendanaan::class, 'id_pengajuan_pendanaan');
            $table->string('judul_buku', 255)->comment('Judul artikel');
            $table->string('isbn', 255)->comment('ISBN buku');
            $table->string('penerbit_buku', 255)->comment('Penerbit');
            $table->year('tahun_terbit_buku')->comment('Tahun terbit');

            $table->logs();
        });

        // unique index
        DB::statement('CREATE UNIQUE INDEX pppb_pengajuan_pendanaan_judul_buku_unique ON litabmas.pengajuan_pendanaan_publikasi_buku (
            id_pengajuan_pendanaan, judul_buku
        ) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan_publikasi_buku');
    }
};
