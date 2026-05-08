<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Litabmas\Models\AgendaKegiatan;
use Modules\Litabmas\Models\SumberPendanaan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('litabmas.sumber_pendanaan_agenda_kegiatan', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(SumberPendanaan::class, 'id_sumber_pendanaan');
            $table->foreignIdTo(AgendaKegiatan::class, 'id_agenda_kegiatan');
            $table->boolean('apakah_aktif')->default(false);

            $table->logs(false);
        });

        // unique
        DB::statement('CREATE UNIQUE INDEX ON litabmas.sumber_pendanaan_agenda_kegiatan (id_sumber_pendanaan, id_agenda_kegiatan);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.sumber_pendanaan_agenda_kegiatan');
    }
};
