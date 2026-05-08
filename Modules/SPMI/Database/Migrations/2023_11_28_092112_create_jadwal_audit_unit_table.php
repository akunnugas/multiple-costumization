<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\JadwalAudit;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.jadwal_audit_unit', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(JadwalAudit::class, 'id_jadwal_audit');
            $table->foreignIdTo(UnitKerja::class, 'id_unit');
            $table->logs(false);
        });

        DB::statement('CREATE UNIQUE INDEX ON spmi.jadwal_audit_unit (id_unit, id_jadwal_audit)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.jadwal_audit_unit');
    }
};
