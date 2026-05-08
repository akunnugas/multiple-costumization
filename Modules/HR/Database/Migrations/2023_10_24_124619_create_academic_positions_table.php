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
        SevimaSchema::create('hr.academic_positions', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('code', 5)->comment('Kode');
            $table->string('name')->comment('Nama Jabatan Akademik');
            $table->string('emis_code', 5)->nullable()->comment('Kode EMIS');
            $table->integer('academic_type')->comment('Jenis Jabatan Akademik');
            $table->logs(true);
        });

        DB::statement('CREATE UNIQUE INDEX ON hr.academic_positions (code) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('hr.academic_positions');
    }
};
