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
        SevimaSchema::create('litabmas.tema_kegiatan', function (SevimaBlueprint $table) {
            $table->id()->comment('ID Tema Kegiatan');
            $table->string('nama_tema')->comment('Nama Tema Kegiatan');
            $table->logs(true);
        });


        // unique name
        DB::statement('CREATE UNIQUE INDEX ON litabmas.tema_kegiatan (nama_tema) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('litabmas.tema_kegiatan');
    }
};
