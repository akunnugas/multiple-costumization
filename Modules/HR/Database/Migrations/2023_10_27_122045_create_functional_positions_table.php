<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
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
        SevimaSchema::create('hr.functional_positions', function (SevimaBlueprint $table) {
            $table->id();
            $table->unsignedBigInteger('academic_position_id')->nullable()->comment('ID Jabatan Akademik');
            $table->foreign('academic_position_id')->references('id')->on('hr.academic_positions');
            $table->unsignedBigInteger('position_level_id')->nullable()->nullable()->comment('ID Pangkat');
            $table->foreign('position_level_id')->references('id')->on('hr.position_levels');
            $table->string('code', 5)->comment('Kode');
            $table->string('name')->comment('Nama Jabatan Fungsional');
            $table->integer('credit_number')->comment('Angka Kredit');
            $table->integer('retirement_age')->comment('Usia Pensiun');
            $table->string('position_emis_code', 5)->nullable()->comment('Kode EMIS Jabatan Fungsional');
            $table->integer('dikti_id')->nullable()->comment('ID DIKTI');
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
        SevimaSchema::dropIfExists('hr.functional_positions');
    }
};
