<?php

use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Extensions\SevimaBlueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\SPMI\Models\AkreditasiBuku;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // add data to table accreditation_books
        // AkreditasiBuku::insert([
        //     [
        //         'code' => '3',
        //         'name' => 'Buku AIPT',
        //         'type' => 'pr',
        //     ],
        //     [
        //         'code' => '3A',
        //         'name' => 'Buku 3A',
        //         'type' => 'pr',
        //     ],
        //     [
        //         'code' => '3B',
        //         'name' => 'Buku 3B',
        //         'type' => 'pr',
        //     ],
        //     [
        //         'code' => 'DKPS',
        //         'name' => 'Dokumen Kinerja Program Studi',
        //         'type' => 'pr',
        //     ],
        //     [
        //         'code' => 'ISK',
        //         'name' => 'Instrumen Suplemen Konversi',
        //         'type' => 'pr',
        //     ],
        //     [
        //         'code' => 'LED',
        //         'name' => 'Laporan Evaluasi Diri',
        //         'type' => 'se',
        //     ],
        //     [
        //         'code' => 'LKPS',
        //         'name' => 'Laporan Kinerja Program Studi',
        //         'type' => 'pr',
        //     ],
        //     [
        //         'code' => 'LKPT',
        //         'name' => 'Laporan Kinerja Perguruan Tinggi',
        //         'type' => 'pr',
        //     ]
        // ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // delete data from table accreditation_books
        AkreditasiBuku::truncate();
    }
};
