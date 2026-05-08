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
        SevimaSchema::create('perguruan_tinggi', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_pt');
            $table->string('nama_pt');
            $table->string('alamat_pt')->nullable();
            $table->string('telepon_pt')->nullable();
            $table->string('kode_sister')->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('perguruan_tinggi', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_pt', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('perguruan_tinggi');
    }
};
