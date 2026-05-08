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
        SevimaSchema::create('pmb.syarat_jenis', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_jenis_syarat', 10);
            $table->string('nama_jenis_syarat');
            $table->logs(true);
        });

        SevimaSchema::table('pmb.syarat_jenis', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_jenis_syarat', true);
        });

        $defaultRequirementTypes = [
            ['kode_jenis_syarat' => 'ADM', 'nama_jenis_syarat' => 'Administrasi'],
            ['kode_jenis_syarat' => 'BM', 'nama_jenis_syarat' => 'Bidik Misi'],
            ['kode_jenis_syarat' => 'DFU', 'nama_jenis_syarat' => 'Daftar Ulang'],
            ['kode_jenis_syarat' => 'UKT', 'nama_jenis_syarat' => 'Uang Kuliah Tunggal'],
        ];

        foreach ($defaultRequirementTypes as $defaultRequirementType) {
            DB::table('pmb.syarat_jenis')->insert($defaultRequirementType);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.syarat_jenis');
    }
};
