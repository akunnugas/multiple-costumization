<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Kerjasama\Data\SasaranKinerjaData;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        app(SasaranKinerjaData::class)->migrate();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(SasaranKinerjaData $data)
    {
        app(SasaranKinerjaData::class)->rollback();
    }
};
