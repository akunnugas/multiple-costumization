<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.spmi_tarikdata_lk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_indikator_laporan_kinerja')->constrained('spmi.indikator_laporan_kinerja');
            $table->boolean('apakah_kurikulum')->default(false);
            $table->string('sumber');
            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.spmi_tarikdata_lk');
    }
};
