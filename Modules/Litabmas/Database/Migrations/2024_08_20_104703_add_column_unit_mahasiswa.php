<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Services\MahasiswaManagementService;


return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('core.mahasiswa', function (SevimaBlueprint $table) {
            if (SevimaSchema::hasColumn('core.mahasiswa', 'id_unit')) {
                return;
            }

            $table->unsignedBigInteger('id_unit')->nullable()->after('ref_key_siakad');
            $table->foreign('id_unit')->references('id')->on('core.unit_kerja');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('core.mahasiswa', function (SevimaBlueprint $table) {
            $table->dropForeign(['id_unit']);
            $table->dropColumn('id_unit');
        });
    }
};
