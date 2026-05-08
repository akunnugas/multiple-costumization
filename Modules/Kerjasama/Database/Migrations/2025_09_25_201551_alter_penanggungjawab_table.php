<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Kerjasama\Models\Kerjasama;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('kerjasama.penanggung_jawab', function (SevimaBlueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('telepon')->nullable()->change();
            $table->string('jabatan')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('kerjasama.penanggung_jawab', function (SevimaBlueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->string('telepon')->nullable(false)->change();
            $table->string('jabatan')->nullable(false)->change();
        });
    }
};