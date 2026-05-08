<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\UnitKerja;
use Modules\Litabmas\Models\PeriodePendanaan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('litabmas.sumber_pendanaan', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(PeriodePendanaan::class);
            $table->foreignIdTo(UnitKerja::class, 'id_unit_kerja');
            $table->string('nama_sumber_pendanaan')->comment('Nama Sumber Pendanaan');
            $table->string('kategori_sumber_pendanaan')->enum(['internal', 'external'])->comment('Kategori Sumber Pendanaan');
            $table->float('total_pendanaan')->comment('Total Pendanaan');
            $table->string('mata_uang', 3)->default('IDR')->comment('Mata Uang');
            $table->logs(true);
        });

        // unique index periode and name
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS sp_periode_unit_kategori_nama_unique ON litabmas.sumber_pendanaan
            (id_periode_pendanaan, id_unit_kerja, kategori_sumber_pendanaan, nama_sumber_pendanaan) WHERE waktu_dihapus IS NULL'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.sumber_pendanaan');
    }
};
