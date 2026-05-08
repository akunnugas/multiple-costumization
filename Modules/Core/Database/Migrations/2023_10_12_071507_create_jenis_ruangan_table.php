<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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
        SevimaSchema::create('core.jenis_ruangan', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_jenis_ruangan');
            $table->string('nama_jenis_ruangan');
            $table->logs(true);
        });

        SevimaSchema::table('core.jenis_ruangan', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_jenis_ruangan', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.jenis_ruangan');
    }
};
