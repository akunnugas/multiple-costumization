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
        SevimaSchema::create('hr.structural_position_types', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('sister_id')->nullable()->comment('ID sister');
            $table->string('code', 5)->comment('Kode');
            $table->string('name')->comment('Nama Jenis Jabatan Struktural');
            $table->string('emis_code', 5)->nullable()->comment('Kode Emis');
            $table->logs(true);
        });

        DB::statement('CREATE UNIQUE INDEX ON hr.structural_position_types (code) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('hr.structural_position_types');
    }
};
