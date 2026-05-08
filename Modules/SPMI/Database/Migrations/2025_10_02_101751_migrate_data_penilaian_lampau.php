<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Models\MappingPenilaianMatriks;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianMatriks;

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

        foreach ($data as $key => $obj) {
            $jadwalAudit = JadwalAudit::where('id_audit_periode', $obj->id_audit_periode)->first();
            if (!isset($obj->id_penilaian_panduan)) {
                $unit = DB::table('core.unit_kerja')->where('id', $obj->id_unit)->first();
                $penilaianPanduan = DB::table('spmi.penilaian_panduan')
                    ->where('id_jenjang_pendidikan', $unit->id_jenjang_pendidikan)
                    ->first();
                $data[$key]->id_penilaian_panduan = $penilaianPanduan ? $penilaianPanduan->id : null;
            }
            $data[$key]->id_jadwal_audit = $jadwalAudit ? $jadwalAudit->id : null;
        }

        $listPenilaianMatriksByPanduan = PenilaianMatriks::whereIn('id_penilaian_panduan', $listPenilaianAudit->pluck('id_penilaian_panduan')->toArray())
            ->get()
            ->groupBy('id_penilaian_panduan');

        foreach ($data as $obj) {
            if ($obj->id_jadwal_audit) {
                $listMatriksPenilaian = $listPenilaianMatriksByPanduan[$obj->id_penilaian_panduan] ?? [];
                foreach ($listMatriksPenilaian as $matriks) {
                    MappingPenilaianMatriks::create([
                        'id_penilaian_matriks' => $matriks->id,
                        'id_audit_periode' => $obj->id_audit_periode,
                        'id_unit' => $obj->id_unit,
                    ]);
                }
            }
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
