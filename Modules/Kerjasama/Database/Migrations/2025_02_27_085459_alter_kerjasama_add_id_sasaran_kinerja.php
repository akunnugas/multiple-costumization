<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Kerjasama\Models\SasaranKinerja;

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
            $table->foreignIdTo(SasaranKinerja::class, 'id_sasaran_kinerja', true);
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
            $table->dropForeignIdFor(SasaranKinerja::class, 'id_sasaran_kinerja');
        });
    }
};
