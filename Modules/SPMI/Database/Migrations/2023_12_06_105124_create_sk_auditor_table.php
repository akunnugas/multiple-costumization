<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\DMS\Models\Dokumen;
use Modules\SPMI\Models\AuditPeriode;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.sk_auditor', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(AuditPeriode::class, 'id_audit_periode');
            $table->string('nomor_sk')->comment('Nomor Surat Keputusan');
            $table->date('tanggal_diterbitkan')->comment('Tanggal Surat Keputusan');
            $table->date('tanggal_awal_berlaku')->comment('Tanggal Awal Berlaku');
            $table->date('tanggal_akhir_berlaku')->comment('Tanggal Akhir Berlaku');
            $table->foreignIdTo(Dokumen::class, 'id_dokumen', true);
            $table->logs(true);
        });

        DB::statement('CREATE UNIQUE INDEX ON spmi.sk_auditor (nomor_sk) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.sk_auditor');
    }
};
