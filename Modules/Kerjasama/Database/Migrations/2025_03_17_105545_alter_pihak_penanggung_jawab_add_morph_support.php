<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Kerjasama\Models\Kerjasama;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('kerjasama.pihak_penanggung_jawab', function (SevimaBlueprint $table) {
           $table->dropConstrainedForeignIdFor(Kerjasama::class, 'id_kerjasama');
           
           $table->string('model')->after('id');
           $table->string('model_id')->after('model');
        });
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
