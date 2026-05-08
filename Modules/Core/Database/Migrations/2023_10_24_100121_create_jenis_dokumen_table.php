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
        SevimaSchema::create('core.jenis_dokumen', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_jenis_dokumen');
            $table->string('kode_jenis_dokumen', 10);
            $table->boolean('apakah_statis')->default(false);
            $table->logs(true);
        });

        SevimaSchema::table('core.jenis_dokumen', function (SevimaBlueprint $table) {
            $table->uniqueIndex('nama_jenis_dokumen', true);
            $table->uniqueIndex('kode_jenis_dokumen', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.jenis_dokumen');
    }
};
