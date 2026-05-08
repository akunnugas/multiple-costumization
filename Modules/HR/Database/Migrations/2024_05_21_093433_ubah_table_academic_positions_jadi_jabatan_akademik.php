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
        // rename table
        DB::statement('ALTER TABLE hr.academic_positions RENAME TO jabatan_akademik');

        // rename column
        SevimaSchema::table('hr.jabatan_akademik', function (SevimaBlueprint $table) {
            $table->renameColumn('code', 'kode_jabatan_akademik');
            $table->renameColumn('name', 'nama_jabatan_akademik');
            $table->renameColumn('emis_code', 'kode_emis');
            $table->renameColumn('academic_type', 'jenis_jabatan_akademik');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // rename table
        DB::statement('ALTER TABLE hr.jabatan_akademik RENAME TO academic_positions');

        // rename column
        SevimaSchema::table('hr.academic_positions', function (SevimaBlueprint $table) {
            $table->renameColumn('kode_jabatan_akademik', 'code');
            $table->renameColumn('nama_jabatan_akademik', 'name');
            $table->renameColumn('kode_emis', 'emis_code');
            $table->renameColumn('jenis_jabatan_akademik', 'academic_type');
        });
    }
};
