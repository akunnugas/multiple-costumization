<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.spmi_peringkat', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignId('id_audit_periode')->comment('Periode Audit')
                ->constrained('spmi.audit_periode');
            $table->string('kode_spmi_peringkat', 10)->comment('Kode Peringkat');
            $table->string('nama_spmi_peringkat')->comment('Nama Peringkat Mutu');
            $table->decimal('skor_minimal', 5, 2)->comment('Nilai Minimal')->default(0.00);
            $table->decimal('skor_maksimal', 5, 2)->comment('Nilai Maksimal')->default(0.00);
            $table->text('deskripsi')->nullable()->comment('Keterangan');

            $table->logs(true);
        });

        DB::statement('CREATE UNIQUE INDEX ON spmi.spmi_peringkat (kode_spmi_peringkat) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.spmi_peringkat');
    }
};
