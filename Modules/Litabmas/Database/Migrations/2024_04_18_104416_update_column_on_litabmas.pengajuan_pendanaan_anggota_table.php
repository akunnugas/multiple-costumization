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
        SevimaSchema::table('litabmas.pengajuan_pendanaan_anggota', function (SevimaBlueprint $table) {
            $table->boolean('apakah_undangan_diterima')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.pengajuan_pendanaan_anggota', function (SevimaBlueprint $table) {
            $table->boolean('apakah_undangan_diterima')->nullable(false)->default(false)->change();
        });
    }
};
