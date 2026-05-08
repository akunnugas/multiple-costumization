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
        //add column to table pengajuan_pendanaan_reviewer_administrasi
        SevimaSchema::table('litabmas.pengajuan_pendanaan_reviewer_administrasi', function (SevimaBlueprint $table) {
            $table->string('rekomendasi_anggaran')->nullable();
            $table->string('mata_uang')->nullable();
            $table->renameColumn('status_penilaian_presentasi', 'status_penilaian_presentasi_proposal');
        });
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //drop column from table pengajuan_pendanaan_reviewer_administrasi
        SevimaSchema::table('litabmas.pengajuan_pendanaan_reviewer_administrasi', function (SevimaBlueprint $table) {
            $table->dropColumn('rekomendasi_anggaran');
            $table->dropColumn('mata_uang');
            $table->renameColumn('status_penilaian_presentasi_proposal', 'status_penilaian_presentasi');
        });
    }
};
