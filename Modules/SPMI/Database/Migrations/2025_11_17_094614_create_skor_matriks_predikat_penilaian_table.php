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
        SevimaSchema::create('spmi.skor_matriks_predikat_penilaian', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignId('id_penilaian_panduan')->constrained('spmi.penilaian_panduan');
            $table->string('deskripsi', 100);
            $table->integer('nilai');
            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.skor_matriks_predikat_penilaian');
    }
};
