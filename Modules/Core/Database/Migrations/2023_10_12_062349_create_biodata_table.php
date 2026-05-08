<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Agama;
use Modules\Core\Models\Pekerjaan;
use Modules\Core\Models\Suku;
use Modules\Core\Models\Wilayah;
use Modules\Gate\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.biodata', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('gelar_depan')->nullable();
            $table->string('gelar_belakang')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->char('jenis_kelamin', 1)->nullable();
            $table->string('email')->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('alamat')->nullable();
            $table->string('desa')->nullable();
            $table->string('dusun')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->string('kode_pos')->nullable();
            $table->foreignIdTo(Wilayah::class, 'id_negara', true);
            $table->foreignIdTo(Wilayah::class, 'id_provinsi', true);
            $table->foreignIdTo(Wilayah::class, 'id_kota', true);
            $table->foreignIdTo(Wilayah::class, 'id_kecamatan', true);
            $table->foreignIdTo(Agama::class, nullable: true);
            $table->foreignIdTo(Suku::class, nullable: true);
            $table->string('nik')->nullable();
            $table->string('no_kk')->nullable();
            $table->string('npsn')->nullable();
            $table->string('no_kps')->nullable();
            $table->string('no_paspor')->nullable();
            $table->float('berat')->nullable();
            $table->float('tinggi')->nullable();
            $table->string('ukuran_seragam')->nullable();
            $table->string('nama_ponpes')->nullable();
            $table->string('alamat_ponpes')->nullable();
            $table->double('lama_ponpes', 4, 2)->nullable();
            $table->foreignIdTo(Pekerjaan::class, nullable: true);
            $table->string('nama_instansi')->nullable();
            $table->foreignIdTo(User::class, nullable: true);
            $table->timestampTz('waktu_validasi')->nullable();
            $table->string('ref_key_siakad')->nullable();
            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.biodata');
    }
};
