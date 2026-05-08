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
            // Add column id biodata yang menyetujui biaya pendanaan
            $table->unsignedBigInteger('id_biodata_penyetuju_biaya')->nullable();
            $table->foreign('id_biodata_penyetuju_biaya')->references('id')->on('core.biodata');
            // add column datetime waktu setuju biaya
            $table->timestamp('waktu_setuju_biaya')->nullable();
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
            $table->dropForeign(['id_biodata_penyetuju_biaya']);
            $table->dropColumn('id_biodata_penyetuju_biaya');
            $table->dropColumn('waktu_setuju_biaya');
        });
    }
};
