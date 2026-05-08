<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\JenisInstitusi;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\PerguruanTinggi;
use Modules\Core\Models\ProgramStudi;
use Modules\Core\Models\Sekolah;
use Modules\Core\Models\Wilayah;
use Modules\PMB\Models\Pendaftar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('pmb.pendaftar_pendidikan', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Pendaftar::class);
            $table->foreignIdTo(JenjangPendidikan::class);
            $table->foreignIdTo(Wilayah::class, 'id_provinsi');
            $table->foreignIdTo(Wilayah::class, 'id_kota');
            $table->foreignIdTo(JenisInstitusi::class);
            $table->string('nama_institusi', 100);
            $table->string('jurusan', 50)->nullable();
            $table->year('tahun_lulus')->nullable();
            $table->foreignIdTo(Sekolah::class, nullable: true);
            $table->foreignIdTo(PerguruanTinggi::class, nullable: true);
            $table->foreignIdTo(ProgramStudi::class, nullable: true);
            $table->string('nisn', 60)->nullable();
            $table->string('nim', 20)->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->decimal('ipk', 3, 2)->nullable();
            $table->decimal('sks', 3, 0)->nullable();
            $table->logs(true);
            $table->index('nama_institusi');
            $table->index('jurusan');
            $table->index('tahun_lulus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('pmb.pendaftar_pendidikan');
    }
};
