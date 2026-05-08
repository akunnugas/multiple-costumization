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
        SevimaSchema::create('core.perguruan_tinggi', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_pt', 20);
            $table->string('nama_pt', 100);
            $table->string('alamat_pt')->nullable();
            $table->string('telepon_pt')->nullable();
            $table->string('ref_key_siakad')->nullable();
            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.perguruan_tinggi');
    }
};
