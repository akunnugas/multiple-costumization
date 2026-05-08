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
        SevimaSchema::table('litabmas.pengajuan_pendanaan_aktivitas_penelitian', function (SevimaBlueprint $table) {
            $table->renameColumn('nama_aktivitas_peneitian', 'nama_aktivitas_penelitian');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.pengajuan_pendanaan_aktivitas_penelitian', function (SevimaBlueprint $table) {
            $table->renameColumn('nama_aktivitas_penelitian', 'nama_aktivitas_peneitian');
        });
    }
};
