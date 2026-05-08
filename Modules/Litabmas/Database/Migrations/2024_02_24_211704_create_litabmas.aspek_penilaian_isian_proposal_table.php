<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Litabmas\Models\PeriodePendanaan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('litabmas.aspek_penilaian_isian_proposal', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(PeriodePendanaan::class, 'id_periode_pendanaan');
            $table->string('kode_jenis_pendanaan', 25)->comment('Jenis Pendanaan');
            $table->integer('urutan_isian_proposal')->nullable()->comment('Urutan');
            $table->string('nama_isian_proposal')->comment('Isian Proposal');

            $table->logs();
        });

        // unique index
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS apip_periode_pendanaan_jenis_pendanaan_nama_isian_unique
            ON litabmas.aspek_penilaian_isian_proposal (
                id_periode_pendanaan, kode_jenis_pendanaan, nama_isian_proposal
            ) WHERE waktu_dihapus IS NULL;');
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS apip_periode_pendanaan_jenis_pendanaan_urutan_isian_unique
            ON litabmas.aspek_penilaian_isian_proposal (
                id_periode_pendanaan, kode_jenis_pendanaan, urutan_isian_proposal
            ) WHERE waktu_dihapus IS NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.aspek_penilaian_isian_proposal');
    }
};
