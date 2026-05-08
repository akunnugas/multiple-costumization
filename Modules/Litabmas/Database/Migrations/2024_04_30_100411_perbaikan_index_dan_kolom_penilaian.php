<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //remove column and constraint id_pengajuan_pendanaan from table penilaian_reviewer_komposisi_proposal
        SevimaSchema::table('litabmas.penilaian_reviewer_komposisi_proposal', function (SevimaBlueprint $table) {
            $table->dropForeign(['id_pengajuan_pendanaan']);
            $table->dropColumn('id_pengajuan_pendanaan');
        });

        //add column id_pengajuan_pendanaan_reviewer to table penilaian_reviewer_komposisi_proposal
        //add constraint foreign key to table pengajuan_pendanaan_reviewer.id_pengajuan_pendanaan
        SevimaSchema::table('litabmas.penilaian_reviewer_komposisi_proposal', function (SevimaBlueprint $table) {
            $table->unsignedBigInteger('id_pengajuan_pendanaan_reviewer')->nullable()
                ->after('id_aspek_penilaian_komposisi_proposal');
            $table->foreign('id_pengajuan_pendanaan_reviewer')
                ->references('id')
                ->on('litabmas.pengajuan_pendanaan_reviewer');
        });

        //change on table pengajuan_pendanaan_reviewer
        SevimaSchema::table('litabmas.pengajuan_pendanaan_reviewer', function (SevimaBlueprint $table) {
            $table->decimal('total_penilaian_komposisi_proposal', 5, 2)->nullable()->change();
            $table->decimal('total_penilaian_presentasi_proposal', 5, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove column and constraint id_pengajuan_pendanaan_reviewer from table penilaian_reviewer_komposisi_proposal
        SevimaSchema::table('litabmas.penilaian_reviewer_komposisi_proposal', function (SevimaBlueprint $table) {
            $table->dropForeign(['id_pengajuan_pendanaan_reviewer']);
            $table->dropColumn('id_pengajuan_pendanaan_reviewer');
        });

        // Restore column id_pengajuan_pendanaan and constraint foreign key to table penilaian_reviewer_komposisi_proposal
        SevimaSchema::table('litabmas.penilaian_reviewer_komposisi_proposal', function (SevimaBlueprint $table) {
            $table->unsignedBigInteger('id_pengajuan_pendanaan')->nullable()
                ->after('id_aspek_penilaian_komposisi_proposal');
            $table->foreign('id_pengajuan_pendanaan')
                ->references('id')
                ->on('litabmas.pengajuan_pendanaan');
        });

        // Revert changes on table pengajuan_pendanaan_reviewer
        SevimaSchema::table('litabmas.pengajuan_pendanaan_reviewer', function (SevimaBlueprint $table) {
            $table->decimal('total_penilaian_komposisi_proposal', 5, 2)->nullable(false)->change();
            $table->decimal('total_penilaian_presentasi_proposal', 5, 2)->nullable(false)->change();
        });
    }
};
