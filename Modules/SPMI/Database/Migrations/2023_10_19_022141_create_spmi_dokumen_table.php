<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('spmi.spmi_dokumen', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_spmi_dokumen', 50)->comment('Nomor Dokumen');
            $table->string('nama_spmi_dokumen')->comment('Nama Dokumen');
            $table->text('deskripsi')->nullable()->comment('Deskripsi Singkat');
            $table->bigInteger("id_dokumen")->comment('ID Dokumen');
            $table->foreign('id_dokumen')->references('id')->on('dms.dokumen');
            $table->string("versi", 5)->comment('Versi Dokumen');
            $table->bigInteger("id_jenis")->nullable()->comment('ID Tipe Dokumen');
            $table->foreign('id_jenis')->references('id')->on('spmi.spmi_jenis_dokumen');
            $table->boolean('apakah_aktif')->default(false)->comment('Status Dokumen');
            $table->date("tanggal_awal_berlaku")->nullable()->comment('Tanggal Mulai Berlaku');
            $table->date("tanggal_akhir_berlaku")->nullable()->comment('Tanggal Akhir Berlaku');

            $table->logs(true);
        });

        DB::statement('CREATE UNIQUE INDEX ON spmi.spmi_dokumen (kode_spmi_dokumen) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('spmi.internal_documents');
    }
};
