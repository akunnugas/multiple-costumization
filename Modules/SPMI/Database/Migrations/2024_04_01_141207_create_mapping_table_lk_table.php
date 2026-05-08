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
        SevimaSchema::create('spmi.mapping_lk', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignId('id_indikator_laporan_kinerja')->constrained('spmi.indikator_laporan_kinerja');
            $table->foreignId('id_jenjang_pendidikan')->constrained('core.jenjang_pendidikan');
            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.mapping_lk');
    }
};
