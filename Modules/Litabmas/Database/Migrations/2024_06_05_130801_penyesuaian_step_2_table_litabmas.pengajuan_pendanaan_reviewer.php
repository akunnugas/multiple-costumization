<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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
        $tableReviewerKegiatan = 'litabmas.pengajuan_pendanaan_reviewer_kegiatan';

        // STEP 1: update 'penilaian_reviewer_isian_proposal', 'penilaian_reviewer_komposisi_proposal', 'penilaian_reviewer_komposisi_proposal'
        // nama kolomnya nya jadi ketambahan '_administrasi'
        // tidak perlu ubah relasinya, karena di migration sblmnya ubah nama table, otomatis semua relasi ke table ini juga ikut berubah
        SevimaSchema::table('litabmas.penilaian_reviewer_isian_proposal', function (SevimaBlueprint $table) {
            $table->renameColumn('id_pengajuan_pendanaan_reviewer', 'id_pengajuan_pendanaan_reviewer_administrasi');
        });
        SevimaSchema::table('litabmas.penilaian_reviewer_komposisi_proposal', function (SevimaBlueprint $table) {
            $table->renameColumn('id_pengajuan_pendanaan_reviewer', 'id_pengajuan_pendanaan_reviewer_administrasi');
            $table->index('id_pengajuan_pendanaan_reviewer_administrasi', 'prkp_id_pengajuan_pendanaan_reviewer_administrasi_idx');
        });
        SevimaSchema::table('litabmas.penilaian_reviewer_presentasi_proposal', function (SevimaBlueprint $table) {
            $table->renameColumn('id_pengajuan_pendanaan_reviewer', 'id_pengajuan_pendanaan_reviewer_administrasi');
        });

        // STEP 2: update 'penilaian_reviewer_laporan_progres', 'penilaian_reviewer_output', 'penilaian_reviewer_output_bersama'
        // nama kolomnya nya jadi ketambahan '_kegiatan'
        // perlu ubah fk lama ke fk baru
        SevimaSchema::table('litabmas.penilaian_reviewer_laporan_progres', function (SevimaBlueprint $table) use ($tableReviewerKegiatan) {
            $table->renameColumn('id_pengajuan_pendanaan_reviewer', 'id_pengajuan_pendanaan_reviewer_kegiatan');

            // generate fk & index baru
            $table->foreign('id_pengajuan_pendanaan_reviewer_kegiatan', 'litabmas_prlp_id_pengajuan_pendanaan_reviewer_kegiatan_fk')
                ->references('id')->on($tableReviewerKegiatan);
            $table->index('id_pengajuan_pendanaan_reviewer_kegiatan', 'prlp_id_pengajuan_pendanaan_reviewer_kegiatan_idx');

            // drop fk & index lama
            $table->dropForeign('litabmas_prlp_id_pengajuan_pendanaan_reviewer');
        });
        DB::statement('DROP INDEX IF EXISTS litabmas.prlp_id_pengajuan_pendanaan_reviewer_foreign');

        SevimaSchema::table('litabmas.penilaian_reviewer_output', function (SevimaBlueprint $table) use ($tableReviewerKegiatan) {
            $table->renameColumn('id_pengajuan_pendanaan_reviewer', 'id_pengajuan_pendanaan_reviewer_kegiatan');

            // generate fk & index baru
            $table->foreign('id_pengajuan_pendanaan_reviewer_kegiatan', 'litabmas_pro_id_pengajuan_pendanaan_reviewer_kegiatan_fk')
                ->references('id')->on($tableReviewerKegiatan);
            $table->index('id_pengajuan_pendanaan_reviewer_kegiatan', 'pro_id_pengajuan_pendanaan_reviewer_kegiatan_idx');

            // drop fk & index lama
            $table->dropForeign('litabmas_pro_id_pengajuan_pendanaan_reviewer');
        });
        DB::statement('DROP INDEX IF EXISTS litabmas.pro_id_pengajuan_pendanaan_reviewer_foreign');

        SevimaSchema::table('litabmas.penilaian_reviewer_output_bersama', function (SevimaBlueprint $table) use ($tableReviewerKegiatan) {
            // generate fk & index baru
            $table->foreign('id_pengajuan_pendanaan_reviewer_pembuat', 'litabmas_prob_id_reviewer_kegiatan_pembuat_fk')
                ->references('id')->on($tableReviewerKegiatan);
            $table->index('id_pengajuan_pendanaan_reviewer_pembuat', 'prob_id_pengajuan_pendanaan_reviewer_kegiatan_pembuat_idx');
            $table->foreign('id_pengajuan_pendanaan_reviewer_pengubah', 'litabmas_prob_id_reviewer_kegiatan_pengubah_fk')
                ->references('id')->on($tableReviewerKegiatan);
            $table->index('id_pengajuan_pendanaan_reviewer_pengubah', 'prob_id_pengajuan_pendanaan_reviewer_kegiatan_pengubah_idx');

            // drop fk & index lama
            $table->dropForeign('litabmas_prob_id_pengajuan_pendanaan_reviewer');
            $table->dropForeign('litabmas_prob_id_pengajuan_pendanaan_reviewer_pengubah');
        });
        DB::statement('DROP INDEX IF EXISTS litabmas.prob_id_pengajuan_pendanaan_reviewer_foreign');
        DB::statement('DROP INDEX IF EXISTS litabmas.prob_id_pengajuan_pendanaan_reviewer_pengubah_foreign');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableReviewerKegiatan = 'litabmas.pengajuan_pendanaan_reviewer_kegiatan';

        // STEP 1: rollback 'penilaian_reviewer_isian_proposal', 'penilaian_reviewer_komposisi_proposal', 'penilaian_reviewer_komposisi_proposal'
        // nama kolomnya nya jadi ketambahan '_administrasi'
        // tidak perlu ubah relasinya, karena di migration sblmnya ubah nama table, otomatis semua relasi ke table ini juga ikut berubah
        SevimaSchema::table('litabmas.penilaian_reviewer_isian_proposal', function (SevimaBlueprint $table) {
            $table->renameColumn('id_pengajuan_pendanaan_reviewer_administrasi', 'id_pengajuan_pendanaan_reviewer');
        });
        SevimaSchema::table('litabmas.penilaian_reviewer_komposisi_proposal', function (SevimaBlueprint $table) {
            $table->renameColumn('id_pengajuan_pendanaan_reviewer_administrasi', 'id_pengajuan_pendanaan_reviewer');
        });
        DB::statement('DROP INDEX IF EXISTS litabmas.prkp_id_pengajuan_pendanaan_reviewer_administrasi_idx');
        SevimaSchema::table('litabmas.penilaian_reviewer_presentasi_proposal', function (SevimaBlueprint $table) {
            $table->renameColumn('id_pengajuan_pendanaan_reviewer_administrasi', 'id_pengajuan_pendanaan_reviewer');
        });

        // STEP 2: rollback 'penilaian_reviewer_laporan_progres', 'penilaian_reviewer_output', 'penilaian_reviewer_output_bersama'
        // nama kolomnya nya jadi ketambahan '_kegiatan'
        // perlu ubah fk lama ke fk baru
        SevimaSchema::table('litabmas.penilaian_reviewer_laporan_progres', function (SevimaBlueprint $table) use ($tableReviewerKegiatan) {
            $table->renameColumn('id_pengajuan_pendanaan_reviewer_kegiatan', 'id_pengajuan_pendanaan_reviewer');

            // generate fk & index baru
            $table->foreign('id_pengajuan_pendanaan_reviewer', 'litabmas_prlp_id_pengajuan_pendanaan_reviewer')
                ->references('id')->on($tableReviewerKegiatan); // masih ke table yg lama karena rollback nya ada di migration sebelumnya
            $table->index('id_pengajuan_pendanaan_reviewer', 'prlp_id_pengajuan_pendanaan_reviewer_foreign');

            // drop fk & index lama
            $table->dropForeign('litabmas_prlp_id_pengajuan_pendanaan_reviewer_kegiatan_fk');
        });
        DB::statement('DROP INDEX IF EXISTS litabmas.prlp_id_pengajuan_pendanaan_reviewer_kegiatan_idx');

        SevimaSchema::table('litabmas.penilaian_reviewer_output', function (SevimaBlueprint $table) use ($tableReviewerKegiatan) {
            $table->renameColumn('id_pengajuan_pendanaan_reviewer_kegiatan', 'id_pengajuan_pendanaan_reviewer');

            // generate fk & index baru
            $table->foreign('id_pengajuan_pendanaan_reviewer', 'litabmas_pro_id_pengajuan_pendanaan_reviewer')
                ->references('id')->on($tableReviewerKegiatan); // masih ke table yg lama karena rollback nya ada di migration sebelumnya
            $table->index('id_pengajuan_pendanaan_reviewer', 'pro_id_pengajuan_pendanaan_reviewer_foreign');

            // drop fk & index lama
            $table->dropForeign('litabmas_pro_id_pengajuan_pendanaan_reviewer_kegiatan_fk');
        });
        DB::statement('DROP INDEX IF EXISTS litabmas.pro_id_pengajuan_pendanaan_reviewer_kegiatan_idx');

        SevimaSchema::table('litabmas.penilaian_reviewer_output_bersama', function (SevimaBlueprint $table) use ($tableReviewerKegiatan) {
            // generate fk & index baru
            $table->foreign('id_pengajuan_pendanaan_reviewer_pembuat', 'litabmas_prob_id_pengajuan_pendanaan_reviewer')
                ->references('id')->on($tableReviewerKegiatan); // masih ke table yg lama karena rollback nya ada di migration sebelumnya
            $table->index('id_pengajuan_pendanaan_reviewer_pembuat', 'prob_id_pengajuan_pendanaan_reviewer_foreign');
            $table->foreign('id_pengajuan_pendanaan_reviewer_pengubah', 'litabmas_prob_id_pengajuan_pendanaan_reviewer_pengubah')
                ->references('id')->on($tableReviewerKegiatan); // masih ke table yg lama karena rollback nya ada di migration sebelumnya
            $table->index('id_pengajuan_pendanaan_reviewer_pengubah', 'prob_id_pengajuan_pendanaan_reviewer_pengubah_foreign');

            // drop fk & index lama
            $table->dropForeign('litabmas_prob_id_reviewer_kegiatan_pembuat_fk');
            $table->dropForeign('litabmas_prob_id_reviewer_kegiatan_pengubah_fk');
        });
        DB::statement('DROP INDEX IF EXISTS litabmas.prob_id_pengajuan_pendanaan_reviewer_kegiatan_pembuat_idx');
        DB::statement('DROP INDEX IF EXISTS litabmas.prob_id_pengajuan_pendanaan_reviewer_kegiatan_pengubah_idx');
    }
};
