<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Litabmas\Models\PengajuanPendanaan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('litabmas.penilaian_reviewer_output_bersama', function (SevimaBlueprint $table) {
            // rename column 'id_pengajuan_pendanaan_reviewer' to 'id_pengajuan_pendanaan_reviewer_pembuat'
            $table->renameColumn('id_pengajuan_pendanaan_reviewer', 'id_pengajuan_pendanaan_reviewer_pembuat');

            // add column yg ngubah juga
            $table->foreignIdTo(
                null,
                'id_pengajuan_pendanaan_reviewer_pengubah',
                nullable: true,
                foreignName: 'litabmas_prob_id_pengajuan_pendanaan_reviewer_pengubah',
                indexName: 'prob_id_pengajuan_pendanaan_reviewer_pengubah_foreign',
                table: 'litabmas.pengajuan_pendanaan_reviewer'
            );

            $table->foreignIdTo(
                PengajuanPendanaan::class,
                'id_pengajuan_pendanaan',
                nullable: true, // utk handle data lama aja secara be required
                foreignName: 'litabmas_prob_id_pengajuan_pendanaan',
                indexName: 'prob_id_pengajuan_pendanaan_foreign'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.penilaian_reviewer_output_bersama', function (SevimaBlueprint $table) {
            // rename column 'id_pengajuan_pendanaan_reviewer_pembuat' to 'id_pengajuan_pendanaan_reviewer'
            $table->renameColumn('id_pengajuan_pendanaan_reviewer_pembuat', 'id_pengajuan_pendanaan_reviewer');

            // drop column yg ditambahin
            $table->dropColumn('id_pengajuan_pendanaan_reviewer_pengubah');
            $table->dropColumn('id_pengajuan_pendanaan');
        });
    }
};
