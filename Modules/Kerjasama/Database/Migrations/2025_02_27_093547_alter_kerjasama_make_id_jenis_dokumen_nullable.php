<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Kerjasama\Models\JenisDokumen;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('kerjasama.kerjasama', function (SevimaBlueprint $table) {
            $table->foreignId('id_jenis_dokumen')->nullable()->unsigned()->change();
            $table->foreignId('id_status_kerjasama')->nullable()->unsigned()->change();
            $table->text('deskripsi')->nullable()->change();

        });
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
