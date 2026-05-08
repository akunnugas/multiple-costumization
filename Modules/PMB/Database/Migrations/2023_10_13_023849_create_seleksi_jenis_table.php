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
        SevimaSchema::create('pmb.seleksi_jenis', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_jenis_seleksi');
            $table->string('nama_jenis_seleksi');
            $table->logs(true);
        });

        SevimaSchema::table('pmb.seleksi_jenis', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_jenis_seleksi', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.seleksi_jenis');
    }
};
