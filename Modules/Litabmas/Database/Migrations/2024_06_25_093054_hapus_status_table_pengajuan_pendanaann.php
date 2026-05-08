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
        SevimaSchema::table('litabmas.pengajuan_pendanaan', function (SevimaBlueprint $table) {
            //remove status column on pengajuan_pendanaaan
            $table->dropColumn('apakah_lolos_nominasi');
            $table->dropColumn('waktu_lolos_nominasi');
            $table->dropColumn('lolos_nominasi_oleh');

            //remove status lolos pendanaan column on pengajuan_pendanaaan
            $table->dropColumn('apakah_lolos_pendanaan');
            $table->dropColumn('waktu_lolos_pendanaan');
            $table->dropColumn('lolos_pendanaan_oleh');
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
            //add status column on pengajuan_pendanaaan
            $table->boolean('apakah_lolos_nominasi')->nullable();
            $table->timestamp('waktu_lolos_nominasi')->nullable();
            $table->unsignedBigInteger('lolos_nominasi_oleh')->nullable();

            //add status lolos pendanaan column on pengajuan_pendanaaan
            $table->boolean('apakah_lolos_pendanaan')->nullable();
            $table->timestamp('waktu_lolos_pendanaan')->nullable();
            $table->unsignedBigInteger('lolos_pendanaan_oleh')->nullable();
        });
    }
};
