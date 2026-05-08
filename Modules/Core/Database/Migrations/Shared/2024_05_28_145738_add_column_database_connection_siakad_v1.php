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
        SevimaSchema::table('klien_config', function (SevimaBlueprint $table) {
            $table->string('nama_db_v1')->nullable();
            $table->string('username_db_v1')->nullable();
            $table->string('password_db_v1')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('klien_config', function (SevimaBlueprint $table) {
            $table->dropColumn('nama_db_v1');
            $table->dropColumn('username_db_v1');
            $table->dropColumn('password_db_v1');
        });
    }
};
