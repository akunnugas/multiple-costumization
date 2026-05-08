<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\Biodata;
use Modules\SPMI\Models\SuratTugasAuditor;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.surat_tugas_auditor_pegawai', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(SuratTugasAuditor::class, 'id_surat_tugas_auditor');
            $table->foreignIdTo(Biodata::class, 'id_personil');
            $table->foreignIdTo(UnitKerja::class, 'id_unit');
            $table->char('posisi', 1)
                ->comment('Posisi Petugas Auditor (L: Ketua, M: Anggota)');

            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.surat_tugas_auditor_pegawai');
    }
};
