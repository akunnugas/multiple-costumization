<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Modules\SPMI\Models\AuditPeriode;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('spmi.jadwal_audit', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(AuditPeriode::class, 'id_audit_periode');
            $table->date('tanggal_awal_pengisian')->comment('Tanggal Mulai Pengisian');
            $table->date('tanggal_akhir_pengisian')->comment('Tanggal Akhir Pengisian');
            $table->date('tanggal_awal_penilaian')->comment('Tanggal Mulai Penilaian');
            $table->date('tanggal_akhir_penilaian')->comment('Tanggal Akhir Penilaian');
            $table->boolean('apakah_audit_aktif')->default(false)->comment('Status Audit');
            $table->boolean('apakah_penilaian_mandiri')->default(false)->comment('Penilaian Mandiri');

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
        SevimaSchema::dropIfExists('spmi.jadwal_audit');
    }
};
