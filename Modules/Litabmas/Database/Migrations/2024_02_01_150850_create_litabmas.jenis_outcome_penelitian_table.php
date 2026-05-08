<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Litabmas\Models\JenisPublikasi;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('litabmas.jenis_outcome_penelitian', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(JenisPublikasi::class, 'id_jenis_publikasi');
            $table->string('nama_outcome')->comment('Jenis Outcome');
            $table->string('kategori_outcome', 2)->nullable()->comment('Kategori Outcome');
            $table->unsignedTinyInteger('batas_pengumpulan_outcome')->nullable()->comment('Batas Pengumpulan');

            $table->logs();
        });

        DB::statement('CREATE UNIQUE INDEX ON litabmas.jenis_outcome_penelitian (nama_outcome) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.jenis_outcome_penelitian');
    }
};
