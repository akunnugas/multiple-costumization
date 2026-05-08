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
        SevimaSchema::create('litabmas.klaster_pendanaan_agenda_kegiatan', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\KlasterPendanaan::class);
            $table->foreignIdTo(\Modules\Litabmas\Models\AgendaKegiatan::class);
            $table->timestampTz('waktu_mulai')->nullable()->comment('Waktu Awal Tahapan Kegiatan');
            $table->timestampTz('waktu_selesai')->nullable()->comment('Waktu Akhir Tahapan Kegiatan');

            $table->logs();
        });

        // unique
        DB::statement('CREATE UNIQUE INDEX ON litabmas.klaster_pendanaan_agenda_kegiatan (id_klaster_pendanaan, id_agenda_kegiatan) WHERE waktu_dihapus IS NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.klaster_pendanaan_agenda_kegiatan');
    }
};
