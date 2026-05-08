<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\JenisPendaftaran;
use Modules\Core\Models\PeriodeAkademik;
use Modules\Core\Models\SistemKuliah;
use Modules\PMB\Models\Gelombang;
use Modules\PMB\Models\JalurPendaftaran;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('pmb.periode_pendaftaran', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_periode');
            $table->string('nama_periode');
            $table->foreignIdTo(PeriodeAkademik::class);
            $table->foreignIdTo(Gelombang::class);
            $table->foreignIdTo(JalurPendaftaran::class);
            $table->foreignIdTo(SistemKuliah::class);
            $table->foreignIdTo(JenisPendaftaran::class);
            $table->string('keterangan_periode')->nullable();
            $table->boolean('apakah_berbayar')->default(0);
            $table->string('status_periode')->default('draft')->comment('draft, published');
            $table->timestampTz('waktu_dibuka')->nullable();
            $table->timestampTz('waktu_ditutup')->nullable();
            $table->year('tahun_lulus_akhir')->nullable();
            $table->date('tanggal_minimal_batas_lahir')->nullable();
            $table->date('tanggal_maksimal_batas_lahir')->nullable();
            $table->timestampTz('tanggal_awal_daftar_ulang')->nullable();
            $table->timestampTz('tanggal_akhir_daftar_ulang')->nullable();
            $table->timestampTz('waktu_pengumuman_kelulusan')->nullable();
            $table->timestampTz('waktu_pengumuman_nilai')->nullable();
            $table->boolean('apakah_tampilkan_daya_tampung')->default(0);
            $table->boolean('apakah_tampilkan_nilai')->default(0);
            $table->boolean('dapat_mengubah_prodi')->default(0);
            $table->boolean('dapat_pilih_prodi_sama')->default(0);
            $table->boolean('dapat_pilih_fakultas_sama')->default(0);
            $table->string('keterangan_finalisasi')->nullable();
            $table->timestampTz('waktu_akhir_finalisasi')->nullable();
            $table->string('penilaian_rapor')->default('no')->comment('yes, no, optional');
            $table->integer('batas_tanggal_va')->nullable();
            $table->string('proses_kelulusan')->default('manual')->comment('manual, auto-recommended, auto-qualified');
            $table->logs(true);
        });

        SevimaSchema::table('pmb.periode_pendaftaran', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_periode', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.periode_pendaftaran');
    }
};
