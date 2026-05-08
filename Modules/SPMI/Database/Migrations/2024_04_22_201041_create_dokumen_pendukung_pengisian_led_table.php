<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\DMS\Models\Dokumen;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\PengisianIndikator;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('spmi.dokumen_pendukung_pengisian_led', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(PengisianIndikator::class, 'pengisian_indikator_id');
            $table->foreignIdTo(IndikatorEvaluasiDiri::class, 'id_indikator_evaluasi_diri');
            $table->foreignIdTo(Dokumen::class, 'id_dokumen');

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
        SevimaSchema::dropIfExists('spmi.dokumen_pendukung_pengisian_led');
    }
};
