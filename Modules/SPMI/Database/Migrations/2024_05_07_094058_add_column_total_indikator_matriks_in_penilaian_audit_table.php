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
        SevimaSchema::table('spmi.penilaian_audit', function (SevimaBlueprint $table) {
            $table->integer('total_indikator_matriks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('spmi.penilaian_audit', function (SevimaBlueprint $table) {
            $table->dropColumn('total_indikator_matriks');
        });
    }
};
