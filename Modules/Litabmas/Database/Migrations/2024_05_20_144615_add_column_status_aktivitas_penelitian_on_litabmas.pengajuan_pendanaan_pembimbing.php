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
        // drop old columns
        SevimaSchema::table('litabmas.pengajuan_pendanaan_status', function (SevimaBlueprint $table) {
            $table->dropColumn('status_bimbingan_logbook');
        });

        // add column to pengajuan_pendanaan_pembimbing
        SevimaSchema::table('litabmas.pengajuan_pendanaan_pembimbing', function (SevimaBlueprint $table) {
            $table->string('status_bimbingan_logbook')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.pengajuan_pendanaan_status', function (SevimaBlueprint $table) {
            $table->string('status_bimbingan_logbook')->nullable();
        });

        SevimaSchema::table('litabmas.pengajuan_pendanaan_pembimbing', function (SevimaBlueprint $table) {
            $table->dropColumn('status_bimbingan_logbook');
        });
    }
};
