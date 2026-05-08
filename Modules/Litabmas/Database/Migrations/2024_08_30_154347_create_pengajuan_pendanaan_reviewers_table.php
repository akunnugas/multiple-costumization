<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\DMS\Models\Dokumen;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        try {
            SevimaSchema::create('litabmas.pengajuan_pendanaan_reviewers', function (SevimaBlueprint $table) {
                $table->id();
                $table->foreignId('id_pengajuan_pendanaan')->constrained('litabmas.pengajuan_pendanaan');
                $table->foreignId('id_biodata')->constrained('core.biodata');
                $table->foreignIdTo(Dokumen::class, 'id_dokumen_sk', true);
                $table->integer('reviewer_ke');
                $table->boolean('apakah_internal')->default(true);
                $table->boolean('apakah_review_proposal')->default(false)->comment('Reviewer Proposal');
                $table->boolean('apakah_review_luaran')->default(false)->comment('Reviewer Output');
                $table->boolean('apakah_review_antara')->default(false)->comment('Reviewer Progress Report');

                // administrasi
                $table->integer('total_nilai_komposisi_reviewer')->nullable();
                $table->integer('total_nilai_presentasi_reviewer')->nullable();
                $table->string('status_penilaian_presentasi', 25)->nullable();
                $table->string('status_penilaian_isian_proposal', 25)->nullable();
                $table->integer('rekomendasi_anggaran')->nullable();

                // kegiatan
                $table->string('status_penilaian_progress_report', 25);
                $table->string('status_penilaian_output', 25);
                $table->string('status_penilaian_output_bersama', 25);

                $table->text('komentar_umum_reviewer_output')->nullable()->comment('Reviewer Output');
                $table->text('komentar_umum_reviewer_progress_report')->nullable()->comment('Review Progress Report');
                $table->logs(true);
            });

            SevimaSchema::table('litabmas.penilaian_reviewer_isian_proposal', function (SevimaBlueprint $table) {
                $table->renameColumn('id_pengajuan_pendanaan_reviewer_administrasi', 'id_pengajuan_pendanaan_reviewer');

                // drop fk & index lama
                $table->dropForeign('litabmas_prib_id_pengajuan_pendanaan_reviewer');

                // generate fk & index baru
                $table->foreign('id_pengajuan_pendanaan_reviewer', 'litabmas_pris_id_pengajuan_pendanaan_reviewer_fk')
                    ->references('id')->on('litabmas.pengajuan_pendanaan_reviewers');
                $table->index('id_pengajuan_pendanaan_reviewer', 'pris_id_pengajuan_pendanaan_reviewer_idx');
            });

            SevimaSchema::table('litabmas.penilaian_reviewer_laporan_progres', function (SevimaBlueprint $table) {
                $table->renameColumn('id_pengajuan_pendanaan_reviewer_kegiatan', 'id_pengajuan_pendanaan_reviewer');

                // drop fk & index lama
                $table->dropForeign('litabmas_prlp_id_pengajuan_pendanaan_reviewer_kegiatan_fk');

                // generate fk & index baru
                $table->foreign('id_pengajuan_pendanaan_reviewer', 'litabmas_prlp_id_pengajuan_pendanaan_reviewer_fk')
                    ->references('id')->on('litabmas.pengajuan_pendanaan_reviewers');
                $table->index('id_pengajuan_pendanaan_reviewer', 'prlp_id_pengajuan_pendanaan_reviewer_idx');
            });

            SevimaSchema::table('litabmas.penilaian_reviewer_presentasi_proposal', function (SevimaBlueprint $table) {
                $table->renameColumn('id_pengajuan_pendanaan_reviewer_administrasi', 'id_pengajuan_pendanaan_reviewer');

                // drop fk & index lama
                $table->dropForeign('litabmas_prpp_id_pengajuan_pendanaan_reviewer');

                // generate fk & index baru
                $table->foreign('id_pengajuan_pendanaan_reviewer', 'litabmas_prpp_id_pengajuan_pendanaan_reviewer_fk')
                    ->references('id')->on('litabmas.pengajuan_pendanaan_reviewers');
                $table->index('id_pengajuan_pendanaan_reviewer', 'prpp_id_pengajuan_pendanaan_reviewer_idx');
            });

            SevimaSchema::table('litabmas.penilaian_reviewer_komposisi_proposal', function (SevimaBlueprint $table) {
                $table->renameColumn('id_pengajuan_pendanaan_reviewer_administrasi', 'id_pengajuan_pendanaan_reviewer');

                // drop fk & index lama
                $table->dropForeign('litabmas_penilaian_reviewer_komposisi_proposal_id_pengajuan_pen');

                // generate fk & index baru
                $table->foreign('id_pengajuan_pendanaan_reviewer', 'litabmas_prkp_id_pengajuan_pendanaan_reviewer_fk')
                    ->references('id')->on('litabmas.pengajuan_pendanaan_reviewers');
                $table->index('id_pengajuan_pendanaan_reviewer', 'prkp_id_pengajuan_pendanaan_reviewer_idx');
            });

            SevimaSchema::table('litabmas.penilaian_reviewer_output', function (SevimaBlueprint $table) {
                $table->renameColumn('id_pengajuan_pendanaan_reviewer_kegiatan', 'id_pengajuan_pendanaan_reviewer');

                // drop fk & index lama
                $table->dropForeign('litabmas_pro_id_pengajuan_pendanaan_reviewer_kegiatan_fk');

                // generate fk & index baru
                $table->foreign('id_pengajuan_pendanaan_reviewer', 'litabmas_pro_id_pengajuan_pendanaan_reviewer_fk')
                    ->references('id')->on('litabmas.pengajuan_pendanaan_reviewers');
                $table->index('id_pengajuan_pendanaan_reviewer', 'pro_id_pengajuan_pendanaan_reviewer_idx');
            });

            SevimaSchema::table('litabmas.penilaian_reviewer_output_bersama', function (SevimaBlueprint $table) {
                // drop fk & index lama
                $table->dropForeign('litabmas_prob_id_reviewer_kegiatan_pembuat_fk');
                $table->dropForeign('litabmas_prob_id_reviewer_kegiatan_pengubah_fk');

                // generate fk & index baru
                $table->foreign('id_pengajuan_pendanaan_reviewer_pembuat', 'litabmas_prob_id_pengajuan_pendanaan_reviewer_pembuat_fk')
                    ->references('id')->on('litabmas.pengajuan_pendanaan_reviewers');

                $table->foreign('id_pengajuan_pendanaan_reviewer_pengubah', 'litabmas_prob_id_pengajuan_pendanaan_reviewer_pengubah_fk')
                    ->references('id')->on('litabmas.pengajuan_pendanaan_reviewers');
            });

            SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan_reviewer_administrasi');
            SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan_reviewer_kegiatan');

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan_reviewers');
    }
};
