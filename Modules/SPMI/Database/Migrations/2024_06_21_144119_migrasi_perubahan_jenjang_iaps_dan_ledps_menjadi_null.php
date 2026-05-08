<?php

use Illuminate\Database\Migrations\Migration;
use Modules\SPMI\Models\PengisianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        PengisianPanduan::whereIn('kode_pengisian_panduan', ['LEDPS9', 'IAPS9'])
            ->update(['id_jenjang_pendidikan' => null]);
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
