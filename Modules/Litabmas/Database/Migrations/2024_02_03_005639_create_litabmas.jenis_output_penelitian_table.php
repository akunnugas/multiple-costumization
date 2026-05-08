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
        SevimaSchema::create('litabmas.jenis_output_penelitian', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\JenisPublikasi::class, 'id_jenis_publikasi');
            $table->string('kategori_output', 2)->nullable()->comment('Kategori Output');

            $table->logs();
        });

        DB::statement('CREATE UNIQUE INDEX ON litabmas.jenis_output_penelitian (id_jenis_publikasi) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.jenis_output_penelitian');
    }
};
