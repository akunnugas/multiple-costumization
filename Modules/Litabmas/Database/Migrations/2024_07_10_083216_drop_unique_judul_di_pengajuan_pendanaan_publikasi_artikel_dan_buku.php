<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // drop unique index pppb_pengajuan_pendanaan_judul_buku_unique ON litabmas.pengajuan_pendanaan_publikasi_buku
        DB::statement('DROP INDEX IF EXISTS litabmas.pppa_pengajuan_pendanaan_judul_artikel_unique;');

        DB::statement('DROP INDEX IF EXISTS litabmas.pppb_pengajuan_pendanaan_judul_buku_unique;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
