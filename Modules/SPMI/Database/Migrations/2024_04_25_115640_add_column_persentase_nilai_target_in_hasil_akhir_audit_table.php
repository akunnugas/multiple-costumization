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
        SevimaSchema::table('spmi.hasil_akhir_audit', function (SevimaBlueprint $table) {
            $table->decimal('persentase_nilai_target', 5, 2)
                ->nullable()
                ->after('persentase_nilai_akhir')
                ->comment('Persentase Nilai Target');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('spmi.hasil_akhir_audit', function (SevimaBlueprint $table) {
            $table->dropColumn('persentase_nilai_target');
        });
    }
};
