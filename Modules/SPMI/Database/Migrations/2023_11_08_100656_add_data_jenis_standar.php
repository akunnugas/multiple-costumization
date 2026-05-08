<?php

use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Extensions\SevimaBlueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\SPMI\Models\JenisStandar;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // add data to table jenis_standar
        JenisStandar::insert([
            ['kode_jenis_standar' => '9S', 'nama_jenis_standar' => '9 Standar']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // delete data from table jenis_standar
        JenisStandar::truncate();
    }
};
