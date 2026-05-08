<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('litabmas.klaster_pendanaan_bidang_ilmu', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\KlasterPendanaan::class, 'id_klaster_pendanaan');
            $table->foreignIdTo(\Modules\Litabmas\Models\BidangIlmu::class, 'id_bidang_ilmu');

            $table->logs();
        });

        // unique index
        DB::statement('CREATE UNIQUE INDEX ON litabmas.klaster_pendanaan_bidang_ilmu (id_klaster_pendanaan, id_bidang_ilmu) WHERE waktu_dihapus IS NULL');

        SevimaSchema::create('litabmas.klaster_pendanaan_bidang_ilmu_tema', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\KlasterPendanaanBidangIlmu::class, 'id_klaster_pendanaan_bidang_ilmu');
            $table->foreignIdTo(\Modules\Litabmas\Models\TemaKegiatan::class, 'id_tema_kegiatan');

            $table->logs();
        });

        // unique index
        DB::statement('CREATE UNIQUE INDEX ON litabmas.klaster_pendanaan_bidang_ilmu_tema (id_klaster_pendanaan_bidang_ilmu, id_tema_kegiatan) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.klaster_pendanaan_bidang_ilmu_tema');
        SevimaSchema::dropIfExists('litabmas.klaster_pendanaan_bidang_ilmu');
    }
};
