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
        SevimaSchema::create('spmi.data_pengisian_led', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignId('id_pengisian_indikator')->comment('Pengisian Indikator')
                ->constrained('spmi.pengisian_indikator');
            $table->foreignId('id_indikator_evaluasi_diri')->comment('Indikator Evaluasi Diri')
                ->constrained('spmi.indikator_evaluasi_diri');
            $table->text('data_pengisian_led')->comment('Data Butir')->nullable();
            $table->text('komentar')->comment('Komentar')->nullable();
            $table->text('key_points')->comment('Key Points')->nullable();

            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.data_pengisian_led');
    }
};
