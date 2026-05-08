<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\LembagaAkreditasi;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\PengisianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.pengisian_indikator', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(AuditPeriode::class, 'id_audit_periode');
            $table->foreignIdTo(LembagaAkreditasi::class);
            $table->foreignIdTo(PengisianPanduan::class, 'id_pengisian_panduan');
            $table->foreignIdTo(UnitKerja::class, 'id_unit');
            $table->enum('jenis_indikator', ['pr', 'se'])
                ->default('pr')->comment('pr = performance report | se = self evaluation');

            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.pengisian_indikator');
    }
};
