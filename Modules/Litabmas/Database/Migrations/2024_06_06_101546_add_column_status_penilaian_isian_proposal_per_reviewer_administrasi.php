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
        SevimaSchema::table('litabmas.pengajuan_pendanaan_reviewer_administrasi', function (SevimaBlueprint $table) {
            $table->string('status_penilaian_isian_proposal', 25)->nullable()
                ->comment('Status Penilaian Isian Proposal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.pengajuan_pendanaan_reviewer_administrasi', function (SevimaBlueprint $table) {
            $table->dropColumn('status_penilaian_isian_proposal');
        });
    }
};
