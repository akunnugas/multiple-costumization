<?php

namespace Modules\SPMI\Services\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Helpers\AssessmentFormula;
use Modules\SPMI\Models\TargetIndikator;
use Modules\SPMI\Models\TargetSkor;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianSkor;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;

trait AssessmentServiceTrait
{
    public function recalculateScore($scoreData, $assessmentData, $assessmentId)
    {
        // Unfinalisasi terlebih dahulu
        $model = $this->model->findOrFail($assessmentId);

        $countSuccess = 0;
        $countFailed = 0;
        foreach ($scoreData as $data) {
            $saveScore = $this->saveScoreAndAssessment($data, $assessmentData, $assessmentId);

            if (Error::isError($saveScore)) {
                $countFailed++;
            } else {
                $countSuccess++;
            }
        }

        return [
            'total_success' => $countSuccess,
            'total_failed' => $countFailed,
            'is_finalized' => $model->apakah_terfinalisasi
        ];
    }

    public function saveScoreAndAssessment(array $scoreData, array $assessmentData, int|null $assessmentId = null)
    {
        DB::beginTransaction();

        $selfMatrix = PenilaianMatriks::find($scoreData['id_penilaian_matriks']);
        $selfMatrixWeight = (float) $selfMatrix->bobot_penilaian;

        $selfTargetIndikator = TargetIndikator::where('id_audit_periode', $assessmentData['id_audit_periode'])
            ->where('id_unit', $assessmentData['id_unit'])
            ->where('id_penilaian_panduan', $assessmentData['id_penilaian_panduan'])
            ->when(!empty($assessmentData['id_jadwal_audit']), fn ($q) => $q->where('id_jadwal_audit', $assessmentData['id_jadwal_audit']))
            ->first();

        $selfTargetSkor = TargetSkor::where('id_target_indikator', $selfTargetIndikator->id)
            ->where('id_penilaian_matriks', $scoreData['id_penilaian_matriks'])
            ->first('nilai')?->nilai;

        $selfScore = (float) $scoreData['nilai'];

        $maxSkorPredikat = SkorMatriksPredikatPenilaian::where('id_penilaian_panduan', $assessmentData['id_penilaian_panduan'])
            ->max('nilai');
        $selfMaxTargetSkor = $maxSkorPredikat;

        // Jika ada bobot maka dikalikan dengan bobot
        if (!empty($selfMatrixWeight)) {
            $selfScore *= $selfMatrixWeight;
            $selfTargetSkor *= $selfMatrixWeight;
            $selfMaxTargetSkor *= $selfMatrixWeight;
        }

        if ($selfTargetSkor === null) {
            return new Error('Skor target belum diatur', 500);
        }

        $parentElementMatrix = $selfMatrix->getParentElement();

        if (isset($assessmentId)) {
            $assessment = PenilaianAudit::find($assessmentId);
        } else {
            $fields = [
                'id_unit',
                'id_audit_periode',
                'id_penilaian_panduan',
                'id_jadwal_audit',
                'apakah_penilaian_mandiri'
            ];

            try {
                $assessment = PenilaianAudit::firstOrCreate(
                    Arr::only($assessmentData, $fields),
                    $assessmentData
                );
            } catch (\Exception $e) {
                return new Error(exception: $e);
            }
        }

        try {
            $amountScores = PenilaianSkor::where('id_penilaian_audit', $assessmentId)
                ->where('id_penilaian_matriks', $scoreData['id_penilaian_matriks'])
                ->count();
            if ($amountScores > 1) {
                PenilaianSkor::where('id_penilaian_audit', $assessmentId)
                    ->where('id_penilaian_matriks', $scoreData['id_penilaian_matriks'])
                    ->orderBy('waktu_dibuat', 'desc')
                    ->skip(1)
                    ->take($amountScores - 1)
                    ->delete();
            }
            $assessmentIndicatorScore = PenilaianSkor::updateOrCreate(
                [
                    'id_penilaian_audit' => $assessment->id,
                    'id_penilaian_matriks' => $scoreData['id_penilaian_matriks']
                ],
                [
                    'id_penilaian_audit' => $assessment->id,
                    'nilai_target' => $selfTargetSkor,
                    'nilai_akhir' => $selfScore,
                    'max_nilai_target' => $selfMaxTargetSkor,
                    ...$scoreData
                ]
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        if ($parentElementMatrix) {
            switch ($parentElementMatrix['jenis_penilaian']) {
                case PenilaianMatriks::TYPE_FINAL_SCORE:
                    $result = $this->processElementFinalScore(
                        $assessment,
                        $assessmentIndicatorScore,
                        $parentElementMatrix
                    );
                    break;
                case PenilaianMatriks::TYPE_ACCUMULATION:
                    $result = $this->processElementAccumulation(
                        $assessment,
                        $parentElementMatrix,
                    );
                    break;
                default:
                    break;
            }

            if (isset($result) && Error::isError($result)) {
                DB::rollBack();
                return $result;
            }
        }

        // Hitung semua skor akhir element info_level 0
        $finalElementScore = $this->sumFinalScoreByElement($assessment);
        $totalFinding = PenilaianSkor::where('id_penilaian_audit', $assessment->id)
            ->whereIn('status_penilaian', [PenilaianSkor::STATUS_BELUM_MEMENUHI, PenilaianSkor::STATUS_MENYIMPANG])
            ->count();

        $totalIndikatorTerisi = PenilaianSkor::where('id_penilaian_audit', $assessment->id)->where('nilai', '!=', null)->count();

        try {
            $assessment->update([
                'nilai_akhir' => $finalElementScore,
                'total_temuan' => $totalFinding,
                'total_indikator_terisi' => $totalIndikatorTerisi
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();

        return $assessment;
    }

    public function countScoreQuantitative(mixed $penilaianMatriksId, mixed $auditPeriodId, mixed $studyProgramId, string|null $rumusPenilaian = "", array|null $penilaianMatriksScores = [])
    {
        $studyProgram = UnitKerja::find($studyProgramId);
        $sourceData = AssessmentFormula::getSourceData($penilaianMatriksId, $auditPeriodId, $studyProgramId);

        $additionalData = [
            'id_periode_audit' => $auditPeriodId,
            'unit_kerja' => $studyProgram->toArray()
        ];

        $rumusPenilaianBuilder = new AssessmentFormula($sourceData, $additionalData);

        $result = [];

        if (empty($rumusPenilaian)) {
            return [];
        }

        // Ambil rumus_penilaian utama
        $result = $rumusPenilaianBuilder->calculate($rumusPenilaian);

        if (Error::isError($result)) {
            return $result;
        }

        $result['show'] = array_map(function ($item) {
            if (is_numeric($item)) {
                return number_format($item, 2, '.', ',');
            }

            return $item;
        }, $result['show']);

        $result['selected'] = [];

        // Mulai hitung rumus_penilaian skor akhir
        if (!empty($result)) {
            $scoreCriteriaResult = [];
            $allCriteriaResult = [];
            foreach ($penilaianMatriksScores as $penilaianMatriksScore) {
                $penilaianMatriksScore = (array) $penilaianMatriksScore;
                $kriteria = $penilaianMatriksScore['kriteria'];
                $defaultScore = $penilaianMatriksScore['nilai'];

                $kriteriaResult = $rumusPenilaianBuilder->calculate($penilaianMatriksScore['rumus_penilaian'], $result['data'] ?? []);

                if (Error::isError($kriteriaResult)) {
                    return $kriteriaResult;
                }

                $scoreResult = $kriteriaResult['data'][$kriteria] ?? 0;
                $isDisable = $penilaianMatriksScore['apakah_nonaktif'] ?? false;

                $intResult = (int) $scoreResult;

                $resultData = [
                    'nilai' => $penilaianMatriksScore['nilai'],
                    'nilai_decimal' => $scoreResult,
                    'nilai_hasil' => $intResult,
                    'apakah_nonaktif' => $isDisable,
                ];

                if (empty($scoreCriteriaResult) && ($defaultScore == $intResult)) {
                    // Ambil nilai decimal dari perhitungan terakhir yang tidak kosong
                    $recentDecimalScores = array_column($allCriteriaResult, 'nilai_decimal');
                    $recentDecimalScore = empty($recentDecimalScores) ? 0 : max($recentDecimalScores);
                    $resultData['nilai_decimal'] = (int) $recentDecimalScore == $intResult ? $recentDecimalScore : $scoreResult;

                    // Set skor akhir
                    $scoreCriteriaResult = $resultData;
                }

                $allCriteriaResult[] = $resultData;
            }

            // Jika skor akhir ada tetapi terdisable maka unset
            if (!empty($scoreCriteriaResult) && $scoreCriteriaResult['apakah_nonaktif']) {
                $scoreCriteriaResult  = [];
            }

            // Jika skor kosong, maka ambil dibulatkan satu angka keatas
            if (empty($scoreCriteriaResult)) {
                $mappingAllCriteriaResult = array_map(fn ($item) => $item['nilai_hasil'], $allCriteriaResult);
                $kriteriaMaxResult = max($mappingAllCriteriaResult);

                // Pembulatan keatas dan kebawah
                $roundDown = $this->filterRoundScore($allCriteriaResult, $kriteriaMaxResult, 'down');
                $roundUp = $this->filterRoundScore($allCriteriaResult, $kriteriaMaxResult, 'up');

                // Ambil nilai minimal
                $mappingAllCriteriaScore = array_map(function ($item) {
                    if (!$item['apakah_nonaktif']) {
                        return $item['nilai'];
                    }

                    return null;
                }, $allCriteriaResult);
                $mappingAllCriteriaScore = array_values(array_filter($mappingAllCriteriaScore));
                $kriteriaMinScore = min($mappingAllCriteriaScore);
                $filteredMinScore = array_filter($allCriteriaResult, fn ($item) => $item['nilai'] == $kriteriaMinScore && !$item['apakah_nonaktif']);
                $filteredMinScore = array_map(function ($item) {
                    $item['nilai_hasil'] = $item['nilai'];
                    $item['nilai_decimal'] = $item['nilai'];
                    return $item;
                }, $filteredMinScore);
                $filteredMinScore = array_values($filteredMinScore)[0] ?? [];

                $roundedResult = empty($roundUp) ? $roundDown : $roundUp;

                // Jika pembulatan kosong, maka ambil nilai minimal
                $result['selected'] = empty($roundedResult) ? $filteredMinScore : $roundedResult;
                $result['selected'] = $result['selected'] ?? [];
            } else {
                $result['selected'] = $scoreCriteriaResult;
            }

            if (!empty($result['selected'])) {
                $result['selected']['nilai_decimal'] = number_format($result['selected']['nilai_decimal'], 2, '.', ',');
            }
        }

        // Jika hasilnya skor 4 maka decimalnya tidak boleh lebih dari 4
        if ($result['selected']['nilai'] >= 4) {
            $result['selected']['nilai_decimal'] = '4.00';
        }

        return $result;
    }

    protected function processElementFinalScore($assessment, $assessmentIndicatorScore, $parentElementMatrix)
    {
        $elementScore = PenilaianSkor::where('id_penilaian_audit', $assessment->id)
            ->where('id_penilaian_matriks', $parentElementMatrix->id)->first();

        $result = $this->updateTotalScore($assessmentIndicatorScore, $parentElementMatrix, $assessment, $elementScore);
        return $result;
    }

    protected function updateTotalScore($assessmentIndicatorScore, $parentElementMatrix, $assessment, $elementScore)
    {
        $finalElementScore = $assessmentIndicatorScore->nilai_akhir;
        $finalElementTargetScore = $assessmentIndicatorScore->nilai_target;
        $maxFinalElementTargetScore = $assessmentIndicatorScore->max_nilai_target;

        // Jika ada sudah ada skor elemen, maka sum semua skor children
        if (isset($elementScore)) {
            $parentChildren = $this->getScoreBetweenTree($assessment, $parentElementMatrix, category: PenilaianMatriks::CATEGORY_INDICATOR);
            $finalElementScore = array_sum(array_map(fn ($item) => $item->nilai_akhir, $parentChildren));
            $finalElementTargetScore = array_sum(array_map(fn ($item) => $item->nilai_target, $parentChildren));
            $maxFinalElementTargetScore = array_sum(array_map(fn ($item) => $item->max_nilai_target, $parentChildren));
        }

        try {
            $assessmentElementFinalScore = PenilaianSkor::updateOrCreate(
                [
                    'id_penilaian_audit' => $assessment->id,
                    'id_penilaian_matriks' => $parentElementMatrix->id
                ],
                [
                    'id_penilaian_audit' => $assessment->id,
                    'nilai_akhir' => $finalElementScore,
                    'nilai_target' => $finalElementTargetScore,
                    'max_nilai_target' => $maxFinalElementTargetScore
                ]
            );
        } catch (\Exception $e) {
            return new Error('Terjadi kesalahan saat menyimpan skor', 500);
        }

        return $assessmentElementFinalScore;
    }

    protected function processElementAccumulation($assessment, $parentElementMatrix)
    {
        // Ambil parent matriks yang berjenis final score
        $parentElementMatrixFinalScore = $parentElementMatrix->getParentElement(PenilaianMatriks::TYPE_FINAL_SCORE);

        if (!$parentElementMatrixFinalScore) {
            return null;
        }

        // Ambil semua indikator setelah info_right > parent info_right
        $sql = "SELECT
                    sc.nilai_akhir,
                    sc.nilai_target,
                    sc.max_nilai_target
                FROM spmi.penilaian_matriks pm
                LEFT JOIN spmi.penilaian_skor sc ON sc.id_penilaian_audit = :id_penilaian_audit
                    AND sc.id_penilaian_matriks = pm.id
                WHERE pm.info_left > :info_left
                    AND pm.info_right < :info_right
                    AND pm.waktu_dihapus is null";

        $scoreData = DB::select($sql, [
            'id_penilaian_audit' => $assessment->id,
            'info_left' => $parentElementMatrix->info_left,
            'info_right' => $parentElementMatrix->info_right
        ]);

        $scoreData = array_values($scoreData);
        $scoreData = array_map(function ($item, $key) {
            $item = (array) $item;
            $item['nilai_akhir'] = (float) $item['nilai_akhir'];
            $item['nilai_target'] = (float) $item['nilai_target'];
            $item['max_nilai_target'] = (float) $item['max_nilai_target'];
            $item['key'] = 'A' . ($key + 1);
            return $item;
        }, $scoreData, array_keys($scoreData));

        $scoreDataColumn = array_column($scoreData, 'nilai_akhir', 'key');
        $targetScoreDataColumn = array_column($scoreData, 'nilai_target', 'key');
        $maxTargetScoreDataColumn = array_column($scoreData, 'max_nilai_target', 'key');

        $rumusPenilaianBuilder = new AssessmentFormula();
        $rumusPenilaian = $parentElementMatrix->rumus_penilaian;
        $weight = (float) $parentElementMatrix->bobot_penilaian;

        if (empty($rumusPenilaian)) {
            // Jika kosong maka jumlahkan semua
            $finalAccumulationScore = array_sum($scoreDataColumn);
            $finalAccumulationTargetScore = array_sum($targetScoreDataColumn);
            $maxFinalElementTargetScore = array_sum($maxTargetScoreDataColumn);
        } else {
            // Hitung akumulasi skor
            $result = $rumusPenilaianBuilder->calculate($rumusPenilaian, $scoreDataColumn);
            $resultTarget = $rumusPenilaianBuilder->calculate($rumusPenilaian, $targetScoreDataColumn);
            $resultMaxTarget = $rumusPenilaianBuilder->calculate($rumusPenilaian, $maxTargetScoreDataColumn);

            if (Error::isError($result)) {
                return $result;
            }


            $finalAccumulationScore = $result['data']['__ACCUMULATION__'] ?? 0;
            $finalAccumulationTargetScore = $resultTarget['data']['__ACCUMULATION__'] ?? 0;
            $maxFinalElementTargetScore = $resultMaxTarget['data']['__ACCUMULATION__'] ?? 0;
        }

        // Jika ada bobot maka dikalikan dengan bobot
        if (!empty($weight)) {
            $finalAccumulationScore *= $weight;
            $finalAccumulationTargetScore *= $weight;
            $maxFinalElementTargetScore *= $weight;
        }

        // Update skor akumulasi
        try {
            PenilaianSkor::updateOrCreate(
                [
                    'id_penilaian_audit' => $assessment->id,
                    'id_penilaian_matriks' => $parentElementMatrix->id
                ],
                [
                    'id_penilaian_audit' => $assessment->id,
                    'nilai_akhir' => $finalAccumulationScore,
                    'nilai_target' => $finalAccumulationTargetScore,
                    'max_nilai_target' => $maxFinalElementTargetScore
                ]
            );
        } catch (\Exception $e) {
            return new Error('Terjadi kesalahan saat menyimpan skor', 500);
        }

        // sum children skor akhir
        $parentChildren = $this->getScoreBetweenTree($assessment, $parentElementMatrixFinalScore, PenilaianMatriks::TYPE_ACCUMULATION);
        $finalElementScore = array_sum(array_map(fn ($item) => $item->nilai_akhir, $parentChildren));
        $finalElementTargetScore = array_sum(array_map(fn ($item) => $item->nilai_target, $parentChildren));
        $finalElementMaxTargetScore = array_sum(array_map(fn ($item) => $item->max_nilai_target, $parentChildren));

        try {
            $parentElementMatrixFinalScore = PenilaianSkor::updateOrCreate(
                [
                    'id_penilaian_audit' => $assessment->id,
                    'id_penilaian_matriks' => $parentElementMatrixFinalScore->id
                ],
                [
                    'id_penilaian_audit' => $assessment->id,
                    'nilai_akhir' => $finalElementScore,
                    'nilai_target' => $finalElementTargetScore,
                    'max_nilai_target' => $finalElementMaxTargetScore
                ]
            );
        } catch (\Exception $e) {
            return new Error('Terjadi kesalahan saat menyimpan skor', 500);
        }

        return $parentElementMatrixFinalScore;
    }

    protected function getAlphabet($index)
    {
        $alphabet = range('A', 'Z');
        $length = count($alphabet);

        if ($index < 0) {
            return '';
        }

        if ($index < $length) {
            return $alphabet[$index];
        }

        $result = '';
        $index = $index + 1;
        while ($index > 0) {
            $mod = ($index - 1) % $length;
            $result = $alphabet[$mod] . $result;
            $index = ($index - $mod) / $length;
        }

        return $result;
    }

    protected function getScoreBetweenTree($assessment, $parentElementMatrix, $type = null, $category = null)
    {
        $sql = "SELECT
                    sc.*
                FROM spmi.penilaian_matriks pm
                LEFT JOIN spmi.penilaian_skor sc ON sc.id_penilaian_audit = :id_penilaian_audit
                    AND sc.id_penilaian_matriks = pm.id
                WHERE pm.info_left > :info_left AND pm.info_right < :info_right
                    AND pm.waktu_dihapus IS NULL";

        $bindings = [
            'id_penilaian_audit' => $assessment->id,
            'info_left' => $parentElementMatrix->info_left,
            'info_right' => $parentElementMatrix->info_right
        ];

        if (isset($type)) {
            $sql .=
                " AND pm.jenis_penilaian = :jenis_penilaian";
            $bindings['jenis_penilaian'] = $type;
        }

        if (isset($category)) {
            $sql .=
                " AND pm.kategori_penilaian = :kategori_penilaian";
            $bindings['kategori_penilaian'] = $category;
        }

        $sql .= " AND pm.waktu_dihapus is null";
        $data = DB::select($sql, $bindings);

        return $data;
    }

    /**
     * Menghitung elemen skor akhir
     */
    protected function sumFinalScoreByElement($assessment)
    {
        $data = DB::select(
            "SELECT
                SUM(sc.nilai_akhir) AS nilai_akhir
            FROM spmi.penilaian_skor AS sc
            JOIN spmi.penilaian_matriks pm ON pm.id = sc.id_penilaian_matriks
                AND pm.waktu_dihapus is null
            WHERE sc.id_penilaian_audit = :id_penilaian_audit
                AND pm.jenis_penilaian = :jenis_penilaian",
            [
                'id_penilaian_audit' => $assessment->id,
                'jenis_penilaian' => PenilaianMatriks::TYPE_FINAL_SCORE
            ]
        );

        if (empty($data)) {
            return 0;
        }

        return $data[0]->nilai_akhir;
    }

    protected function filterRoundScore($allCriteriaResult, $kriteriaMaxResult, $type = 'up')
    {
        // Jika up maka order semua skor dari terkecil ke terbesar
        if ($type == 'up') {
            usort($allCriteriaResult, function ($a, $b) {
                return $a['nilai'] <=> $b['nilai'];
            });
        } else {
            usort($allCriteriaResult, function ($a, $b) {
                return $b['nilai'] <=> $a['nilai'];
            });
        }

        $result = [];
        foreach ($allCriteriaResult as $kriteriaResult) {
            $isDisable = !$kriteriaResult['apakah_nonaktif'] ?? false;
            if ($kriteriaResult['nilai'] == $kriteriaMaxResult && $isDisable) {
                $result = $kriteriaResult;
                break;
            }

            if ($type == 'up') {
                if ($kriteriaResult['nilai'] > $kriteriaMaxResult && $isDisable) {
                    $result = $kriteriaResult;
                    break;
                }
            } else {
                if ($kriteriaResult['nilai'] < $kriteriaMaxResult && $isDisable) {
                    $result = $kriteriaResult;
                    break;
                }
            }
        }

        if (!empty($result)) {
            $result['nilai_hasil'] = $result['nilai'];
            $result['nilai_decimal'] = $result['nilai'];
        }

        return $result;
    }
}
