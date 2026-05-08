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
        SevimaSchema::create('pmb.seleksi_komponen', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_komponen', 20);
            $table->string('nama_komponen', 100);
            $table->logs(true);
        });

        SevimaSchema::table('pmb.seleksi_komponen', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_komponen', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.seleksi_komponen');
    }
};
