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
        SevimaSchema::create('core.lembaga_akreditasi', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_lembaga');
            $table->string('kode_lembaga', 10);
            $table->string('nama_singkat_lembaga', 25)->nullable();
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
        SevimaSchema::dropIfExists('core.lembaga_akreditasi');
    }
};
