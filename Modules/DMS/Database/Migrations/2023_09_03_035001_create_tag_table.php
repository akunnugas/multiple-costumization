<?php

use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Extensions\SevimaBlueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Jenis dokumen
        SevimaSchema::create('dms.tag', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_tag', 100)->comment('Nama Tag');
            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('dms.tag');
    }
};
