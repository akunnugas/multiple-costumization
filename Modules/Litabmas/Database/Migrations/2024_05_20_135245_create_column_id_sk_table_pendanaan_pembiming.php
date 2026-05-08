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
        //add column pengajuan_pendanaan_pembimbing.id_dokumen_sk 
        SevimaSchema::table('litabmas.pengajuan_pendanaan_pembimbing', function (SevimaBlueprint $table) {
            $table->unsignedBigInteger('id_dokumen_sk')->nullable();
            $table->foreign('id_dokumen_sk')->references('id')->on('dms.dokumen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //drop column pengajuan_pendanaan_pembimbing.id_dokumen_sk 
        SevimaSchema::table('litabmas.pengajuan_pendanaan_pembimbing', function (SevimaBlueprint $table) {
            $table->dropForeign(['id_dokumen_sk']);
            $table->dropColumn('id_dokumen_sk');
        });
    }
};
