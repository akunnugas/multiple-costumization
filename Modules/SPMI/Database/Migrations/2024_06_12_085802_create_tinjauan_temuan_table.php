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
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('spmi.tinjauan_temuan', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(PenilaianAudit::class, 'id_penilaian_audit');
            $table->foreignIdTo(PenilaianMatriks::class, 'id_penilaian_matriks');
            $table->text('akar_masalah')
                ->nullable()
                ->comment('Akar Masalah');
            $table->text('rencana_peningkatan_mutu')
                ->nullable()
                ->comment('Akar Masalah');
            $table->string('pelaksana')
                ->comment('Pelaksana');
            $table->date('tanggal_peningkatan_mutu')->comment('Tanggal Perbaikan');
            $table->foreignIdTo(PenilaianMatriksPredikat::class, 'id_predikat_matriks_penilaian');
            $table->decimal('nilai_target_default', 5, 2)
                ->comment('Skor Target Default');
            $table->decimal('nilai_target', 5, 2)
                ->comment('Skor Target');

            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('spmi.tinjauan_temuan');
    }
};
