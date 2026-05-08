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
        SevimaSchema::create('pmb.gelombang', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_gelombang');
            $table->logs(true);
        });

        SevimaSchema::table('pmb.gelombang', function (SevimaBlueprint $table) {
            $table->uniqueIndex('nama_gelombang', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.gelombang');
    }
};
