<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\SistemKuliah;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.sistem_kuliah', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_sistem');
            $table->string('deskripsi_sistem')->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('core.sistem_kuliah', function (SevimaBlueprint $table) {
            $table->uniqueIndex('nama_sistem', true);
        });

        $lectureSystems = [
            [
                'nama_sistem' => 'Reguler',
                'deskripsi_sistem' => null,
            ],
            [
                'nama_sistem' => 'Karyawan',
                'deskripsi_sistem' => null,
            ],
        ];
        foreach ($lectureSystems as $lectureSystem) {
            SistemKuliah::create($lectureSystem);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.sistem_kuliah');
    }
};
