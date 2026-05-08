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
        SevimaSchema::create('hr.certification_types', function (SevimaBlueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feeder_id')->nullable()->comment('ID Feeder');
            $table->unsignedBigInteger('dikti_id')->nullable()->comment('ID Dikti');
            $table->string('sister_id')->nullable()->comment('ID Sister');
            $table->string('code', 5)->comment('Kode');
            $table->string('name')->comment('Nama Jenis Sertifikasi');

            $table->logs(true);
        });

        DB::statement('CREATE UNIQUE INDEX ON hr.certification_types (code) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('hr.certification_types');
    }
};
