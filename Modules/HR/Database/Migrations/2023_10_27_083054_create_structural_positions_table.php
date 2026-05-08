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
        SevimaSchema::create('hr.structural_positions', function (SevimaBlueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->comment('Parent Jabatan Struktural');
            $table->foreign('parent_id')->references('id')->on('hr.structural_positions');
            $table->unsignedBigInteger('organization_id')->nullable()->comment('Unit Kerja');
            $table->foreign('organization_id')->references('id')->on('core.unit_kerja');
            $table->unsignedBigInteger('min_position_level_id')->nullable()->comment('Pangkat Minimal');
            $table->foreign('min_position_level_id')->references('id')->on('hr.position_levels');
            $table->unsignedBigInteger('max_position_level_id')->nullable()->comment('Pangkat Maksimal');
            $table->foreign('structural_position_type_id')->references('id')->on('hr.structural_position_types');
            $table->unsignedBigInteger('structural_position_type_id')->nullable()->comment('Jenis Jabatan Struktural');
            $table->foreign('max_position_level_id')->references('id')->on('hr.position_levels');
            $table->unsignedBigInteger('echelon_id')->nullable()->comment('Eselon');
            $table->foreign('echelon_id')->references('id')->on('hr.echelons');
            $table->string('name')->comment('Nama Jabatan Struktural');
            $table->string('code', 10)->comment('Kode');
            $table->string('email')->nullable()->comment('Email');
            $table->unsignedInteger('depth')->nullable()->comment('Level');
            $table->integer('info_left')->nullable()->comment('Info Left');
            $table->integer('info_right')->nullable()->comment('Info Right');
            $table->string('description')->nullable()->comment('Keterangan');
            $table->boolean('is_active')->default(true)->comment('Status Jabatan Struktural');
            $table->boolean('is_leader')->comment('Pimpinan?');
            $table->string('abbreviation')->nullable()->comment('Singkatan');
            $table->logs(true);

            // create index
            $table->index(['parent_id','info_left','info_right']);
        });

        DB::statement('CREATE UNIQUE INDEX ON hr.structural_positions (code) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('hr.structural_positions');
    }
};
