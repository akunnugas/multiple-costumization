<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.data_pengisian_lk', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignId('id_pengisian_indikator')->comment('Pengisian Indikator')
                ->constrained('spmi.pengisian_indikator');
            $table->foreignId('id_indikator_laporan_kinerja')->comment('Indikator Kinerja')
                ->constrained('spmi.indikator_laporan_kinerja');
            $table->text('data_pengisian_lk')->comment('Data Butir');

            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.data_pengisian_lk');
    }
};
