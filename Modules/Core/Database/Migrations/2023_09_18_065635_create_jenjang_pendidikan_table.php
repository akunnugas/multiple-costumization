<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Modules\Core\Services\JenjangPendidikanManagementService;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.jenjang_pendidikan', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_jenjang');
            $table->string('nama_jenjang_en')->nullable();
            $table->string('kode_jenjang', 10);
            $table->unsignedBigInteger('kode_dikti')->nullable();
            $table->boolean('apakah_akademik')->default(false);
            $table->boolean('apakah_pt')->default(false);
            $table->boolean('apakah_pasca')->default(false);
            $table->boolean('apakah_data_default')->default(false);
            $table->string('kode_emis')->nullable();
            $table->string('kode_emis_pasca')->nullable();
            $table->string('kode_emis_dosen')->nullable();
            $table->integer('urutan');
            $table->string('ref_key_siakad')->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('core.jenjang_pendidikan', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_jenjang', true);
        });

        $service = new JenjangPendidikanManagementService;
        $service->syncFromSiakadv1();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.jenjang_pendidikan');
    }
};
