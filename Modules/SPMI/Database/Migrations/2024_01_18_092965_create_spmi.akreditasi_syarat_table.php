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
        SevimaSchema::create('spmi.akreditasi_syarat', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignId('id_penilaian_panduan')
                ->constrained('spmi.penilaian_panduan')->comment('Panduan Penilaian');
            $table->foreignId('id_penilaian_matriks')
                ->constrained('spmi.penilaian_matriks')->comment('Matriks Penilaian');
            $table->foreignId('id_akreditasi_peringkat')
                ->constrained('spmi.akreditasi_peringkat')->comment('Peringkat Akreditasi');
            $table->string('jenis_syarat_akreditasi', 2)->comment('Jenis Syarat (T: Terakreditasi, P: Peringkat)');
            $table->decimal('nilai_syarat_akreditasi', 5, 2)->comment('Syarat Skor');

            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.akreditasi_syarat');
    }
};
