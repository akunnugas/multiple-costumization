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
        SevimaSchema::create('hr.work_relations', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('code', 5)->nullable()->comment('Kode');
            $table->string('name')->comment('Nama Hubungan Kerja');
            $table->boolean('is_pns')->comment('Apakah PNS?');
            $table->boolean('is_active')->default(true)->comment('Status Hubungan Kerja');
            $table->boolean('is_permanent')->default(false)->comment('Apakah Pegawai Tetap?');
            $table->string('ref_key_siakad')->nullable()->comment('Kolom PK SIAKAD V1');

            $table->logs(true);
        });

        DB::statement('CREATE UNIQUE INDEX ON hr.work_relations (code) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('hr.work_relations');
    }
};
