<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\AuditTemuan;
use Modules\SPMI\Models\MappingPenilaianMatriks;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\PenilaianSkor;
use Modules\SPMI\Models\TargetIndikator;
use Modules\SPMI\Models\TargetSkor;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $listTargetIndikatorTermapping = TargetIndikator::where('apakah_terfinalisasi', true)
            ->get();

        foreach ($listTargetIndikatorTermapping as $targetIndikator) {
            $listMapping = MappingPenilaianMatriks::join('spmi.penilaian_matriks', 'spmi.penilaian_matriks.id', '=', 'spmi.mapping_penilaian_matriks.id_penilaian_matriks')
                ->where('spmi.mapping_penilaian_matriks.id_unit', $targetIndikator->id_unit)
                ->where('spmi.mapping_penilaian_matriks.id_audit_periode', $targetIndikator->id_audit_periode)
                ->where('spmi.penilaian_matriks.id_penilaian_panduan', $targetIndikator->id_penilaian_panduan)
                ->distinct()
                ->pluck('spmi.mapping_penilaian_matriks.id_penilaian_matriks');

            $targetSkor = TargetSkor::where('id_target_indikator', $targetIndikator->id)->pluck('id_penilaian_matriks')->toArray();
            $mappingToBeRemoved = array_diff($listMapping->toArray(), $targetSkor);

            if (!empty($mappingToBeRemoved)) {
                $penilaianAudit = PenilaianAudit::where('id_unit', $targetIndikator->id_unit)
                    ->where('id_audit_periode', $targetIndikator->id_audit_periode)
                    ->where('id_penilaian_panduan', $targetIndikator->id_penilaian_panduan)
                    ->first();

                TargetSkor::where('id_target_indikator', $targetIndikator->id)
                    ->whereIn('id_penilaian_matriks', $mappingToBeRemoved)
                    ->delete();

                if ($penilaianAudit) {
                    $penilaianSkor = PenilaianSkor::where('id_penilaian_audit', $penilaianAudit->id)->pluck('id_penilaian_matriks')->toArray();
                    $mappingPenilaianToBeRemoved = array_diff($listMapping->toArray(), $penilaianSkor);
                    PenilaianSkor::where('id_penilaian_audit', $penilaianAudit->id)
                        ->whereIn('id_penilaian_matriks', $mappingPenilaianToBeRemoved)
                        ->delete();
                    AuditTemuan::where('id_penilaian_audit', $penilaianAudit->id)
                        ->whereIn('id_penilaian_matriks', $mappingPenilaianToBeRemoved)
                        ->delete();
                }

                MappingPenilaianMatriks::where('id_unit', $targetIndikator->id_unit)
                    ->where('id_audit_periode', $targetIndikator->id_audit_periode)
                    ->whereIn('id_penilaian_matriks', $mappingToBeRemoved)
                    ->delete();
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
