<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\LembagaAkreditasi;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.target_indikator', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(AuditPeriode::class, 'id_audit_periode');
            $table->foreignIdTo(LembagaAkreditasi::class, 'id_lembaga_akreditasi');
            $table->foreignIdTo(PenilaianPanduan::class, 'id_penilaian_panduan');
            $table->foreignIdTo(UnitKerja::class, 'id_unit');
            $table->boolean('apakah_terfinalisasi')
                ->default(false)
                ->comment('Finalisasi');
            $table->integer('total_matriks')
                ->nullable()->comment('Total Matriks');

            $table->logs(true);
        });

        SevimaSchema::table('spmi.target_indikator', function (SevimaBlueprint $table) {
            $table->unique(['id_audit_periode', 'id_unit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.target_indikator');
    }
};
