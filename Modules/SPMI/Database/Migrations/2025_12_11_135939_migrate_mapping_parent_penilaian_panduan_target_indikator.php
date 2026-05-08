<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\AuditTemuan;
use Modules\SPMI\Models\MappingPenilaianMatriks;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\PenilaianMatriks;
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

            $listIdParentNeedToBeCheck = [];
            foreach ($listMapping as $idPenilaianMatriks) {
                $penilaianMatriks = PenilaianMatriks::find($idPenilaianMatriks);
                while ($penilaianMatriks && $penilaianMatriks->id_parent) {
                    $listIdParentNeedToBeCheck[] = $penilaianMatriks->id_parent;
                    $penilaianMatriks = PenilaianMatriks::find($penilaianMatriks->id_parent);
                }
            }

            $listIdParentNeedToBeCheck = array_unique($listIdParentNeedToBeCheck);
            foreach ($listIdParentNeedToBeCheck as $idParentPenilaianMatriks) {
                $existingMapping = MappingPenilaianMatriks::where('id_unit', $targetIndikator->id_unit)
                    ->where('id_audit_periode', $targetIndikator->id_audit_periode)
                    ->where('id_penilaian_matriks', $idParentPenilaianMatriks)
                    ->first();
                if (!$existingMapping) {
                    MappingPenilaianMatriks::create([
                        'id_unit' => $targetIndikator->id_unit,
                        'id_audit_periode' => $targetIndikator->id_audit_periode,
                        'id_penilaian_matriks' => $idParentPenilaianMatriks,
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
