<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AkreditasiPeringkat;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\SpmiPeringkat;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.hasil_akhir_audit', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(AuditPeriode::class, 'id_audit_periode');
            $table->foreignIdTo(UnitKerja::class, 'id_unit');
            $table->foreignIdTo(PenilaianAudit::class, 'id_penilaian_audit');
            $table->decimal('nilai_iku', 5, 2)->nullable()->comment('Skor IKU');
            $table->decimal('nilai_ikt', 5, 2)->nullable()->comment('Skor IKT');
            $table->decimal('nilai_akhir', 5, 2)->nullable()->comment('Skor Akhir');
            $table->decimal('nilai_akhir_auditee', 5, 2)->nullable()->comment('Skor Auditee');
            $table->decimal('persentase_nilai_akhir', 5, 2)->nullable()->after('final_score');
            $table->foreignIdTo(SpmiPeringkat::class, 'id_spmi_peringkat');
            $table->foreignIdTo(AkreditasiPeringkat::class, 'id_akreditasi_peringkat');

            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.hasil_akhir_audit');
    }
};
