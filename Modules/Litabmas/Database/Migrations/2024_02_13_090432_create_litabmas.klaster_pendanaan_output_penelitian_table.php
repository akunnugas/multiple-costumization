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
        SevimaSchema::create('litabmas.klaster_pendanaan_output_penelitian', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\KlasterPendanaan::class, 'id_klaster_pendanaan');
            $table->foreignIdTo(\Modules\Litabmas\Models\JenisOutputPenelitian::class, 'id_jenis_output_penelitian');
            $table->boolean('apakah_wajib')->default(false)->comment('Wajib?');

            $table->logs();
        });

        // unique
        DB::statement('CREATE UNIQUE INDEX ON litabmas.klaster_pendanaan_output_penelitian (
            id_klaster_pendanaan, id_jenis_output_penelitian
        ) WHERE waktu_dihapus IS NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.klaster_pendanaan_output_penelitian');
    }
};
