<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\UnitKerja;
use Modules\DMS\Models\Dokumen;
use Modules\SPMI\Models\AuditPeriode;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.dokumen_hasil_audit', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(UnitKerja::class, 'id_unit');
            $table->foreignIdTo(Dokumen::class, 'id_dokumen');
            $table->foreignIdTo(AuditPeriode::class, 'id_audit_periode');

            $table->logs(true);
        });

        DB::statement('CREATE UNIQUE INDEX ON spmi.dokumen_hasil_audit (id_unit, id_audit_periode) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.dokumen_hasil_audit');
    }
};
