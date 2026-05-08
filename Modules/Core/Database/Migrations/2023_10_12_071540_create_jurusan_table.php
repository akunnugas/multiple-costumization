<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Modules\Core\Models\JenjangPendidikan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.jurusan', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_jurusan', 20);
            $table->string('nama_jurusan', 100);
            $table->foreignIdTo(JenjangPendidikan::class);
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
        SevimaSchema::dropIfExists('core.jurusan');
    }
};
