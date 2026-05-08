<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Gate\Models\User;
use Modules\PMB\Models\Pendaftar;
use Modules\PMB\Models\SeleksiJadwal;
use Modules\PMB\Models\SeleksiJenis;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('pmb.seleksi_nilai', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Pendaftar::class);
            $table->foreignIdTo(SeleksiJenis::class);
            $table->float('nilai_seleksi')->nullable();
            $table->boolean('apakah_sesuai')->nullable();
            $table->string('keterangan_nilai')->nullable();
            $table->unsignedBigInteger('id_file_lampiran')->nullable();
            $table->foreignIdTo(SeleksiJadwal::class, nullable: true);
            $table->foreignIdTo(User::class, 'dinilai_oleh', true);
            $table->logs(true);
            $table->foreign('id_file_lampiran')->references('id')->on('dms.dokumen');
        });

        SevimaSchema::table('pmb.seleksi_nilai', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_pendaftar', 'id_seleksi_jenis', 'id_seleksi_jadwal'], true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.seleksi_nilai');
    }
};
