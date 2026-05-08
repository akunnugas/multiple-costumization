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
        SevimaSchema::create('litabmas.jenis_publikasi', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('ref_key_siakad')->nullable()->comment('Kolom PK SIAKAD V1');
            $table->string('nama_jenis_publikasi')->comment('Nama Jenis Publikasi');

            $table->logs();
        });

        // unique index for code
        DB::statement('CREATE UNIQUE INDEX ON litabmas.jenis_publikasi (ref_key_siakad) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.jenis_publikasi');
    }
};
