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
        SevimaSchema::create('spmi.audit_periode', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('tahun_audit', 4)->comment('Tahun Audit');
            $table->date('tanggal_mulai')->nullable()
                ->comment('Tanggal Mulai');
            $table->date('tanggal_selesai')->nullable()
                ->comment('Tanggal Selesai');

            $table->logs(true);
        });

        DB::statement('CREATE UNIQUE INDEX ON spmi.audit_periode (tahun_audit) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.audit_periode');
    }
};
