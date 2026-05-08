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
        SevimaSchema::create('pmb.syarat', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_syarat', 10);
            $table->string('nama_syarat');
            $table->decimal('poin_syarat', 5, 2, true);
            $table->logs();
        });

        SevimaSchema::table('pmb.syarat', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_syarat', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.syarat');
    }
};
