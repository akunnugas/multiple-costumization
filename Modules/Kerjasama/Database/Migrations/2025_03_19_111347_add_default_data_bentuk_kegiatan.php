<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Kerjasama\Data\BentukKegiatanData;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        app(BentukKegiatanData::class)->migrate();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        app(BentukKegiatanData::class)->rollback();
    }
};
