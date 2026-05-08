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
        SevimaSchema::create('core.jenis_perguruan_tinggi', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_jenis_pt', 10);
            $table->string('nama_jenis_pt');
            $table->string('kategori')->nullable();
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
        SevimaSchema::dropIfExists('core.jenis_perguruan_tinggi');
    }
};
