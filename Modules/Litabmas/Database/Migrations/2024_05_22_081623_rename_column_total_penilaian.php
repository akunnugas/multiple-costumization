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
        SevimaSchema::table('litabmas.pengajuan_pendanaan', function (SevimaBlueprint $table) {
            $table->renameColumn('total_penilaian_komposisi_proposal', 'total_nilai_komposisi_proposal');
            $table->renameColumn('total_penilaian_presentasi_proposal', 'total_nilai_presentasi_proposal');
        });

        SevimaSchema::table('litabmas.pengajuan_pendanaan_reviewer', function (SevimaBlueprint $table) {
            $table->renameColumn('total_penilaian_komposisi_proposal', 'total_nilai_komposisi_reviewer');
            $table->renameColumn('total_penilaian_presentasi_proposal', 'total_nilai_presentasi_reviewer');
        });
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.pengajuan_pendanaan', function (SevimaBlueprint $table) {
            $table->renameColumn('total_nilai_komposisi_proposal', 'total_penilaian_komposisi_proposal');
            $table->renameColumn('total_nilai_presentasi_proposal', 'total_penilaian_presentasi_proposal');
        });

        SevimaSchema::table('litabmas.pengajuan_pendanaan_reviewer', function (SevimaBlueprint $table) {
            $table->renameColumn('total_nilai_komposisi_reviewer', 'total_penilaian_komposisi_proposal');
            $table->renameColumn('total_nilai_presentasi_reviewer', 'total_penilaian_presentasi_proposal');
        });
    }
};
