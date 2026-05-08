<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\PenilaianMatriks;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.audit_temuan', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(PenilaianAudit::class, 'id_penilaian_audit');
            $table->foreignIdTo(PenilaianMatriks::class, 'id_penilaian_matriks');
            $table->text('uraian_temuan_audit')
                ->nullable()
                ->comment('Temuan');
            $table->text('rencana_peningkatan_mutu')
                ->nullable()
                ->comment('Rencana Perbaikan');
            // FIXME: Sementara masih free text, nanti akan diubah menjadi foreign key
            $table->string('id_pelaksana')
                ->comment('Pelaksana');
            $table->date('tanggal_peningkatan_mutu')->comment('Tanggal Perbaikan');
            $table->string('jenis_temuan', 2)->nullable()
                ->comment('Jenis Temuan (1: Observasi, 2: KeTidakSesuaian(KTS) Minor, 3: KeTidakSesuaian(KTS) Mayor)');

            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.audit_temuan');
    }
};
