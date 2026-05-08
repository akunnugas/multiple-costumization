<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorBobot;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $listPenilaianPanduan = PenilaianPanduan::all();
        $listPeriode = AuditPeriode::all();
        $units = UnitKerja::whereIn('jenis_unit', [UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI])->get();
        $listIndikatorBobots = IndikatorBobot::all();

        foreach ($listPenilaianPanduan as $penilaianPanduan) {
            foreach ($listPeriode as $periode) {
                $indikatorBobots = $listIndikatorBobots->where("id_audit_periode", $periode->id);
                foreach ($units as $unit) {
                    foreach ($indikatorBobots as $indikatorBobot) {
                        $payload = [
                            "id_audit_periode" => $indikatorBobot->id_audit_periode,
                            "jenis_indikator_bobot" => $indikatorBobot->jenis_indikator_bobot,
                            "nama_kategori_indikator" => $indikatorBobot->nama_kategori_indikator,
                            "persentase" => $indikatorBobot->persentase,
                            "id_unit" => $unit->id,
                            "id_penilaian_panduan" => $penilaianPanduan->id
                        ];

                        IndikatorBobot::create($payload);
                    }
                }
            }
        }

        IndikatorBobot::where("id_unit", null)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
