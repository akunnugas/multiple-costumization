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
        SevimaSchema::create('litabmas.agenda_kegiatan', function (SevimaBlueprint $table) {
            $table->id();
            $table->unsignedInteger('urutan')->comment('Urutan Kegiatan');
            $table->string('kode_agenda', 100)->comment('Kode Tahapan Kegiatan');
            $table->string('nama_agenda')->comment('Tahapan Kegiatan');
            $table->boolean('apakah_wajib')->default(false)->comment('Tahapan Kegiatan Wajib');

            $table->logs();
        });

        // unique index code and name where waktu_dihapus is null
        DB::statement('CREATE UNIQUE INDEX ON litabmas.agenda_kegiatan (kode_agenda) WHERE waktu_dihapus IS NULL');
        DB::statement('CREATE UNIQUE INDEX ON litabmas.agenda_kegiatan (nama_agenda) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.agenda_kegiatan');
    }
};
