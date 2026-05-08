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
        SevimaSchema::create('litabmas.aspek_penilaian_komposisi_proposal', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\PeriodePendanaan::class, 'id_periode_pendanaan');
            $table->string('kode_jenis_pendanaan', 25)->comment('Jenis Pendanaan');
            $table->string('nama_komposisi_proposal')->comment('Nama Aspek Penilaian');
            $table->decimal('bobot_komposisi_proposal', 5, 2)->comment('Bobot');

            $table->logs();
        });

        // unique index
        DB::statement('CREATE UNIQUE INDEX apkp_periode_pendanaan_jenis_pendanaan_name_unique ON litabmas.aspek_penilaian_komposisi_proposal (
            id_periode_pendanaan, kode_jenis_pendanaan, nama_komposisi_proposal
        ) WHERE waktu_dihapus IS NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.aspek_penilaian_komposisi_proposal');
    }
};
