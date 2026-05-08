<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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
        SevimaSchema::create('pmb.mata_pelajaran', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_mata_pelajaran');
            $table->decimal('nilai_minimal_lulus', 5, 2, true)->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('pmb.mata_pelajaran', function (SevimaBlueprint $table) {
            $table->uniqueIndex('nama_mata_pelajaran', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.mata_pelajaran');
    }
};
