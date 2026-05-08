<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.bank', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_bank', 5);
            $table->string('nama_bank', 100);
            $table->logs(true);
        });

        SevimaSchema::table('core.bank', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_bank', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.banks');
    }
};
