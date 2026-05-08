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
        SevimaSchema::table('kerjasama.kerjasama', function (SevimaBlueprint $table) {
            $table->foreignIdTo(Kerjasama::class, 'id_parent', true);
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
            $table->dropForeignIdFor(Kerjasama::class, 'id_parent');
        });
    }
};
