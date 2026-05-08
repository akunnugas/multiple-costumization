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
        SevimaSchema::create('litabmas.jenis_aktivitas', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_jenis_aktivitas')->comment('Nama jenis aktivitas');
            $table->logs(true);
        });

        DB::statement('CREATE UNIQUE INDEX ON litabmas.jenis_aktivitas (nama_jenis_aktivitas) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.jenis_aktivitas');
    }
};
