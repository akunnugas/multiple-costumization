<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
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
        SevimaSchema::table('', function (SevimaBlueprint $table) {
            DB::table('kerjasama.jenis_kegiatan')->insert([
                'nama_jenis_kegiatan' => 'Penelitian',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('', function (SevimaBlueprint $table) {

        });
    }
};
