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
        SevimaSchema::table('spmi.data_pengisian_lk', function (SevimaBlueprint $table) {
            $table->json('data_pengisian_lk_awal')->nullable()->comment('Hanya untuk keperluan perbaikan data, tidak boleh digunakan untuk keperluan lainnya');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('spmi.data_pengisian_lk', function (SevimaBlueprint $table) {
            $table->dropColumn('data_pengisian_lk_awal');
        });
    }
};
