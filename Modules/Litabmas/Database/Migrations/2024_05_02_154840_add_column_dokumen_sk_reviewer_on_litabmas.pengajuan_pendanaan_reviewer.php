<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\DMS\Models\Dokumen;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('litabmas.pengajuan_pendanaan_reviewer', function (SevimaBlueprint $table) {
            $table->foreignIdTo(Dokumen::class, 'id_dokumen_sk', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.pengajuan_pendanaan_reviewer', function (SevimaBlueprint $table) {
            $table->dropForeign(['id_dokumen_sk']);
            $table->dropColumn('id_dokumen_sk');
        });
    }
};
