<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Kerjasama\Data\KriteriaMitraData;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        app(KriteriaMitraData::class)->migrate();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        app(KriteriaMitraData::class)->rollback();
    }
};
