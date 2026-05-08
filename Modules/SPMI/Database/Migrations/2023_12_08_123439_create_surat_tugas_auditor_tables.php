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
        SevimaSchema::create('spmi.surat_tugas_auditor', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(AuditPeriode::class, 'id_audit_periode');
            $table->string('nomor_surat_tugas')->comment('Nomor Surat Tugas');
            $table->date('tanggal_surat_tugas')->comment('Tanggal Surat Tugas');
            $table->date('tanggal_mulai')->comment('Tanggal Awal Berlaku');
            $table->date('tanggal_selesai')->comment('Tanggal Akhir Berlaku');
            $table->foreignIdTo(Dokumen::class, 'id_dokumen', true);
            $table->logs(true);
        });

        DB::statement('CREATE UNIQUE INDEX ON spmi.surat_tugas_auditor (nomor_surat_tugas) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.surat_tugas_auditor');
    }
};
