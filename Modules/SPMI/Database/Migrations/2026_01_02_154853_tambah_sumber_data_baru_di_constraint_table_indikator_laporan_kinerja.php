<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop constraint lama
        DB::statement("
            ALTER TABLE spmi.indikator_laporan_kinerja
            DROP CONSTRAINT IF EXISTS indikator_laporan_kinerja_sumber_data_check
        ");

        // Buat constraint baru dengan expression yang baru
        DB::statement("
            ALTER TABLE spmi.indikator_laporan_kinerja
            ADD CONSTRAINT indikator_laporan_kinerja_sumber_data_check
            CHECK (
                sumber_data::text = ANY (ARRAY['IN','AK','SDM','SA','AC','TS','MBK','SKM','PMB']::text[])
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
