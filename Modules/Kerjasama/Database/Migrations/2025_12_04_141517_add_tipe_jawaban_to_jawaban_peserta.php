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
        SevimaSchema::table('kerjasama.jawaban_peserta', function (SevimaBlueprint $table) {
            $table->string('tipe_jawaban')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('kerjasama.jawaban_peserta', function (SevimaBlueprint $table) {
            $table->dropColumn('tipe_jawaban');
        });
    }
};
