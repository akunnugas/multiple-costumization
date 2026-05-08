<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Kerjasama\Models\IndikatorSasaran;
use Modules\Kerjasama\Models\SasaranKinerja;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('kerjasama.sasaran_kinerja', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('sasaran');
            $table->text('keterangan')->nullable();
            $table->string('level')->nullable();
            $table->logs();
        });

        SevimaSchema::create('kerjasama.indikator_sasaran', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('indikator');
            $table->text('keterangan')->nullable();
            $table->string('volume')->nullable();
            $table->string('satuan')->nullable();
            $table->foreignIdTo(SasaranKinerja::class, 'id_sasaran_kinerja');
            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('kerjasama.sasaran_kinerja');
        SevimaSchema::dropIfExists('kerjasama.indikator_sasaran');
    }
};
