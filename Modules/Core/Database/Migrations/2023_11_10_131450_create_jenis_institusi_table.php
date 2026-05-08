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
        SevimaSchema::create('core.jenis_institusi', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_jenis_institusi');
            $table->string('nama_jenis_institusi');
            $table->foreignIdTo(JenjangPendidikan::class);
            $table->logs(true);
        });

        SevimaSchema::table('core.jenis_institusi', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_jenis_institusi', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.jenis_institusi');
    }
};
