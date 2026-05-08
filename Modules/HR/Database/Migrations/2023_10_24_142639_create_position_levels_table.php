<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
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
        SevimaSchema::create('hr.position_levels', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('code', 5)->comment('Kode');
            $table->string('name')->comment('Nama Pangkat');
            $table->string('emis_code', 5)->nullable()->comment('Kode Emis');
            $table->logs(true);
        });

        DB::statement('CREATE UNIQUE INDEX ON hr.position_levels (code) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('hr.position_levels');
    }
};
