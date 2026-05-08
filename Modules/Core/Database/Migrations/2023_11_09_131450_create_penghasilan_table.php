<?php

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
        SevimaSchema::create('core.penghasilan', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_penghasilan');
            $table->string('nama_penghasilan');
            $table->string('poin_kip')->default(0);
            $table->string('kode_emis')->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('core.penghasilan', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_penghasilan', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.penghasilan');
    }
};
