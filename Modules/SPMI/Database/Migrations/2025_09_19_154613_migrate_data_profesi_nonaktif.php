<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\JadwalAuditUnit;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $mappingJadwalAuditUnit = JadwalAuditUnit::join('core.unit_kerja as uk', 'uk.id', '=', 'jadwal_audit_unit.id_unit')
            ->join('spmi.penilaian_panduan as pp', 'pp.id', '=', 'jadwal_audit_unit.id_penilaian_panduan')
            ->where('pp.apakah_aktif', false)
            ->select('spmi.jadwal_audit_unit.id_unit', 'spmi.jadwal_audit_unit.id')
            ->get();

        $idPenilaianPanduanS1 = PenilaianPanduan::where('kode_penilaian_panduan', 'IAPS-S1')->value('id');

        foreach ($mappingJadwalAuditUnit as $item) {
            JadwalAuditUnit::where('id', $item->id)->update([
                'id_penilaian_panduan' => $idPenilaianPanduanS1,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
