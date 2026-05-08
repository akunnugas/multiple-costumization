<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\UnitKerja;
use Modules\DMS\Models\Dokumen;
use Modules\Kerjasama\Models\BentukKegiatan;
use Modules\Kerjasama\Models\IndikatorSasaran;
use Modules\Kerjasama\Models\Kerjasama;
use Modules\Kerjasama\Models\SasaranKinerja;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('kerjasama.kegiatan', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Kerjasama::class, 'id_induk_kerjasama');
            $table->string('nomor_dokumen');
            $table->string('nomor_dokumen_mitra')->nullable();

            $table->text('judul_kegiatan');
            $table->foreignIdTo(UnitKerja::class, 'id_unit_kerja');
            $table->foreignIdFor(BentukKegiatan::class, 'id_bentuk_kegiatan');
            $table->foreignIdFor(SasaranKinerja::class, 'id_sasaran_kinerja');
            $table->foreignIdFor(IndikatorSasaran::class, 'id_indikator_sasaran');

            $table->date('tanggal_mulai_berlaku');
            $table->date('tanggal_akhir_berlaku');

            $table->text('ruang_lingkup')->nullable();
            $table->text('hasil_pelaksanaan')->nullable();
            $table->bigInteger('anggaran')->nullable();
            $table->foreignIdFor(Dokumen::class, 'id_dokumen')->nullable();
            $table->string('link_dokumentasi')->nullable();
            
            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('kerjasama.kegiatan');
    }
};
