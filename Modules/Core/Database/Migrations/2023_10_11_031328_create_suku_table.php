<?php

use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.suku', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_suku');
            $table->logs(true);
        });

        SevimaSchema::table('core.suku', function (SevimaBlueprint $table) {
            $table->uniqueIndex('nama_suku', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.suku');
    }
};
