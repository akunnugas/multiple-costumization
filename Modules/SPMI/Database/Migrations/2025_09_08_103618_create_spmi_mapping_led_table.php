<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AuditPeriode;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.mapping_led', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignId('id_indikator_evaluasi_diri')->constrained('spmi.indikator_evaluasi_diri');
            $table->foreignIdTo(AuditPeriode::class, 'id_audit_periode');
            $table->foreignIdTo(UnitKerja::class, 'id_unit');
            $table->logs(true);
        });

        $listTargetCapaian = DB::table('spmi.target_indikator')
            ->select('id_audit_periode', 'id_unit')
            ->distinct()
            ->get();

        $listPengisian = DB::table('spmi.pengisian_indikator')
            ->select('id_audit_periode', 'id_unit')
            ->distinct()
            ->get();

        // merge list pengisian led ke target capaian
        $data = [];
        foreach ($listTargetCapaian as $target) {
            $target->id_pengisian_panduan = null;
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

        // copy data
        $pengisianIAPS4 = DB::table('spmi.pengisian_panduan')
            ->where('kode_pengisian_panduan', 'LEDPS9')
            ->first();
        $listButirLED = DB::table('spmi.indikator_evaluasi_diri')
            ->where('id_pengisian_panduan', $pengisianIAPS4->id)
            ->get();
        foreach ($data as $item) {
            foreach ($listButirLED as $butir) {
                DB::table('spmi.mapping_led')->insert([
                    'id_indikator_evaluasi_diri' => $butir->id,
                    'id_audit_periode' => $item->id_audit_periode,
                    'id_unit' => $item->id_unit,
                    'waktu_dibuat' => now(),
                    'waktu_diubah' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // SevimaSchema::dropIfExists('spmi.mapping_lk_new');
    }
};
