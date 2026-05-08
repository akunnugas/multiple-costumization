<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksPredikat;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.penilaian_skor', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(PenilaianAudit::class, 'id_penilaian_audit');
            $table->foreignIdTo(PenilaianMatriks::class, 'id_penilaian_matriks');
            $table->foreignIdTo(PenilaianMatriksPredikat::class, 'id_predikat_matriks_penilaian', true);
            $table->decimal('nilai_default', 5, 2)->nullable()->comment('Skor Default');
            $table->decimal('nilai', 5, 2)->nullable()->comment('Skor');
            $table->decimal('nilai_target', 5, 2)->comment('Target Skor');
            $table->decimal('nilai_akhir', 5, 2)
                ->nullable()
                ->comment('Skor Akhir');
            $table->string('status_penilaian', 2)->nullable()
                ->comment('Status (1: Menyimpang, 2: Belum Memeuhi, 3: Memenuhi, 4: Sangat Melampaui)');
            $table->text('catatan_penilaian')
                ->nullable()
                ->comment('Feedback');

            $table->logs(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.penilaian_skor');
    }
};
