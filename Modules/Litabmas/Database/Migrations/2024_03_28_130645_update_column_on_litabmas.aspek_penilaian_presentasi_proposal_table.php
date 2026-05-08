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
        SevimaSchema::table('litabmas.aspek_penilaian_presentasi_proposal', function (SevimaBlueprint $table) {
            $table->unsignedInteger('no')->nullable();
        });

        SevimaSchema::table('litabmas.aspek_penilaian_presentasi_proposal', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_periode_pendanaan', 'kode_jenis_pendanaan', 'no'],
                uniqueIndexName: 'appp_periode_pendanaan_jenis_pendanaan_no_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.aspek_penilaian_presentasi_proposal', function (SevimaBlueprint $table) {
            $table->dropColumn('no');
        });
    }
};
