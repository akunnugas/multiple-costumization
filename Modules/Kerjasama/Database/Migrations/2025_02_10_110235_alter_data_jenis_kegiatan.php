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
        DB::table('kerjasama.jenis_kegiatan')
            ->where('nama_jenis_kegiatan', 'Lain-lain')
            ->update([
                'nama_jenis_kegiatan' => 'Pendidikan'
            ]);
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
