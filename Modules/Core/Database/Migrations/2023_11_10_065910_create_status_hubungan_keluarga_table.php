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
        SevimaSchema::create('core.status_hubungan_keluarga', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_status_keluarga');
            $table->string('nama_status_keluarga');
            $table->logs(true);
        });

        SevimaSchema::table('core.status_hubungan_keluarga', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_status_keluarga', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.status_hubungan_keluarga');
    }
};
