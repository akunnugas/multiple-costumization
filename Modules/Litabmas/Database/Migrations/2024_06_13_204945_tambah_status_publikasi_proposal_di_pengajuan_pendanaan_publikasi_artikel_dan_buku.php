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
        SevimaSchema::table('litabmas.pengajuan_pendanaan_publikasi_artikel', function (SevimaBlueprint $table) {
            $table->string('status_publikasi', 25)->nullable()->comment('Status');
        });

        SevimaSchema::table('litabmas.pengajuan_pendanaan_publikasi_buku', function (SevimaBlueprint $table) {
            $table->string('status_publikasi', 25)->nullable()->comment('Status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.pengajuan_pendanaan_publikasi_artikel', function (SevimaBlueprint $table) {
            $table->dropColumn('status_publikasi');
        });

        SevimaSchema::table('litabmas.pengajuan_pendanaan_publikasi_buku', function (SevimaBlueprint $table) {
            $table->dropColumn('status_publikasi');
        });
    }
};
