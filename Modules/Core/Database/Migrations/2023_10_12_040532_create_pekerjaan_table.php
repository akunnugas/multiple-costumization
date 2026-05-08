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
        SevimaSchema::create('core.pekerjaan', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_pekerjaan');
            $table->string('kode_emis', 2)->nullable();
            $table->string('kode_emis_siswa', 2)->nullable();
            $table->string('kode_emis_lulusan', 2)->nullable();
            $table->unsignedBigInteger('kode_sister')->nullable();
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
        SevimaSchema::dropIfExists('core.pekerjaan');
    }
};
