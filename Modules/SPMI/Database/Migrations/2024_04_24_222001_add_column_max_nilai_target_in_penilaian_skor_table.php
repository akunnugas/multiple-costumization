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
        SevimaSchema::table('spmi.penilaian_skor', function (SevimaBlueprint $table) {
            $table->decimal('max_nilai_target', 5, 2)
                ->nullable()
                ->after('nilai_target')
                ->comment('Maksimal Target Skor');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('spmi.penilaian_skor', function (SevimaBlueprint $table) {
            $table->dropColumn('max_nilai_target');
        });
    }
};
