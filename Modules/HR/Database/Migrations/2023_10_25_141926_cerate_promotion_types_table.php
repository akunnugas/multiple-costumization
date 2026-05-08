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
        SevimaSchema::create('hr.promotion_types', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('code', 5)->comment('Kode');
            $table->string('name')->comment('Nama Jenis Kenaikan Pangkat');

            $table->logs(true);
        });

        DB::statement('CREATE UNIQUE INDEX ON hr.promotion_types (code) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('hr.promotion_types');
    }
};
