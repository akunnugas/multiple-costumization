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
        SevimaSchema::table('core.pegawai', function (SevimaBlueprint $table) {
            if (SevimaSchema::hasColumn('core.pegawai', 'id_homebase_dosen')) {
                return;
            }

            $table->unsignedBigInteger('id_homebase_dosen')->nullable();
            $table->foreign('id_homebase_dosen')->references('id')->on('core.unit_kerja');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('core.pegawai', function (SevimaBlueprint $table) {
            $table->dropForeign(['id_homebase_dosen']);
            $table->dropColumn('id_homebase_dosen');
        });
    }
};
