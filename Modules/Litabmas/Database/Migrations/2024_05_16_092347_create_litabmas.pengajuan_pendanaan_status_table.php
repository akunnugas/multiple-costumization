<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('litabmas.pengajuan_pendanaan_status', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\PengajuanPendanaan::class);
            $table->string('status_penilaian_administrasi')->nullable();
            $table->string('status_penentuan_nominasi')->nullable();
            $table->string('status_penentuan_pendanaan')->nullable();
            $table->string('status_penilaian_isian_proposal')->nullable();
            $table->string('status_penilaian_presentasi_proposal')->nullable();
            $table->string('status_penilaian_progress_report')->nullable();
            $table->string('status_penilaian_output')->nullable();
            $table->string('status_bimbingan_logbook')->nullable();

            $table->logs();
        });

        // drop old columns
        SevimaSchema::table('litabmas.pengajuan_pendanaan', function (SevimaBlueprint $table) {
            $table->dropColumn('apakah_dokumen_lengkap');
            $table->dropColumn('waktu_validasi_dokumen');
            $table->dropColumn('apakah_similarity_ai_memenuhi');
            $table->dropColumn('waktu_validasi_similarity_ai');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan_status');

        // add column
        SevimaSchema::table('litabmas.pengajuan_pendanaan', function (SevimaBlueprint $table) {
            $table->boolean('apakah_dokumen_lengkap')->nullable();
            $table->timestampTz('waktu_validasi_dokumen')->nullable();
            $table->boolean('apakah_similarity_ai_memenuhi')->nullable();
            $table->timestampTz('waktu_validasi_similarity_ai')->nullable();
        });
    }
};
