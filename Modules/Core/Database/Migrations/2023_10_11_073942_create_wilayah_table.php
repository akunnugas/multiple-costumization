<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.wilayah', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_wilayah');
            $table->string('nama_wilayah');
            $table->char('level_wilayah', 1);
            $table->unsignedBigInteger('id_parent')->nullable();
            $table->string('kode_dikti')->nullable();
            $table->string('kode_bps')->nullable();
            $table->string('kode_dagri')->nullable();
            $table->string('kode_keuangan')->nullable();
            $table->logs(true);
            $table->index('id_parent');
        });

        SevimaSchema::table('core.wilayah', function (SevimaBlueprint $table) {
            $table->foreign('id_parent')->references('id')->on('core.wilayah');
            $table->uniqueIndex('kode_wilayah', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.wilayah');
    }
};
