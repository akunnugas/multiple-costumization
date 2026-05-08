<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\Mahasiswa;
use Modules\Core\Models\PeriodeAkademik;
use Modules\Core\Models\UnitKerja;
use Modules\Gate\Models\User;
use Modules\PMB\Models\PeriodePendaftaran;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('pmb.pendaftar', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_pendaftar')->nullable();
            $table->foreignIdTo(Biodata::class, nullable: true);
            $table->foreignIdTo(PeriodePendaftaran::class, nullable: true);
            $table->foreignIdTo(PeriodeAkademik::class);
            $table->foreignIdTo(Mahasiswa::class, nullable: true);
            $table->string('sumber_data')->nullable();
            $table->string('status_lulus')->nullable();
            $table->foreignIdTo(UnitKerja::class, 'id_prodi_lulus', true);
            $table->foreignIdTo(UnitKerja::class, 'id_prodi_diminati', true);
            $table->foreignIdTo(User::class, 'direkomendasikan_oleh', true);
            $table->string('utm_source')->nullable();
            $table->boolean('apakah_import_nim')->nullable();
            $table->timestampTz('waktu_registrasi')->nullable();
            $table->date('tanggal_daftar_ulang')->nullable();
            $table->timestampTz('waktu_finalisasi')->nullable();
            $table->timestampTz('waktu_aktif')->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('pmb.pendaftar', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_pendaftar', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.pendaftar');
    }
};
