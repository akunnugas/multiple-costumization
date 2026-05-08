<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\IndikatorLaporanKinerja;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.indikator_cell', function (SevimaBlueprint $table) {
            $table->id();
            $table->integer('column_to')->comment('Kolom Ke');
            $table->integer('row_to')->comment('Baris Ke');
            $table->foreignIdTo(IndikatorLaporanKinerja::class, 'id_indikator_laporan_kinerja');

            $table->enum('kategori_cell', ['C','F'])->comment('Kategori Sel (C: Cell, F: Footer)');
            $table->enum('jenis_cell', ['L','D','J','A','R','B','E'])->comment('Tipe Sel (L: Label, D: Disabled, J: SUM COLUMN , A: SUM ALL, R: AVERAGE, B: SUM DATA COLUMN, E: SUM DATA NOT EMPTY COLUMN)');
            $table->enum('posisi_label', ['L','C','R'])->default(true)->comment('Posisi Label (L: Left, C: Center, R: Right)');
            $table->string('nama')->nullable()->comment('Nama Sel');
            $table->string('colspan')->nullable()->comment('colspan');
            $table->string('rowspan')->nullable()->comment('rowspan');
            $table->string('properti')->nullable()->comment('Properties');

            $table->boolean('apakah_sub_footer')->default(false)->comment('Apakah Sub Footer?');
            $table->boolean('dapat_dilihat')->default(true)->comment('Tampilkan di laporan?');

            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.indikator_cell');
    }
};
