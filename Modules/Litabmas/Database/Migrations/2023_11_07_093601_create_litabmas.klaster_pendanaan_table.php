<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Litabmas\Models\SumberPendanaan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('litabmas.klaster_pendanaan', function (SevimaBlueprint $table) {
            $table->id()->comment('ID Klaster Pendanaan');
            $table->string('kode_jenis_pendanaan', 25)->nullable()->comment('Jenis Pendanaan');
            $table->foreignIdTo(SumberPendanaan::class, 'id_sumber_pendanaan');
            $table->string('nama_klaster')->comment('Nama Klaster Pendanaan');
            $table->jsonb('persyaratan_administratif_ketua')->nullable()->comment('Persyaratan Administratif Ketua');
            $table->boolean('apakah_butuh_approve_semua_anggota')->default(false)
                ->comment('Untuk mengajukan proposal apakah perlu approval semua anggota?');
            $table->integer('maksimal_anggota')->comment('Maksimal Anggota');
            $table->float('maksimal_anggaran')->comment('Maksimal Anggaran');
            $table->string('mata_uang', 3)->default('IDR')->comment('Mata Uang');

            $table->logs(true);
        });

        // unique index source and name
        DB::statement('CREATE UNIQUE INDEX kp_sumber_pendanaan_nama_unique
            ON litabmas.klaster_pendanaan (
                id_sumber_pendanaan, nama_klaster
            ) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('litabmas.klaster_pendanaan');
    }
};
