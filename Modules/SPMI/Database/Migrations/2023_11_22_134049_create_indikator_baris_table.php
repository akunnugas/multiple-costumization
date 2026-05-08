<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.indikator_baris', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama')->comment('Nama Baris');
            $table->unsignedBigInteger('id_indikator_laporan_kinerja')->comment('Indikator Pengisian');
            $table->string('jenis_penomoran')->comment('Jenis Penomoran (A: Alphabet, N: Number, R: Romawi)')->nullable();

            // optional if apakah_sub_footer = true
            $table->integer('row_range_from')->nullable()->comment('Baris Range Dari');
            $table->integer('row_range_to')->nullable()->comment('Baris Range Sampai');

            // foreign key
            $table->foreign('id_indikator_laporan_kinerja')->references('id')->on('spmi.indikator_laporan_kinerja');
            $table->foreign('id_parent')->references('id')->on('spmi.indikator_baris');

            // tree structure
            $table->unsignedBigInteger('id_parent')->nullable();
            $table->unsignedInteger('info_level')->nullable();
            $table->integer('info_left')->nullable();
            $table->integer('info_right')->nullable();

            // create index
            $table->index(['id_indikator_laporan_kinerja','id_parent','info_left','info_right']);

            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.indikator_baris');
    }
};
