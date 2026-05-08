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
        SevimaSchema::create('litabmas.aspek_penilaian_presentasi_proposal', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(PeriodePendanaan::class, 'id_periode_pendanaan');
            $table->string('kode_jenis_pendanaan', 25)->comment('Jenis Pendanaan');
            $table->text('pertanyaan_presentasi_proposal')->comment('Pertanyaan');
            $table->decimal('bobot_pertanyaan_presentasi_proposal', 5, 2)->comment('Bobot');

            $table->logs();
        });

        // unique index
        DB::statement('CREATE UNIQUE INDEX appp_periode_pendanaan_jenis_pendanaan_pertanyaan_unique
            ON litabmas.aspek_penilaian_presentasi_proposal (
                id_periode_pendanaan, kode_jenis_pendanaan, pertanyaan_presentasi_proposal
            ) WHERE waktu_dihapus IS NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.aspek_penilaian_presentasi_proposal');
    }
};
