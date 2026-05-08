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
        SevimaSchema::create('core.agama', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_agama', 10)->nullable();
            $table->string('nama_agama', 100);
            $table->integer('kode_dikti')->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('core.agama', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_agama', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.agama');
    }
};
