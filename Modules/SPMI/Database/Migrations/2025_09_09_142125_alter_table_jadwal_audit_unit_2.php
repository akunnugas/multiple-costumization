<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Models\PengisianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pengisianPanduan = PengisianPanduan::where('kode_pengisian_panduan', 'IAPS9')->first();

        // update data existing
        $listPenilaianAudit = DB::table('spmi.penilaian_audit')
            ->select('id_unit', 'id_penilaian_panduan', 'id_audit_periode')
            ->distinct()
            ->get();

        $listTargetCapaian = DB::table('spmi.target_indikator')
            ->select('id_audit_periode', 'id_unit')
            ->distinct()
            ->get();

        $listPengisian = DB::table('spmi.pengisian_indikator')
            ->select('id_audit_periode', 'id_unit')
            ->distinct()
            ->get();

        $data = [];
        foreach ($listTargetCapaian as $target) {
            $target->id_pengisian_panduan = $pengisianPanduan->id;
            $data[$target->id_audit_periode . '-' . $target->id_unit] = $target;
        }
        foreach ($listPengisian as $pengisian) {
            $key = $pengisian->id_audit_periode . '-' . $pengisian->id_unit;
            if (!isset($data[$key])) {
                $data[$key] = (object) [
                    'id_audit_periode' => $pengisian->id_audit_periode,
                    'id_unit' => $pengisian->id_unit,
                ];
            }
        }

        foreach ($listPenilaianAudit as $penilaian) {
            $key = $penilaian->id_audit_periode . '-' . $penilaian->id_unit;
            if (isset($data[$key])) {
                $data[$key]->id_penilaian_panduan = $penilaian->id_penilaian_panduan;
            }
        }

        $defaultPenilaianPanduan = DB::table('spmi.penilaian_panduan')
            ->where('kode_penilaian_panduan', 'IAPS-S1')
            ->first();
        foreach ($data as $key => $obj) {
            $jadwalAudit = JadwalAudit::where('id_audit_periode', $obj->id_audit_periode)->first();
            if (!isset($obj->id_penilaian_panduan)) {
                $unit = DB::table('core.unit_kerja')->where('id', $obj->id_unit)->first();
                $penilaianPanduan = DB::table('spmi.penilaian_panduan')
                    ->where('id_jenjang_pendidikan', $unit->id_jenjang_pendidikan)
                    ->where('apakah_aktif', true) // tambahkan filter apakah_aktif
                    ->first();
                if (!$penilaianPanduan) {
                    $penilaianPanduan = $defaultPenilaianPanduan;
                }
                $data[$key]->id_penilaian_panduan = $penilaianPanduan ? $penilaianPanduan->id : null;
            }
            $data[$key]->id_jadwal_audit = $jadwalAudit ? $jadwalAudit->id : null;
        }

        foreach ($data as $obj) {
            if ($obj->id_jadwal_audit) {
                DB::table('spmi.jadwal_audit_unit')
                    ->where('id_jadwal_audit', $obj->id_jadwal_audit)
                    ->where('id_unit', $obj->id_unit)
                    ->update([
                        'id_pengisian_panduan' => $obj->id_pengisian_panduan ?? null,
                        'id_penilaian_panduan' => $obj->id_penilaian_panduan ?? null,
                    ]);
            }
        }

        // update data yang null
        $nullData = DB::table('spmi.jadwal_audit_unit')
            ->whereNull('id_pengisian_panduan')
            ->orWhereNull('id_penilaian_panduan')
            ->get();

        foreach ($nullData as $item) {
            $unit = DB::table('core.unit_kerja')->where('id', $item->id_unit)->first();
            $penilaianPanduan = DB::table('spmi.penilaian_panduan')
                ->where('id_jenjang_pendidikan', $unit->id_jenjang_pendidikan)
                ->first();
            DB::table('spmi.jadwal_audit_unit')
                ->where('id', $item->id)
                ->update([
                    'id_pengisian_panduan' => $pengisianPanduan->id ?? null,
                    'id_penilaian_panduan' => $penilaianPanduan->id ?? null,
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
