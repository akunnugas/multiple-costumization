<?php

use Modules\Core\Extensions\Blueprint;
use Modules\Core\Extensions\Schema;
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
        DB::statement('DROP SCHEMA IF EXISTS spmi CASCADE');
        DB::statement('CREATE SCHEMA spmi');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP SCHEMA spmi CASCADE');
    }
};
