<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\DMS\Models\Dokumen;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableReviewerKegiatan = 'litabmas.pengajuan_pendanaan_reviewer_kegiatan';

        // STEP 1: ubah nama table pengajuan_pendanaan_reviewer menjadi pengajuan_pendanaan_reviewer_administrasi
        // ubah nama table
        SevimaSchema::rename('litabmas.pengajuan_pendanaan_reviewer', 'pengajuan_pendanaan_reviewer_administrasi');

        // penyesuaian pengajuan_pendanaan_reviewer_administrasi
        // SevimaSchema::table('litabmas.pengajuan_pendanaan_reviewer_administrasi', function (SevimaBlueprint $table) {
        //     // kolom-kolom ini pindah ke reviewer_kegiatan
        //     $table->dropColumn('status_penilaian_progress_report');
        //     $table->dropColumn('status_penilaian_output');
        //     $table->dropColumn('status_penilaian_output_bersama');
        // });

        // STEP 2: nambah table baru litabmas.pengajuan_pendanaan_reviewer_kegiatan
        SevimaSchema::create($tableReviewerKegiatan, function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\PengajuanPendanaan::class, 'id_pengajuan_pendanaan');
            $table->foreignIdTo(\Modules\Gate\Models\User::class, 'id_user');
            $table->unsignedTinyInteger('reviewer_ke')->comment('Reviewer ke');
            $table->foreignIdTo(Dokumen::class, 'id_dokumen_sk', true);
            $table->string('tipe_reviewer', 100)->nullable()->comment('Tipe reviewer');
            $table->text('komentar_umum_penilaian_output')->nullable()->comment('Komentar umum penilaian output');
            $table->text('komentar_umum_presentasi_progres')->nullable()->comment('Komentar umum presentasi progres');
            $table->string('status_penilaian_progress_report', 25)->nullable()->comment('Status Penilaian');
            $table->string('status_penilaian_output', 25)->nullable()->comment('Status Penilaian');
            $table->string('status_penilaian_output_bersama', 25)->nullable()->comment('Status Penilaian Output Bersama');

            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // STEP 1: rollback table pengajuan_pendanaan_reviewer_administrasi
        // rollback pengajuan_pendanaan_reviewer_administrasi
        SevimaSchema::table('litabmas.pengajuan_pendanaan_reviewer_administrasi', function (SevimaBlueprint $table) {
            $table->string('status_penilaian_progress_report', 25)->nullable()->comment('Status Penilaian');
            $table->string('status_penilaian_output', 25)->nullable()->comment('Status Penilaian');
            $table->string('status_penilaian_output_bersama', 25)->nullable()->comment('Status Penilaian Output Bersama');
        });

        // rollback pengajuan_pendanaan_reviewer
        SevimaSchema::rename('litabmas.pengajuan_pendanaan_reviewer_administrasi', 'pengajuan_pendanaan_reviewer');

        // STEP 2: rollback table litabmas.pengajuan_pendanaan_reviewer_kegiatan
        SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan_reviewer_kegiatan');
    }
};
