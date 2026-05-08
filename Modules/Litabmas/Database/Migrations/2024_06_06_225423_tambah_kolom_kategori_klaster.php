<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Litabmas\Models\KlasterPendanaan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('litabmas.klaster_pendanaan', function (SevimaBlueprint $table) {
            // update jadi nullable maksimal_anggota
            $table->integer('maksimal_anggota')->nullable()->change();

            // tambah 2 kolom kategori klaster dan minimal_anggota
            $table->integer('minimal_anggota')->nullable()->comment('Minimal Anggota');
            $table->string('kategori_klaster', 25)->nullable()->comment('Kategori Klaster');
        });

        // set data lama kategorinya sebagai kelompok
        DB::table('litabmas.klaster_pendanaan')
            ->whereNull('kategori_klaster')
            ->update([
                'kategori_klaster' => KlasterPendanaan::KATEGORI_KELOMPOK,
                'minimal_anggota' => 1
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.klaster_pendanaan', function (SevimaBlueprint $table) {
            // hapus 2 kolom kategori klaster dan minimal_anggota
            $table->dropColumn('minimal_anggota');
            $table->dropColumn('kategori_klaster');

            // note: biarkan maksimal_anggota nullable
        });
    }
};
