<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\PerguruanTinggi;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\PegawaiManagementService;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.pegawai', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Biodata::class);
            $table->foreignIdTo(UnitKerja::class, nullable: true);
            $table->string('nip', 25);
            $table->string('nip_pns', 25)->nullable();
            $table->string('email_kampus', 50)->nullable();
            $table->string('akun_sidik_jari', 25)->nullable();
            $table->foreignId('id_status_pegawai')->nullable()->constrained('hr.employee_statuses');
            $table->foreignId('id_hubungan_kerja')->nullable()->constrained('hr.work_relations');
            $table->foreignId('id_jabatan_struktural_atasan')->nullable()->constrained('hr.structural_positions');
            $table->foreignId('id_jabatan_fungsional')->nullable()->constrained('hr.functional_positions');
            $table->foreignId('id_jabatan_akademik')->nullable()->constrained('hr.academic_positions');
            $table->string('ref_key_siakad')->nullable();
            $table->logs(true);
            $table->index('id_status_pegawai');
            $table->index('id_hubungan_kerja');
            $table->index('id_jabatan_struktural_atasan');
            $table->index('id_jabatan_fungsional');
            $table->index('id_jabatan_akademik');
        });

        SevimaSchema::table('core.pegawai', function (SevimaBlueprint $table) {
            $table->uniqueIndex('nip', true);
        });

        if (env("DB_SIAKADV1_SYNC", false)) {
            $service = new PegawaiManagementService;
            $service->syncFromSiakadv1();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.pegawai');
    }
};
