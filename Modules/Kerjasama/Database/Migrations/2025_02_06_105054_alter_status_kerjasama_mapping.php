<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Kerjasama\Models\StatusKerjasama;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $mappings = [
            'Draft' => '',
            'Aktif' => 'Deal',
            'Tidak Aktif' => 'Batal',
            'Perpanjang' => 'Pending',
            'Kadaluwarsa' => '',
            'Selesai' => ''
        ];

        foreach ($mappings as $new => $old) {
            StatusKerjasama::updateOrCreate(['status_kerjasama' => $new]);
        }
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
