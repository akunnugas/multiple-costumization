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
        SevimaSchema::table('litabmas.aspek_penilaian_output_pertanyaan', function (SevimaBlueprint $table) {
            $table->unsignedInteger('no')->nullable();
        });

        SevimaSchema::table('litabmas.aspek_penilaian_output_pertanyaan', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_periode_pendanaan', 'no'], true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.aspek_penilaian_output_pertanyaan', function (SevimaBlueprint $table) {
            $table->dropColumn('no');
        });
    }
};
