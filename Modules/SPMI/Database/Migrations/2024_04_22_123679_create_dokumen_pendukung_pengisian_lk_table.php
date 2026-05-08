<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\DMS\Models\Dokumen;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
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
        SevimaSchema::create('spmi.dokumen_pendukung_pengisian_lk', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(PengisianIndikator::class, 'pengisian_indikator_id');
            $table->foreignIdTo(IndikatorLaporanKinerja::class, 'id_indikator_laporan_kinerja');
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
        SevimaSchema::dropIfExists('spmi.dokumen_pendukung_pengisian_indikator');
    }
};
