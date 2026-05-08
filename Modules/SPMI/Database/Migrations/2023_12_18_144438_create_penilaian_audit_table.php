<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\LembagaAkreditasi;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.penilaian_audit', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(AuditPeriode::class, 'id_audit_periode');
            $table->foreignIdTo(LembagaAkreditasi::class, 'id_lembaga_akreditasi');
            $table->foreignIdTo(PenilaianPanduan::class, 'id_penilaian_panduan');
            $table->foreignIdTo(UnitKerja::class, 'id_unit');
            $table->foreignIdTo(JadwalAudit::class, 'id_jadwal_audit');
            $table->unsignedBigInteger('id_ketua_auditor')
                ->nullable()
                ->comment('Ketua Auditor');
            $table->boolean('apakah_penilaian_mandiri')->comment('Penilaian Mandiri');
            $table->decimal('nilai_akhir', 5, 2)->nullable()->comment('Skor Akhir');
            $table->boolean('apakah_terfinalisasi')
                ->default(false)
                ->comment('Finalisasi');
            $table->integer('total_indikator_terisi')
                ->default(0)
                ->comment('Total Indikator Terisi');
            $table->integer('total_temuan')
                ->default(0)
                ->comment('Total Temuan');

            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.penilaian_audit');
    }
};
