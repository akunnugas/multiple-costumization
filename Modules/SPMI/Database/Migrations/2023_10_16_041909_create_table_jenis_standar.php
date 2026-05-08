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
        SevimaSchema::create('spmi.jenis_standar', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_jenis_standar');
            $table->string('kode_jenis_standar', 5);

            $table->logs(true);
        });

        DB::statement('CREATE UNIQUE INDEX ON spmi.jenis_standar (kode_jenis_standar) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('spmi.jenis_standar');
    }
};
