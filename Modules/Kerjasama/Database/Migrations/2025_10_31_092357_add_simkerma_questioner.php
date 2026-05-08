<?php


use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    public function up()
    {
        SevimaSchema::create('kerjasama.evaluasi', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('judul_evaluasi');
            $table->string('tipe_evaluasi');
            $table->uuid('uuid');
            $table->string('tipe_model');
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('id_mitra');
            // $table->unsignedBigInteger('id_induk_kerjasama'); 
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->date('mulai')->nullable();
            $table->date('selesai')->nullable();
            $table->logs();
        });


        SevimaSchema::create('kerjasama.pertanyaan', function (SevimaBlueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evaluasi_id');
            $table->string('nomor');
            $table->text('pertanyaan');
            $table->string('tipe');
            $table->string('rating')->nullable();
            $table->timestamps();
            $table->text('deskripsi')->nullable();
            $table->boolean('apakah_wajib')->nullable();
            $table->foreign('evaluasi_id')->references('id')->on('kerjasama.evaluasi')->onDelete('cascade');
            $table->logs();
        });



        SevimaSchema::create('kerjasama.opsi_jawaban', function (SevimaBlueprint $table) {
            $table->id();

            $table->unsignedBigInteger('pertanyaan_id');
            $table->string('urutan')->nullable();

            $table->string('jawaban');
            $table->timestamps();
            $table->foreign('pertanyaan_id')->references('id')->on('kerjasama.pertanyaan')->onDelete('cascade');
            $table->logs();
        });

        SevimaSchema::create('kerjasama.peserta', function (SevimaBlueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_kerja_id')->nullable();
            $table->unsignedBigInteger('evaluasi_id');
            $table->timestamps();
            $table->string('nama')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('nik')->nullable();
            $table->string('npwp')->nullable();
            $table->logs();
            $table->foreign('evaluasi_id')->references('id')->on('kerjasama.evaluasi')->onDelete('cascade');
        });

        SevimaSchema::create('kerjasama.jawaban_peserta', function (SevimaBlueprint $table) {
            $table->id();
            $table->unsignedBigInteger('peserta_id');
            $table->unsignedBigInteger('pertanyaan_id');
            $table->unsignedBigInteger('opsi_jawaban_id')->nullable();
            $table->text('jawaban')->nullable();
            $table->timestamps();
            $table->foreign('peserta_id')->references('id')->on('kerjasama.peserta');
            $table->foreign('pertanyaan_id')->references('id')->on('kerjasama.pertanyaan');
            $table->logs();
        });
    }

    public function down()
    {
        SevimaSchema::dropIfExists('kerjasama.jawaban_peserta');
        SevimaSchema::dropIfExists('kerjasama.peserta');
        SevimaSchema::dropIfExists('kerjasama.opsi_jawaban');
        SevimaSchema::dropIfExists('kerjasama.pertanyaan');
        SevimaSchema::dropIfExists('kerjasama.evaluasi');
        SevimaSchema::dropIfExists('kerjasama.tipe_jawaban');
    }
};
