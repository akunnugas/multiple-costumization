<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\PenilaianMatriks;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.penilaian_matriks_predikat', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(PenilaianMatriks::class, 'id_penilaian_matriks');
            $table->integer('nilai')->comment('Nilai');
            $table->text('deskripsi')->nullable()->comment('Uraian Skor');
            $table->string('kriteria', 100)->nullable()->comment('Variabel Kriteria');
            $table->text('rumus_penilaian')->nullable()->comment('rumus_penilaian');
            $table->boolean('apakah_nonaktif')->default(false)->comment('Status Skor');

            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.penilaian_matriks_predikat');
    }
};
