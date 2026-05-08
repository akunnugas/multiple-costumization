<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\TargetIndikator;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksPredikat;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.target_skor', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(TargetIndikator::class, 'id_target_indikator');
            $table->foreignIdTo(PenilaianMatriks::class, 'id_penilaian_matriks');
            $table->foreignIdTo(PenilaianMatriksPredikat::class, 'id_predikat_matriks_penilaian');
            $table->decimal('nilai_default', 5, 2)->comment('Skor Default');
            $table->decimal('nilai', 5, 2)->comment('Skor');

            $table->logs(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.target_skor');
    }
};
