<?php

use Modules\Core\Extensions\Schema;
use Modules\Core\Extensions\Blueprint;
use Illuminate\Database\Migrations\Migration;
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
        DB::statement('DROP SCHEMA IF EXISTS dms CASCADE');
        DB::statement('CREATE SCHEMA dms');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP SCHEMA dms CASCADE');
    }
};
