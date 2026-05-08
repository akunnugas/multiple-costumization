<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\DMS\Models\Dokumen;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('litabmas.dokumen_petunjuk_teknis', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Dokumen::class, 'id_dokumen');
            $table->string('nama_dokumen_teknis')->comment('Nama Dokumen');
            $table->string('jenis_dokumen_teknis', 100)->comment('Jenis Dokumen');
            $table->boolean('apakah_aktif')->default(false)->comment('Status');

            $table->logs();
        });

        // unique name
        DB::statement('CREATE UNIQUE INDEX ON litabmas.dokumen_petunjuk_teknis (nama_dokumen_teknis) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.dokumen_petunjuk_teknis');
    }
};
