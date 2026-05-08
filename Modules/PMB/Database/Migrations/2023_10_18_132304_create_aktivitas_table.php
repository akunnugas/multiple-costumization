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
        SevimaSchema::create('pmb.aktivitas', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_aktivitas');
            $table->string('kode_aktivitas', 10);
            $table->unsignedInteger('urutan_aktivitas');
            $table->logs(true);
        });

        SevimaSchema::table('pmb.aktivitas', function (SevimaBlueprint $table) {
            $table->uniqueIndex('nama_aktivitas', true);
            $table->uniqueIndex('kode_aktivitas', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.aktivitas');
    }
};
