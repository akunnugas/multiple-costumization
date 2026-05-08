<?php

namespace Modules\SPMI\Data\PenilaianMatriks;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksPredikat;

abstract class MigrateFormula
{
    protected string $assessmentGuideCode = '';

    /**
     * Start migrate formula
     *
     * @return bool|Error
     */
    public function startMigrate(): bool|Error
    {
        foreach ($this->getRumus() as $code => $indicatorFormula) {
            $penilaianMatriks = self::findAssessmentMatix($code, $this->assessmentGuideCode);

            if (!empty($penilaianMatriks)) {
                $update = $this->updateIndicatorFormula($penilaianMatriks, $indicatorFormula);

                if ($update instanceof Error) {
                    return $update;
                }
            }
        }

        return true;
    }

    public static function getRumusPenilaianByCode($kodeMatriksPenilaian, $replaceWords = [])
    {
        $rumusRaw = (new static())->getRumus()[$kodeMatriksPenilaian] ?? null;

        if (!empty($replaceWords)) {
            $newRumusPenilaian = $rumusRaw['rumus_penilaian'];

            foreach ($replaceWords as $key => $value) {
                $escapedKey = preg_quote($key, '/');
                $newRumusPenilaian = preg_replace("/$escapedKey/", $value, $newRumusPenilaian);
            }

            $rumusRaw['rumus_penilaian'] = $newRumusPenilaian;
        }

        return $rumusRaw;
    }

    protected function getRumus(): array
    {
        return [];
    }

    protected function updateIndicatorFormula($penilaianMatriks, $indicatorFormulaItem): bool|Error
    {
        $penilaianMatriksId = $penilaianMatriks['id'];
        $indicatorFormula = $indicatorFormulaItem['rumus_penilaian'] ?? null;
        $scoreFormulas = $indicatorFormulaItem['rumus_skor'] ?? [];

        $isHasNoError = true;

        DB::beginTransaction();

        // Update formula matrix utama
        $updatedMatrix = PenilaianMatriks::find($penilaianMatriksId);

        try {
            $updatedMatrix->update([
                'rumus_penilaian' => str_replace(["\t"], '', $indicatorFormula),
                'apakah_aktif' => $updatedMatrix->apakah_aktif == true ? true : false
            ]);

            $isHasNoError = true;
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        // Update formula matrix skor
        foreach ($scoreFormulas as $score => $item) {
            $matrixScoreId = PenilaianMatriksPredikat::join('spmi.skor_matriks_predikat_penilaian', 'spmi.skor_matriks_predikat_penilaian.id', '=', 'spmi.penilaian_matriks_predikat.id_skor_matriks_predikat_penilaian')
                ->where('spmi.penilaian_matriks_predikat.id_penilaian_matriks', $penilaianMatriksId)
                ->where('spmi.skor_matriks_predikat_penilaian.nilai', $score)
                ->select('spmi.penilaian_matriks_predikat.id')
                ->value('id');

            if (!empty($matrixScoreId)) {
                try {
                    PenilaianMatriksPredikat::where('id', $matrixScoreId)->update([
                        'rumus_penilaian' => str_replace(["\t"], '', $item['rumus_penilaian'] ?? null),
                        'kriteria' => $item['kriteria'] ?? null
                    ]);

                    $isHasNoError = true;
                } catch (\Exception $e) {
                    DB::rollBack();
                    return new Error(exception: $e);
                }
            }
        }

        DB::commit();
        return $isHasNoError;
    }

    protected static function findAssessmentMatix($kodeMatriksPenilaian, $kodePanduanPenilaian)
    {
        $sql =
            "SELECT
                pm.*
            FROM
                spmi.penilaian_panduan ag
                LEFT JOIN spmi.penilaian_matriks pm ON pm.id_penilaian_panduan = ag.id
                    AND pm.waktu_dihapus IS NULL
            WHERE
                ag.kode_penilaian_panduan = :kode_penilaian_panduan
                AND pm.nomor_penilaian = :nomor_penilaian
            LIMIT 1";

        $penilaianMatriks = DB::select($sql, [
            'nomor_penilaian' => $kodeMatriksPenilaian,
            'kode_penilaian_panduan' => $kodePanduanPenilaian
        ]);
        $penilaianMatriks = $penilaianMatriks[0] ?? null;

        return (array)$penilaianMatriks;
    }
}
