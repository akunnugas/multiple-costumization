<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\PenilaianMatriks;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('spmi.penilaian_matriks', function (SevimaBlueprint $table) {
            $table->boolean('butir_indikator_spme')->default(false)->after('is_aktif')->comment('Menandai apakah butir indikator ini berasal dari SPME');
        });

        PenilaianMatriks::where('apakah_data_default', true)->update(['butir_indikator_spme' => true]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('spmi.penilaian_matriks', function (SevimaBlueprint $table) {
            $table->dropColumn('butir_indikator_spme');
        });
    }
};
