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
        SevimaSchema::create('spmi.akreditasi_peringkat', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_peringkat', 10)->comment('Kode Peringkat');
            $table->string('nama_peringkat_akreditasi')->comment('Nama Peringkat Akreditasi');
            $table->decimal('nilai_minimal', 5, 2)->comment('Nilai Minimal')->default(0.00);
            $table->decimal('nilai_maksimal', 5, 2)->comment('Nilai Maksimal')->default(0.00);
            $table->text('deskripsi')->nullable()->comment('Keterangan');

            $table->logs(true);
        });

        DB::statement('CREATE UNIQUE INDEX ON spmi.akreditasi_peringkat (kode_peringkat) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.akreditasi_peringkat');
    }
};
