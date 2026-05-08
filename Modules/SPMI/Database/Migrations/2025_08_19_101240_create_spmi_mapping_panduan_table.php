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
        SevimaSchema::create('spmi.mapping_panduan', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignId('id_pengisian_panduan')
                ->constrained('spmi.pengisian_panduan')
                ->cascadeOnDelete();
            $table->foreignId('id_penilaian_panduan')
                ->constrained('spmi.penilaian_panduan')
                ->cascadeOnDelete();
            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.mapping_panduan');
    }
};
