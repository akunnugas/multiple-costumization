<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Kerjasama\Models\IndikatorSasaran;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('kerjasama.kerjasama', function (SevimaBlueprint $table) {
            $table->foreignIdTo(IndikatorSasaran::class, 'id_indikator_sasaran', true);
        });
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('kerjasama.kerjasama', function (SevimaBlueprint $table) {
            $table->dropForeignIdFor(IndikatorSasaran::class, 'id_indikator_sasaran');
        });
    }
};
