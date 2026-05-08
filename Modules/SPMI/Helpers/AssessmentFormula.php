<?php

namespace Modules\SPMI\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use MathParser\Exceptions\DivisionByZeroException;
use MathParser\Exceptions\MathParserException;
use MathParser\StdMathParser;
use MathParser\Interpreting\Evaluator;
use Modules\Core\Helpers\Error;
use Illuminate\Support\Str;
use Modules\SPMI\Models\AuditPeriode;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class AssessmentFormula
{
    public $sourceData = [];

    protected static $additionalData = [];
    protected static $mathParser = null;
    protected static $mathEvaluator = null;
    protected static $expressionLanguage = null;

    public function __construct($sourceData = [], $additionalData = [])
    {
        $this->sourceData = $sourceData;

        self::$additionalData = $additionalData;
        self::$mathParser = new StdMathParser();
        self::$mathEvaluator = new Evaluator();
        self::$expressionLanguage = new ExpressionLanguage();
    }

    /**
     * Calculate formula
     *
     * @param string $formula
     * @param array $bindings
     *
     * @return array
     */
    public function calculate($formula, $bindings = []): array|Error
    {
        // Contoh penggunaan
        // N1 = COUNT('1a', C1, {X2}{Y1}:{X2});
        // N2 = COUNT('1a', C2, {X2}{Y1}:{X2});
        // N3 = COUNT('1a', C3, {X2}{Y1}:{X2});
        // NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, 1);
        // RK = ((2 * [N1]) + (1 * [N2]) + (3 * [N3])) / [NDTPS];
        // X = CASES([RK] < 4, [RK]);
        // AVERAGE = AVG_Y('1a', C1, {X7}{Y1}:{X10}, WITH_NULL);
        // NI = COUNT_WHERE('8b.1', null, {X3}{Y1}:{X6},
        //     {X3} >= [TS-2],
        //     {X6} == '1'
        // );

        $timeStart = microtime(true);

        $formula = $this->cleanWhitespaces($formula);
        $result = $this->processFormula($formula, $this->sourceData, $bindings);

        if (Error::isError($result)) {
            return $result;
        }

        [$result, $show] = $result;

        $timeEnd = microtime(true);
        $executionTime = ($timeEnd - $timeStart);

        return [
            'data' => $result,
            'show' => $show,
            'execution_time' => number_format($executionTime, 5, '.', '')
        ];
    }

    /**
     * Get source data
     *
     * @param int $idPenilaianMatriks
     * @param int $idPeriodeAudit
     * @return array
     */
    public static function getSourceData($idPenilaianMatriks, $idPeriodeAudit, $idUnit)
    {
        $sourceData = DB::select(
            "SELECT
                fid.id,
                pr.nomor_indikator,
                fid.data_pengisian_lk AS rows
            FROM
                spmi.penilaian_matriks_referensi pmr
                JOIN spmi.indikator_laporan_kinerja pr ON pr.id = pmr.id_butir_referensi
                    AND pr.waktu_dihapus IS NULL
                JOIN spmi.pengisian_indikator fi ON fi.id_audit_periode = :id_audit_periode
                    AND fi.id_unit = :id_unit
                    AND fi.waktu_dihapus IS NULL
                JOIN spmi.data_pengisian_lk fid ON fid.id_indikator_laporan_kinerja = pr.id
                    AND fid.id_pengisian_indikator = fi.id
                    AND fid.waktu_dihapus IS NULL
            WHERE pmr.id_penilaian_matriks = :id_penilaian_matriks",
            [
                'id_audit_periode' => $idPeriodeAudit,
                'id_penilaian_matriks' => $idPenilaianMatriks,
                'id_unit' => $idUnit
            ]
        );

        return self::mapSourceData($sourceData);
    }

    /**
     * Prosess perhitungan formula
     *
     * @param string $formula
     * @param array $sourceData
     * @param array $bindings
     *
     * @return array
     */
    protected static function processFormula($formula, $sourceData, $bindings = [])
    {
        // Parsing ulang formula
        $formulas = self::parseFormula($formula);

        if (Error::isError($formulas)) {
            return $formulas;
        }

        $resultFunctions = [
            ...$bindings
        ];

        $forShowData = [];

        foreach ($formulas as $item) {
            $formula = $item['formula'];
            $originalFormula = $item['original_formula'];
            $functions = $item['functions'];
            $aliases = $item['aliases'];

            $mappingShowData = self::mapForShowData($aliases, $formula, $resultFunctions, $item);

            if (!empty($mappingShowData)) {
                $forShowData = $mappingShowData;
                continue;
            }

            // Replace operator penggunaan data seperti [F1]
            $formula = self::replaceDataInFormula($formula, $resultFunctions);
            $functions = self::getFunctionArguments($formula, $originalFormula);

            $sourceDefinitions = self::getDefinitions($functions, $sourceData, $resultFunctions);

            if (Error::isError($sourceDefinitions)) {
                return $sourceDefinitions;
            }

            // Replace formula dengan hasil definisi fungsi
            foreach ($sourceDefinitions as $sourceDefinition) {
                $formula = str_replace($sourceDefinition['formula'], $sourceDefinition['result'], $formula);
            }

            // Jika boolean true atau false maka langsung return
            if ($formula === 'true' || $formula === 'false') {
                $resultFunctions[$aliases] = $formula;
                continue;
            }

            $isValidString = self::isValidString($formula);

            if (
                (empty($sourceDefinitions) && $isValidString) || $isValidString
            ) {
                $resultFunctions[$aliases] = $formula;
                continue;
            }

            $calculateResult = self::calculateFormula($formula);

            if (Error::isError($calculateResult)) {
                return $calculateResult;
            }

            $formatNumber = number_format($calculateResult, 4, '.', '');
            $result = floatval($formatNumber);

            $resultFunctions[$aliases] = $result;
        }

        return [$resultFunctions, $forShowData];
    }

    /**
     * Mengambil data untuk ditampilkan
     *
     * @param string $aliases
     * @param string $formula
     * @param array $resultFunctions
     *
     * @return array|false
     */
    protected static function mapForShowData($aliases, $formula, $resultFunctions = [])
    {
        if ($resultFunctions[$aliases] ?? false) {
            return false;
        }

        if (strpos($aliases, '__SHOW__') !== false) {
            $pattern = '/\[(.*?)\]/';

            preg_match_all($pattern, $formula, $matches);
            $matchReplaceKeys = $matches[1] ?? [];

            $extracted = [];
            foreach ($matchReplaceKeys as $key) {
                [$key, $aliases] = array_pad(explode('_AS_', $key, 2), 2, null);
                $value = $resultFunctions[$key] ?? 0;
                $arrayKey = $key;

                if(!empty($aliases)) {
                    $arrayKey = trim($aliases, "'");
                }

                // Cek apakah string value
                $stringValue = self::stringValue($value);
                if ($stringValue !== false) {
                    $value = $stringValue;
                }

                $extracted[$arrayKey] = $value;
            }

            return $extracted;
        }

        return false;
    }

    /**
     * Replace operator penggunaan data seperti [F1] dengan data yang telah dibinding
     *
     * @param string $formula
     * @param array $accessableData
     *
     * @return string
     */
    protected static function replaceDataInFormula($formula, $accessableData)
    {
        $pattern = '/\[(.*?)\]/';

        preg_match_all($pattern, $formula, $matches);
        $matchReplaceKeys = $matches[1] ?? [];

        foreach ($matchReplaceKeys as $key => $value) {
            $data = $accessableData[$value] ?? 0;
            $formula = str_replace($matches[0][$key], $data, $formula);
        }

        return $formula;
    }

    /**
     * Parsing formula
     *
     * @param string $formula
     * @param array $only
     * @param array $except
     */
    protected static function parseFormula($formula, $only = [], $except = [])
    {
        $errParse = false;

        $formulas = explode(';', $formula);
        $formulas = array_filter($formulas, function ($value) {
            return !empty($value);
        });
        $formulas = array_map(function ($value) use (&$errParse) {
            $value = str_replace(['==', '>=', '<=', '>', '<', '&', '|', '!='], ['^eq^', '^gte^', '^lte^', '^gt^', '^lt^', '^and^', '^or^', '^not^'], $value);
            $splited = explode('=', $value);
            $aliases = $splited[0] ?? null;
            $formula = $splited[1] ?? null;
            $originalFormula = str_replace(['^eq^', '^gte^', '^lte^', '^gt^', '^lt^', '^and^', '^or^', '^not^'], ['==', '>=', '<=', '>', '<', '&', '|', '!='], $formula);

            if ((empty($aliases) || empty($formula)) && ($formula !== '0' && $formula !== 0)) {
                $errParse = true;
                return;
            }

            return [
                'aliases' => $aliases,
                'formula' => $formula,
                'original_formula' => $originalFormula,
            ];
        }, $formulas);

        if ($errParse) {
            return new Error('Terjadi kesalahan saat mengurai formula');
        }

        foreach ($formulas as $key => $value) {
            $functions = self::getFunctionArguments(
                $value['formula'],
                $value['original_formula'],
                $only,
                $except
            );

            if (Error::isError($functions)) {
                return $functions;
            }

            $formulas[$key]['functions'] = $functions;
        }

        return $formulas;
    }

    /**
     * Menghitung formula
     *
     * @param string $formula
     *
     * @return float|int|Error
     */
    protected static function calculateFormula($formula)
    {
        // Deteksi angka scientific (notasi E) dan konversi ke desimal
        $formula = preg_replace_callback('/\b\d+\.?\d*E[+-]?\d+\b/i', function ($matches) {
            // Konversi ke string desimal
            return sprintf('%.0f', $matches[0]);
        }, $formula);

        try {
            $result = self::$mathParser->parse($formula)?->accept(self::$mathEvaluator);
        } catch (MathParserException $e) {
            if ($e instanceof DivisionByZeroException) {
                return 0;
            }

            return new Error('Terjadi kesalahan saat mengurai formula, ' . PHP_EOL .
                'Error formula: ' . $formula);
        }

        return $result;
    }

    /**
     * Membersihkan formula
     *
     * @param string $formula
     *
     * @return string
     */
    protected static function cleanUpFormula($formula)
    {
        // Hapus semua karakter selain angka, operator, dan tanda kurung
        $pattern = '/[^0-9\+\-\.\*\/\(\)]/';
        $formula = preg_replace($pattern, '', $formula);

        // Hapus operator () yang tidak memiliki value di dalamnya
        $pattern = '/\(\)/';
        $formula = preg_replace($pattern, '', $formula);

        // Jika setelah operator tidak terdapat angka, maka hapus operator
        $pattern = '/[\+\-\*\/]\s*$/';
        $formula = preg_replace($pattern, '', $formula);

        // Cleaning operator
        $formula = self::replaceConsecutiveOperators($formula);
        return $formula;
    }

    /**
     * Mendefinisikan fungsi
     *
     * @param array $functions
     * @param array $sourceData
     *
     * @return array|Error
     */
    protected static function getDefinitions($functions, $sourceData)
    {
        $mappedSources = [];

        foreach ($functions as $function) {
            $operation = $function['type'];

            $filterIncludeRangeSymbol = array_filter($function['arguments'], function ($value) {
                return strpos($value, ':') !== false;
            });

            if ($operation == 'cases' && !empty($filterIncludeRangeSymbol)) {
                return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                    'Error fungsi ' . $function['original_formula'] . ': ' . 'Tidak boleh menggunakan rentang data pada fungsi CASES');
            }

            $result = self::processOperationResult($function, $sourceData, $operation);

            if (Error::isError($result)) {
                return $result;
            }

            $mappedSources[] = [
                'formula' => $function['formula'],
                'result' => $result
            ];
        }

        return $mappedSources;
    }

    /**
     * Proses hasil operasi fungsi
     *
     * @param array $functions
     * @param array $sourceData
     * @param string $operation
     *
     * @return float|int|Error
     */
    protected static function processOperationResult($functions = [], $sourceData = [], $operation = '')
    {
        $camelOperation = Str::camel($operation);
        $selfFunction = "$camelOperation" . "Result";
        $isExistsMethod = method_exists(self::class, $selfFunction);

        if ($isExistsMethod) {
            return self::$selfFunction($functions, $sourceData, $operation);
        }

        return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
            'Error fungsi ' . $functions['original_formula'] . ': ' . 'Operasi ' . $operation . ' tidak ditemukan');
    }

    /**
     * Proses hasil operasi fungsi SUM
     *
     * @param array $functions
     * @param array $sourceData
     * @param string $operation
     *
     * @return float|int|Error
     */
    protected static function sumResult($functions, $sourceData, $operation)
    {
        $keyNumber = $functions['arguments'][0] ?? null;
        $keyNumber = self::stringValue($keyNumber);

        if ($keyNumber === false) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen pertama harus berupa string yang valid');
        }

        $category = $functions['arguments'][1] ?? null;
        $rangeArgs = $functions['arguments'][2] ?? null;

        $rangeData = self::getRange($keyNumber, $category, $rangeArgs, $sourceData, $operation);

        $proceededData = array_values(array_filter($rangeData));
        return array_sum($proceededData);
    }

    /**
     * Proses hasil operasi fungsi COUNT
     *
     * @param array $functions
     * @param array $sourceData
     * @param string $operation
     *
     * @return float|int|Error
     */
    protected static function countResult($functions, $sourceData, $operation)
    {
        $keyNumber = $functions['arguments'][0] ?? null;
        $keyNumber = self::stringValue($keyNumber);

        if ($keyNumber === false) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen pertama harus berupa string yang valid');
        }

        $category = $functions['arguments'][1] ?? null;
        $rangeArgs = $functions['arguments'][2] ?? null;

        $rangeData = self::getRange($keyNumber, $category, $rangeArgs, $sourceData, $operation);

        $filterNotFilled = array_filter($rangeData, function ($value) {
            return $value !== null;
        });

        $proceededData = array_values($filterNotFilled);
        return count($proceededData);
    }

    /**
     * Proses hasil operasi fungsi COUNT_EMPTY
     *
     * @param array $functions
     * @param array $sourceData
     * @param string $operation
     *
     * @return float|int|Error
     */
    protected static function countEmptyResult($functions, $sourceData, $operation)
    {
        $keyNumber = $functions['arguments'][0] ?? null;
        $keyNumber = self::stringValue($keyNumber);

        if ($keyNumber === false) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen pertama harus berupa string yang valid');
        }

        $category = $functions['arguments'][1] ?? null;
        $rangeArgs = $functions['arguments'][2] ?? null;

        $rangeData = self::getRange($keyNumber, $category, $rangeArgs, $sourceData, $operation);

        $filterEmpty = array_filter($rangeData, function ($value) {
            return empty($value);
        });

        $proceededData = array_values($filterEmpty);
        return count($proceededData);
    }

    /**
     * Proses hasil operasi fungsi COUNT_CATEGORY
     *
     * @param array $functions
     * @param array $sourceData
     * @param string $operation
     *
     * @return float|int|Error
     */
    protected static function countCategoryResult($functions, $sourceData, $operation)
    {
        $keyNumber = $functions['arguments'][0] ?? null;
        $keyNumber = self::stringValue($keyNumber);

        if ($keyNumber === false) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen pertama harus berupa string yang valid');
        }

        $sources = $sourceData[$keyNumber] ?? "[]";
        $sources = (array) json_decode($sources);
        $sources = array_values($sources);

        return count($sources);
    }

    /**
     * Proses hasil operasi fungsi COUNT_IF
     *
     * @param array $functions
     * @param array $sourceData
     * @param string $operation
     *
     * @return float|int|Error
     */
    protected static function countIfResult($functions, $sourceData, $operation)
    {
        $keyNumber = $functions['arguments'][0] ?? null;
        $keyNumber = self::stringValue($keyNumber);

        if ($keyNumber === false) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen pertama harus berupa string yang valid');
        }

        $category = $functions['arguments'][1] ?? null;
        $rangeArgs = $functions['arguments'][2] ?? null;

        $rangeData = self::getRange($keyNumber, $category, $rangeArgs, $sourceData, $operation);
        $rangeData = array_values(array_filter(
            $rangeData,
            function ($value) {
                return $value !== null;
            }
        ));

        $condition = $functions['arguments'][3] ?? null;
        $condition = self::stringOrNumericValue($condition);

        if ($condition === false) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen keempat harus berupa string atau angka');
        }

        return count(array_filter($rangeData, function ($value) use ($condition) {
            return $value == $condition;
        }));
    }

    /**
     * Proses hasil operasi fungsi COUNT_IFS
     *
     * @param array $functions
     * @param array $sourceData
     * @param string $operation
     *
     * @return float|int|Error
     */
    protected static function countIfsResult($functions, $sourceData, $operation)
    {
        $keyNumber = $functions['arguments'][0] ?? null;
        $keyNumber = self::stringValue($keyNumber);

        if ($keyNumber === false) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen pertama harus berupa string yang valid');
        }

        $category = $functions['arguments'][1] ?? null;
        $rangeArgs = $functions['arguments'][2] ?? null;

        $rangeData = self::getRange($keyNumber, $category, $rangeArgs, $sourceData, $operation);

        $groupedData = self::groupRangeData($rangeData);
        $groupedData = array_values($groupedData);

        // Cek jika rentang data lebih dari 1, Maka tampilkan error
        if (isset($groupedData[0]) && count($groupedData[0]) > 1) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Tidak boleh menggunakan rentang data lebih dari 1');
        }

        $rangeData = array_values(array_filter(
            $rangeData,
            function ($value) {
                return $value !== null;
            }
        ));

        $arguments = $functions['arguments'];

        $conditions = [];
        foreach ($arguments as $key => $argument) {
            if ($key >= 3) {
                $condition = self::stringOrNumericValue($argument);

                if ($condition === false) {
                    return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                        'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen ke ' . $key . ' harus berupa string atau angka');
                }

                $conditions[] = $condition;
            }
        }

        $countResult = 0;
        $checkedConditions = [];
        foreach ($conditions as $condition) {
            // Cek apakah kondisi sudah dicek
            if (in_array($condition, $checkedConditions)) {
                continue;
            }

            $countResult += count(array_filter($rangeData, function ($value) use ($condition) {
                return $value == $condition;
            }));

            // Jika sudah dicek maka masukkan ke checked conditions
            $checkedConditions[] = $condition;
        }

        return $countResult;
    }

    /**
     * Proses hasil operasi fungsi COUNT_WHERE yang dimana fungsi ini menerapkan operator AND
     *
     * @param array $functions
     * @param array $sourceData
     * @param string $operation
     *
     * @return float|int|Error
     */
    protected static function countWhereResult($functions, $sourceData, $operation)
    {
        $keyNumber = self::stringValue($functions['arguments'][0] ?? null);

        if ($keyNumber === false) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen pertama harus berupa string yang valid');
        }

        $category = $functions['arguments'][1] ?? null;
        $rangeArgs = $functions['arguments'][2] ?? null;

        $rangeData = self::getRange($keyNumber, $category, $rangeArgs, $sourceData, $operation);
        $rangeData = array_filter($rangeData, fn($value) => $value !== null);

        $conditions = $filteredData = [];

        foreach (array_slice($functions['arguments'], 3) as $y => $argument) {
            $orOperators = explode('OR', $argument);

            foreach ($orOperators as $x => $orOperator) {
                $conditions[$y][$x] = $orOperator;
            }
        }

        $filteredData = self::groupRangeData($rangeData);

        $error = null;
        $filtered = array_filter($filteredData, function ($value) use ($conditions, $functions, &$error) {
            $resultConditions = [];

            foreach ($conditions as $k => $condition) {
                $k += 4;
                $orConditionResult = [];

                foreach ($condition as $child) {
                    $evaluated = self::evaluateCountCondition($child, $value, $functions, $k);

                    if (Error::isError($evaluated)) {
                        $error = $evaluated;
                        continue;
                    }

                    if (Error::isError($error)) {
                        continue;
                    }

                    $orConditionResult[] = $evaluated;
                }

                $resultConditions[] = in_array(true, $orConditionResult, true);
            }

            return !in_array(false, $resultConditions, true);
        });

        if (Error::isError($error)) {
            return $error;
        }

        return count($filtered);
    }

    /**
     * Proses hasil operasi fungsi AVG_X_WHERE yang dimana fungsi ini menerapkan operator AND
     *
     * @param array $functions
     * @param array $sourceData
     * @param string $operation
     *
     * @return float|int|Error
     */
    protected static function avgXWhereResult($functions, $sourceData, $operation)
    {
        $keyNumber = self::stringValue($functions['arguments'][0] ?? null);

        if ($keyNumber === false) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen pertama harus berupa string yang valid');
        }

        $category = $functions['arguments'][1] ?? null;
        $rangeArgs = $functions['arguments'][2] ?? null;
        $flagArg = $functions['arguments'][3] ?? null;

        $ishWithNull = $flagArg == 'WITH_NULL' ? true : false;

        $rangeData = self::getRange($keyNumber, $category, $rangeArgs, $sourceData, $operation);
        $rangeData = array_filter($rangeData, fn($value) => $value !== null);

        $conditions = $filteredData = [];

        foreach (array_slice($functions['arguments'], 4) as $y => $argument) {
            $orOperators = explode('OR', $argument);

            foreach ($orOperators as $x => $orOperator) {
                $conditions[$y][$x] = $orOperator;
            }
        }

        $filteredData = self::groupRangeData($rangeData);

        $error = null;
        $excludeColumn = [];
        $filtered = array_filter($filteredData, function ($value) use ($conditions, $functions, &$error, &$excludeColumn) {
            $resultConditions = [];

            foreach ($conditions as $k => $condition) {
                $k += 4;
                $orConditionResult = [];

                foreach ($condition as $child) {
                    $callbackColumn = null;
                    $evaluated = self::evaluateCountCondition($child, $value, $functions, $k, $callbackColumn);

                    if (!empty($callbackColumn) && !in_array($callbackColumn, $excludeColumn)) {
                        $excludeColumn[] = $callbackColumn;
                    }

                    if (Error::isError($evaluated)) {
                        $error = $evaluated;
                        continue;
                    }

                    if (Error::isError($error)) {
                        continue;
                    }

                    $orConditionResult[] = $evaluated;
                }

                $resultConditions[] = in_array(true, $orConditionResult, true);
            }

            return !in_array(false, $resultConditions, true);
        });

        if (Error::isError($error)) {
            return $error;
        }

        $filterRangeData = array_values(array_filter($filtered));
        $filterRangeData = array_map(fn($range) => array_diff_key($range, array_flip($excludeColumn)), $filterRangeData);

        $result = [];
        foreach ($filterRangeData as $key => $value) {
            if ($ishWithNull) {
                $result[$key] = array_sum($value);
            } else {
                $value = array_filter($value, function ($value) {
                    return $value !== null;
                });

                if (!empty($value)) {
                    $result[$key] = array_sum($value);
                }
            }
        }

        $totalRow = count($result);
        $totalRow = $totalRow == 0 ? 1 : $totalRow;

        return array_sum($result) / $totalRow;
    }

    /**
     * Proses hasil operasi fungsi AVG_Y_WHERE
     *
     * @param array $functions
     * @param array $sourceData
     * @param string $operation
     *
     * @return float|int|Error
     */
    protected static function avgYWhereResult($functions, $sourceData, $operation)
    {
        $keyNumber = self::stringValue($functions['arguments'][0] ?? null);

        if ($keyNumber === false) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen pertama harus berupa string yang valid');
        }

        $category = $functions['arguments'][1] ?? null;
        $rangeArgs = $functions['arguments'][2] ?? null;
        $flagArg = $functions['arguments'][3] ?? null;

        $ishWithNull = $flagArg == 'WITH_NULL' ? true : false;

        $rangeData = self::getRange($keyNumber, $category, $rangeArgs, $sourceData, $operation);
        $rangeData = array_filter($rangeData, fn($value) => $value !== null);

        $conditions = $filteredData = [];

        foreach (array_slice($functions['arguments'], 4) as $y => $argument) {
            $orOperators = explode('OR', $argument);

            foreach ($orOperators as $x => $orOperator) {
                $conditions[$y][$x] = $orOperator;
            }
        }
        
        $filteredData = self::groupRangeData($rangeData);

        $error = null;
        $excludeColumn = [];
        $filtered = array_filter($filteredData, function ($value) use ($conditions, $functions, &$error, &$excludeColumn) {
            $resultConditions = [];

            foreach ($conditions as $k => $condition) {
                $k += 4;
                $orConditionResult = [];

                foreach ($condition as $child) {
                    $callbackColumn = null;
                    $evaluated = self::evaluateCountCondition($child, $value, $functions, $k, $callbackColumn);

                    if (!empty($callbackColumn) && !in_array($callbackColumn, $excludeColumn)) {
                        $excludeColumn[] = $callbackColumn;
                    }

                    if (Error::isError($evaluated)) {
                        $error = $evaluated;
                        continue;
                    }

                    if (Error::isError($error)) {
                        continue;
                    }

                    $orConditionResult[] = $evaluated;
                }

                $resultConditions[] = in_array(true, $orConditionResult, true);
            }

            return !in_array(false, $resultConditions, true);
        });

        if (Error::isError($error)) {
            return $error;
        }
        
        $filterRangeData = array_values(array_filter($filtered));
        $filterRangeData = array_map(fn($range) => array_diff_key($range, array_flip($excludeColumn)), $filterRangeData);

        $result = [];
        foreach ($filterRangeData as $key => $value) {
            if ($ishWithNull) {
                $totalRow = count($value);
                $totalRow = $totalRow == 0 ? 1 : $totalRow;
                $result[$key] = array_sum($value) / $totalRow;
            } else {
                $totalRow = count(array_filter($value, function ($value) {
                    return $value !== null;
                }));
                $totalRow = $totalRow == 0 ? 1 : $totalRow;
                $result[$key] = array_sum($value) / $totalRow;
            }
        }

        $totalRow = count($result);
        $totalRow = $totalRow == 0 ? 1 : $totalRow;

        return array_sum($result) / $totalRow;
    }

    /**
     * Proses hasil operasi fungsi AVG_Y
     *
     * @param array $functions
     * @param array $sourceData
     * @param string $operation
     *
     * @return float|int|Error
     */
    protected static function avgYResult($functions, $sourceData, $operation)
    {
        $keyNumber = $functions['arguments'][0] ?? null;
        $keyNumber = self::stringValue($keyNumber);

        if ($keyNumber === false) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen pertama harus berupa string yang valid');
        }

        $category = $functions['arguments'][1] ?? null;
        $rangeArgs = $functions['arguments'][2] ?? null;
        $flagArg = $functions['arguments'][3] ?? null;

        $rangeData = self::getRange($keyNumber, $category, $rangeArgs, $sourceData, $operation);
        $rangeData = array_values(array_filter($rangeData));

        $result = [];

        $ishWithNull = $flagArg == 'WITH_NULL' ? true : false;

        foreach ($rangeData as $key => $value) {
            if ($ishWithNull) {
                $totalRow = count($value);
                $totalRow = $totalRow == 0 ? 1 : $totalRow;
                $result[$key] = array_sum($value) / $totalRow;
            } else {
                $totalRow = count(array_filter($value, function ($value) {
                    return $value !== null;
                }));
                $totalRow = $totalRow == 0 ? 1 : $totalRow;
                $result[$key] = array_sum($value) / $totalRow;
            }
        }

        $totalRow = count($result);
        $totalRow = $totalRow == 0 ? 1 : $totalRow;

        return array_sum($result) / $totalRow;
    }

    /**
     * Proses hasil operasi fungsi AVG_X
     *
     * @param array $functions
     * @param array $sourceData
     * @param string $operation
     *
     * @return float|int|Error
     */
    protected static function avgXResult($functions, $sourceData, $operation)
    {
        $keyNumber = $functions['arguments'][0] ?? null;
        $keyNumber = self::stringValue($keyNumber);

        if ($keyNumber === false) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen pertama harus berupa string yang valid');
        }

        $category = $functions['arguments'][1] ?? null;
        $rangeArgs = $functions['arguments'][2] ?? null;
        $flagArg = $functions['arguments'][3] ?? null;

        $rangeData = self::getRange($keyNumber, $category, $rangeArgs, $sourceData, $operation);
        $rangeData = array_values(array_filter($rangeData));

        $result = [];

        $ishWithNull = $flagArg == 'WITH_NULL' ? true : false;

        foreach ($rangeData as $key => $value) {
            if ($ishWithNull) {
                $result[$key] = array_sum($value);
            } else {
                $value = array_filter($value, function ($value) {
                    return $value !== null;
                });

                if (!empty($value)) {
                    $result[$key] = array_sum($value);
                }
            }
        }

        $totalRow = count($result);
        $totalRow = $totalRow == 0 ? 1 : $totalRow;

        return array_sum($result) / $totalRow;
    }

    /**
     * Proses hasil operasi fungsi CASES
     *
     * @param array $functions
     *
     * @return float|int|Error
     */
    protected static function casesResult($functions)
    {
        $arguments = $functions['arguments'];

        $conditions = [];
        foreach ($arguments as $key => $value) {
            $isCondition = $key % 2 == 0;
            $isValue = $key % 2 != 0;

            if ($isCondition) {
                $expression = self::caseCondition($value, $functions, $key);

                if (Error::isError($expression)) {
                    return $expression;
                }

                $case = self::evaluateCaseCondition($expression);

                if (Error::isError($case)) {
                    return $case;
                }

                $conditions[$key]['case'] = $case;
            }

            if ($isValue) {
                $result = self::calculateFormula($value);

                if (Error::isError($result)) {
                    return new Error(
                        'Terjadi kesalahan saat mengurai formula, ' . PHP_EOL .
                            'Error fungsi ' . $functions['original_formula'] . ': ' . 'Perhitungan tidak valid pada argument ke ' . ($key + 1)
                    );
                }

                $conditions[$key - 1]['value'] = $result;
            }
        }

        $result = array_filter($conditions, function ($value) {
            return $value['case'] == true;
        });

        $result = array_values($result);
        $result = $result[0]['value'] ?? 0;

        if (!is_numeric($result)) {
            return 0;
        }

        return $result;
    }

    /**
     * Fungsi untuk menggunakan data USE
     *
     * @param array $functions
     *
     * @return string|Error
     */
    protected static function useResult($functions)
    {
        $sourceName = $functions['arguments'][0] ?? null;
        $selectedField = $functions['arguments'][1] ?? null;

        $sourceName = self::stringValue($sourceName);
        $selectedField = self::stringValue($selectedField);

        if ($sourceName === false || $selectedField === false) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen pertama dan kedua harus berupa string yang valid');
        }

        $selectedData = self::$additionalData[$sourceName] ?? [];
        $selectedField = $selectedData[$selectedField] ?? '';

        return "'$selectedField'";
    }

    /**
     * Fungsi untuk generate tahun YEAR()
     *
     * @param array $functions
     *
     * @return string|Error
     */
    protected static function yearResult($functions)
    {
        $yearOption = $functions['arguments'][0] ?? null;

        $periodeAudit = AuditPeriode::find(self::$additionalData['id_periode_audit']);

        if (empty($periodeAudit)) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Periode audit tidak ditemukan');
        }

        $yearAudit = $periodeAudit->tahun_audit;

        if ($yearOption === null) {
            return "'" . $yearAudit . "'";
        }

        if (!is_numeric($yearOption)) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen pertama harus berupa angka');
        }

        // Jika opsi tahun argumennya angka minus maka ambil tahun sekarang dikurangi dengan angka minus tersebut
        if ($yearOption < 0) {
            return $yearAudit - abs($yearOption);
        }

        // Jika opsi tahun argumennya angka positif maka ambil tahun sekarang ditambah dengan angka positif tersebut
        if ($yearOption > 0) {
            return $yearAudit + abs($yearOption);
        }
    }

    /**
     * Fungsi untuk concat string CONCAT()
     *
     * @param array $functions
     *
     * @return string|Error
     */
    protected static function concatResult($functions)
    {
        $arguments = $functions['arguments'];

        $result = '';
        foreach ($arguments as $key => $defaultValue) {
            $value = self::stringOrNumericValue($defaultValue);

            if ($value === false) {
                $value = self::calculateFormula($defaultValue);

                if (Error::isError($value)) {
                    return new Error(
                        'Terjadi kesalahan saat mengurai formula, ' . PHP_EOL .
                            'Error fungsi ' . $functions['original_formula'] . ': ' . 'Parameter tidak valid pada argument ke ' . ($key + 1)
                    );
                }

                $value = number_format($value, 2, '.', '');
                $value = (float) $value == (int) $value ? (int) $value : $value;
            }

            $result .= $value;
        }

        return "'$result'";
    }

    /**
     * Fungsi untuk menghitung selisih tanggal DATE_DIFF()
     *
     * @param array $functions
     *
     * @return int|Error
     */
    protected static function dateDiffResult($functions)
    {
        $date1 = $functions['arguments'][0] ?? null;
        $date2 = $functions['arguments'][1] ?? null;
        $unit = $functions['arguments'][2] ?? null;

        $date1 = self::stringOrNumericValue($date1);
        $date2 = self::stringOrNumericValue($date2);
        $unit = self::stringValue($unit);

        if ($date1 === false || $date2 === false) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen pertama dan kedua harus berupa string tanggal yang valid');
        }

        if ($unit === false) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen ketiga harus berupa string satuan yang valid (Y, M, D, H, I, S)');
        }

        // Validasi satuan
        $validUnits = ['Y', 'M', 'D', 'H', 'I', 'S'];
        if (!in_array(strtoupper($unit), $validUnits)) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Satuan harus salah satu dari: Y (years), M (months), D (days), H (hours), I (minutes), S (seconds)');
        }

        // Convert year-only format to full date
        if (is_numeric($date1) && strlen($date1) == 4) {
            $date1 = $date1 . '-01-01';
        }
        if (is_numeric($date2) && strlen($date2) == 4) {
            $date2 = $date2 . '-01-01';
        }

        try {
            $datetime1 = new \DateTime($date1);
            $datetime2 = new \DateTime($date2);
        } catch (\Exception $e) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Format tanggal tidak valid');
        }

        // If the difference is negative, return 0
        if ($datetime1 > $datetime2) {
            return 0;
        }

        // Calculate the difference
        $interval = $datetime1->diff($datetime2);

        // Return the appropriate unit
        $unit = strtoupper($unit);
        switch ($unit) {
            case 'Y':
                return (int) $interval->y;
            case 'M':
                return (int) $interval->m;
            case 'D':
                return (int) $interval->d;
            case 'H':
                return (int) $interval->h;
            case 'I':
                return (int) $interval->i;
            case 'S':
                return (int) $interval->s;
            default:
                return 0;
        }
    }

    /**
     * Fungsi untuk mendapatkan tanggal saat ini CURRENT_DATE()
     *
     * @param array $functions
     *
     * @return string|Error
     */
    protected static function currentDateResult($functions)
    {
        $format = $functions['arguments'][0] ?? null;
        $format = self::stringValue($format);

        if ($format === false) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen pertama harus berupa string format tanggal yang valid');
        }

        try {
            $result = date($format);
            return "'$result'";
        } catch (\Exception $e) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Format tanggal tidak valid');
        }
    }

    /**
     * Ambil rentang data
     *
     * @param string $keyNumber
     * @param string $category
     * @param string $rangeArgs
     * @param array $sourceData
     * @param string $operation
     *
     * @return array
     */
    protected static function getRange($keyNumber, $category, $rangeArgs, $sourceData, $operation)
    {
        $rangeArgs = self::parseRange($rangeArgs);

        $sources = $sourceData[$keyNumber] ?? "[]";
        $sources = (array) json_decode($sources);
        $sources = array_values($sources);

        $proceededSources = [];

        // Jika kategori tidak ada, maka ambil semua kategori dan flatten
        if ($category == 'null') {
            $flattenSource = Arr::flatten($sources);
            $proceededSources = self::getDimensionRanges($flattenSource, $rangeArgs, $operation);
        }

        // Jika kategori ada, maka ambil kategori yang sesuai
        if (strpos($category, 'C') !== false) {
            $patternNumberOnly = '/[^0-9]/';
            $categoryKey = preg_replace($patternNumberOnly, '', $category);
            $sourceByCategory = $sources[$categoryKey - 1] ?? [];
            $flattenSource = Arr::flatten($sourceByCategory);
            $proceededSources = self::getDimensionRanges($flattenSource, $rangeArgs, $operation);
        }

        return $proceededSources;
    }

    /**
     * Proses kondisi pada fungsi CASES
     *
     * @param string $value
     * @param array $functions
     * @param int $key
     *
     * @return bool|Error
     */
    protected static function caseCondition($value, $functions, $key, $withImplode = true)
    {
        // Replace parenthesis pada value
        $value = str_replace(['(', ')'], ['^lp^', '^rp^'], $value);

        $operatorConditions = ['^lte^', '^gte^', '^eq^', '^lt^', '^gt^', '^and^', '^or^', '^not^', '^lp^', '^rp^'];
        $pattern = '/(' . implode('|', array_map('preg_quote', $operatorConditions)) . ')/';

        $parts = preg_split($pattern, $value, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $errorValueNotStringOrNumeric = [];
        $parts = array_map(function ($item) use (&$errorValueNotStringOrNumeric) {
            switch ($item) {
                case '^lte^':
                    return '<=';
                case '^gte^':
                    return '>=';
                case '^eq^':
                    return '==';
                case '^lt^':
                    return '<';
                case '^gt^':
                    return '>';
                case '^and^':
                    return '&&';
                case '^or^':
                    return '||';
                case '^not^':
                    return '!=';

                    // Tambahan untuk kondisi yang memiliki tanda kurung
                case '^lp^':
                    return '(';
                case '^rp^':
                    return ')';
                default:
                    if ($item === 'true' | $item === 'false' | $item === 'null') {
                        $value = "$item";
                    } else {
                        $value = self::stringOrNumericValue($item);
                    }

                    if ($value === false) {
                        $errorValueNotStringOrNumeric[] = $item;
                    }

                    return $item;
            }
        }, $parts);

        if (!empty($errorValueNotStringOrNumeric)) {
            return new Error(
                'Terjadi kesalahan saat mengurai formula, ' . PHP_EOL .
                    'Error fungsi ' . $functions['original_formula'] . ': ' . 'Operand argumen ke ' . ($key + 1) . ' harus berupa string atau angka'
            );
        }

        $expression = implode('', $parts);

        if (!$withImplode) {
            return $parts;
        }

        return $expression;
    }

    protected static function evaluateCaseCondition($expression)
    {
        try {
            $result = self::$expressionLanguage->evaluate($expression);

            return $result;
        } catch (\Exception $e) {
            return new Error('Terjadi kesalahan saat mengurai formula');
        }
    }

    /**
     * Proses untuk evaluate kondisi COUNT_WHERE
     *
     * @param string $condition
     * @param array $value
     * @param array $functions
     * @param int $key
     * @param mixed $callbackColumn
     *
     * @return bool|Error
     */
    protected static function evaluateCountCondition($condition, $value, $functions, $key, &$callbackColumn = null)
    {
        $condition = str_replace(['{', '}'], "'", $condition);
        $expressions = self::caseCondition($condition, $functions, $key, withImplode: false);

        if (Error::isError($expressions)) {
            return $expressions;
        }

        $column = $expressions[0] ?? null;
        $column = self::stringOrNumericValue($column);

        $callbackColumn = $column;

        $operator = $expressions[1] ?? null;

        $operand = $expressions[2] ?? null;
        $operand = self::stringOrNumericValue($operand);

        $value[$column] ??= 0;

        if ($operand === false && $operator === null) {
            return in_array($column, $value);
        }

        if ($operand === false || $operator === null || !isset($value[$column])) {
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL .
                'Error fungsi ' . $functions['original_formula'] . ': ' . 'Argumen ke ' . $key . ' tidak valid');
        }

        if ($operator == '==') {
            return $value[$column] == $operand;
        }

        if ($operator == '>=') {
            return $value[$column] >= $operand;
        }

        if ($operator == '<=') {
            return $value[$column] <= $operand;
        }

        if ($operator == '>') {
            return $value[$column] > $operand;
        }

        if ($operator == '<') {
            return $value[$column] < $operand;
        }

        if ($operator == '!=') {
            return $value[$column] != $operand;
        }

        return false;
    }

    /**
     * Parsing rentang data
     *
     * @param string $range
     *
     * @return array
     */
    protected static function parseRange($range)
    {
        $pattern = '/\{([^}]+)\}/';
        $patternNumberOnly = '/[^0-9]/';

        $range = explode(':', $range);

        $parsedRanges = [];
        foreach ($range as $key => $value) {
            preg_match_all($pattern, $value, $matches);

            if (isset($matches[1][0]) && $key == 0) {
                $parsedRanges['start_x_axis'] = preg_replace($patternNumberOnly, '', $matches[1][0]);
            }

            if (isset($matches[1][1]) && $key == 0) {
                $parsedRanges['start_y_axis'] = preg_replace($patternNumberOnly, '', $matches[1][1]);
            }

            if (isset($matches[1][0]) && $key == 1) {
                $parsedRanges['end_x_axis'] = preg_replace($patternNumberOnly, '', $matches[1][0]);
            }

            if (isset($matches[1][1]) && $key == 1) {
                $parsedRanges['end_y_axis'] = preg_replace($patternNumberOnly, '', $matches[1][1]);
            }
        }

        return $parsedRanges;
    }

    /**
     * Group data berdasarkan Y
     *
     * @param array $rangeData
     *
     * @return array
     */
    protected static function groupRangeData($rangeData)
    {
        $data = [];

        foreach ($rangeData as $key => $value) {
            preg_match('/{Y(\d+)}{X(\d+)}/', $key, $matches);

            if (!isset($matches[1]) && !isset($matches[2])) {
                continue;
            }

            $yValue = 'Y' . $matches[1];
            $xValue = 'X' . $matches[2];

            $data[$yValue][$xValue] = $value;
        }

        return $data;
    }

    /**
     * Ambil dimensi rentang data
     *
     * @param array $data
     * @param array $ranges
     * @param string $operation
     *
     * @return array
     */
    protected static function getDimensionRanges($data, $ranges, $operation)
    {
        $startX = $ranges['start_x_axis'] ?? null;
        $startY = $ranges['start_y_axis'] ?? null;
        $endX = $ranges['end_x_axis'] ?? null;
        $endY = $ranges['end_y_axis'] ?? null;

        // Jika end y tidak ada, maka ambil semua rentang data vertikal
        if (!$endY) {
            $endY = count($data);
        }

        $result = [];

        // Ambil rentang data
        for ($x = $startX; $x <= $endX; $x++) {
            for ($y = $startY; $y <= $endY; $y++) {
                $xData = $data[$y - 1] ?? [];
                $value = ((array) $xData)[$x] ?? null;

                // Jika operation avg_y, maka kelompokkan berdasarkan x
                if ($operation == 'avg_y' || $operation == 'avg_x') {
                    $result["{Y$y}"][] =  is_numeric($value) ? floatval($value) : $value;
                    continue;
                }

                // Jika operation count empty maka semua value diambil tanpa filter
                if ($operation == 'count_empty') {
                    $result["{Y$y}{X$x}"] = is_numeric($value) ? floatval($value) : $value;
                    continue;
                }

                if (isset($value)) {
                    $result["{Y$y}{X$x}"] = is_numeric($value) ? floatval($value) : $value;
                }
            }
        }

        // Sort array berdasarkan key
        ksort($result);

        return $result;
    }

    /**
     * Mapping source data
     *
     * @param array $sourceData
     *
     * @return array
     */
    protected static function mapSourceData($sourceData)
    {
        $mappedSourceData = [];

        foreach ($sourceData as $value) {
            $mappedSourceData[$value->nomor_indikator] = $value->rows;
        }

        return $mappedSourceData;
    }

    /**
     * Mendapatkan argumen fungsi
     *
     * @param string $formula
     * @param string $originalFormula
     * @param array $only
     * @param array $except
     *
     * @return array|Error
     */
    protected static function getFunctionArguments($formula, $originalFormula, $only = [], $except = [])
    {
        $pattern = self::getPatterns();

        $functions = [];
        $errParse = [];

        $i = 0;
        foreach ($pattern as $key => $value) {
            if (!empty($only) && !in_array($key, $only)) {
                continue;
            }

            if (!empty($except) && in_array($key, $except)) {
                continue;
            }

            preg_match_all($value['pattern'], $formula, $matches);
            foreach ($matches[1] as $match) {
                if (isset($match)) {
                    $matchArgs = explode(',', $match);
                    $matchArgs = array_filter($matchArgs, function ($value) {
                        return !empty($value) || $value == '0';
                    });
                    $matchArgs = array_values($matchArgs);
                    $matchedFormula = "$key(" . $match . ")";

                    // Jika jumlah argumen tidak sesuai, maka error parsing
                    $isHasNullArgs = count($matchArgs) != $value['argsLen'];
                    if (!empty($value['optArgsLen']) && $value['optArgsLen'] != 'inf') {
                        $optArgLen = $value['optArgsLen'] ?? 0;
                        $totalArgLen = $value['argsLen'] + $optArgLen;
                        $isFillOptionalArg = count($matchArgs) == $totalArgLen;
                    }

                    if (!empty($value['optArgsLen']) && $value['optArgsLen'] == 'inf') {
                        $optArgLen = $value['optArgsLen'] ?? 0;
                        $totalArgLen = $value['argsLen'];
                        $isFillOptionalArg = count($matchArgs) >= $totalArgLen;
                    }

                    if (!empty($isFillOptionalArg) && $isFillOptionalArg) {
                        $isHasNullArgs = false;
                    }

                    if ($value['argsLen'] == 'inf') {
                        $isHasNullArgs = false;
                    }

                    if ($isHasNullArgs) {
                        $errParse[] = [
                            'function' => $key,
                            'msg' => 'Jumlah argumen tidak sesuai',
                            'argsLen' => $value['argsLen'],
                            'original_formula' => $originalFormula
                        ];
                        break;
                    }

                    $functions[$i] = [
                        'function' => $key,
                        'type' => $value['type'],
                        'formula' => $matchedFormula,
                        'original_formula' => $originalFormula,
                        'arguments' => $matchArgs
                    ];
                }
                $i++;
            }
        }

        if (!empty($errParse)) {
            // Array to text
            $errParse = array_map(function ($value) {
                return 'Error fungsi ' . $value['original_formula'] . ': ' . 'Jumlah argumen yang diharapkan ' . $value['argsLen'] . PHP_EOL;
            }, $errParse);

            $errParse = implode(', ', $errParse);
            return new Error('Terjadi kesalahan saat mengurai formula,' . PHP_EOL . $errParse);
        }

        return $functions;
    }

    /**
     * Membersihkan spasi
     *
     * @param string $string
     *
     * @return string
     */
    protected static function cleanWhitespaces($string)
    {
        preg_match_all("/'(.*?)'/", $string, $matches);
        $cleanedString = preg_replace('/\s+/', '', $string);

        // Case ketika tipe string atau '' ada spasi di dalamnya, maka tidak di cleaning
        if (!empty($matches[1])) {
            foreach ($matches[1] as $match) {
                $cleanedMatch = preg_replace('/\s+/', '', $match);
                $cleanedString = str_replace($cleanedMatch, $match, $cleanedString);
            }
        }

        return $cleanedString;
    }

    /**
     * Mendapatkan nilai string
     *
     * @param string $string
     *
     * @return string|bool
     */
    protected static function stringValue($string)
    {
        if (is_numeric($string) || empty($string)) {
            return false;
        }

        $pattern = "/'(.*?)'/";
        preg_match_all($pattern, $string, $matches);

        if (isset($matches[1]) && count($matches[1]) > 1) {
            return false;
        }

        if (isset($matches[1][0])) {
            return $matches[1][0];
        }

        return false;
    }

    /**
     * Mendapatkan nilai string atau numerik
     *
     * @param string $string
     *
     * @return string|bool
     */
    protected static function stringOrNumericValue($string)
    {
        if (!is_numeric($string) && empty($string)) {
            return false;
        }

        if (is_numeric($string)) {
            return $string;
        }

        if ($string === 'null') {
            return null;
        }

        $pattern = "/'(.*?)'/";
        preg_match_all($pattern, $string, $matches);

        if (isset($matches[1]) && count($matches[1]) > 1) {
            return false;
        }

        if (isset($matches[1][0])) {
            return $matches[1][0];
        }

        return false;
    }


    /**
     * Replace operator matematika yang berurutan
     *
     * @param string $input
     *
     * @return string
     */
    protected static function replaceConsecutiveOperators($input)
    {
        return preg_replace('/([+\-*\/]+)(?=[^\d(]|$)/', '', $input);
    }

    /**
     * Cek apakah string valid
     *
     * @param string $input
     *
     * @return bool
     */
    protected static function isValidString($input)
    {
        return preg_match('/^\'\'$|^\'[^\']*\'$/', $input) === 1;
    }

    /**
     * Mendapatkan pattern fungsi
     *
     * @return array
     */
    protected static function getPatterns()
    {
        return [
            'SUM' => [
                'pattern' => self::generateFunctionPattern('SUM'),
                'type' => 'sum',
                'argsLen' => 3
            ],
            'COUNT' => [
                'pattern' => self::generateFunctionPattern('COUNT'),
                'type' => 'count',
                'argsLen' => 3
            ],
            'COUNT_EMPTY' => [
                'pattern' => self::generateFunctionPattern('COUNT_EMPTY'),
                'type' => 'count_empty',
                'argsLen' => 3
            ],
            'COUNT_CATEGORY' => [
                'pattern' => self::generateFunctionPattern('COUNT_CATEGORY'),
                'type' => 'count_category',
                'argsLen' => 1
            ],
            'COUNT_IF' => [
                'pattern' => self::generateFunctionPattern('COUNT_IF'),
                'type' => 'count_if',
                'argsLen' => 4
            ],
            'COUNT_IFS' => [
                'pattern' => self::generateFunctionPattern('COUNT_IFS'),
                'type' => 'count_ifs',
                'argsLen' => 4,
                'optArgsLen' => 'inf'
            ],
            'COUNT_WHERE' => [
                'pattern' => self::generateFunctionPattern('COUNT_WHERE'),
                'type' => 'count_where',
                'argsLen' => 4,
                'optArgsLen' => 'inf'
            ],
            'AVG_X_WHERE' => [
                'pattern' => self::generateFunctionPattern('AVG_X_WHERE'),
                'type' => 'avg_x_where',
                'argsLen' => 5,
                'optArgsLen' => 'inf'
            ],
            'AVG_Y_WHERE' => [
                'pattern' => self::generateFunctionPattern('AVG_Y_WHERE'),
                'type' => 'avg_y_where',
                'argsLen' => 5,
                'optArgsLen' => 'inf'
            ],
            'AVG_Y' => [
                'pattern' => self::generateFunctionPattern('AVG_Y'),
                'type' => 'avg_y',
                'argsLen' => 3,
                'optArgsLen' => 1
            ],
            'AVG_X' => [
                'pattern' => self::generateFunctionPattern('AVG_X'),
                'type' => 'avg_x',
                'argsLen' => 3,
                'optArgsLen' => 1
            ],
            'CASES' => [
                'pattern' => self::generateFunctionPattern('CASES'),
                'type' => 'cases',
                'argsLen' => 'inf'
            ],
            'USE' => [
                'pattern' => self::generateFunctionPattern('USE'),
                'type' => 'use',
                'argsLen' => 2
            ],
            'YEAR' => [
                'pattern' => self::generateFunctionPattern('YEAR'),
                'type' => 'year',
                'argsLen' => 0,
                'optArgsLen' => 1
            ],
            'CONCAT' => [
                'pattern' => self::generateFunctionPattern('CONCAT'),
                'type' => 'concat',
                'argsLen' => 1,
                'optArgsLen' => 'inf'
            ],
            'DATE_DIFF' => [
                'pattern' => self::generateFunctionPattern('DATE_DIFF'),
                'type' => 'date_diff',
                'argsLen' => 3
            ],
            'CURRENT_DATE' => [
                'pattern' => self::generateFunctionPattern('CURRENT_DATE'),
                'type' => 'current_date',
                'argsLen' => 1
            ],
        ];
    }

    /**
     * Mendapatkan pattern fungsi 3 level
     *
     * @param string $functionName
     *
     * @return array
     */
    protected static function generateFunctionPattern($functionName)
    {
        return '/' . preg_quote($functionName, '/') . '\(((?:[^()]|\((?:(?:[^()]|\((?:[^()]|\([^()]*\))*\))*)*\))*)\)/';
    }

    /**
     * Mendapatkan pattern fungsi 2 level
     *
     * @param string $functionName
     *
     * @return array
     */
    protected static function generateTwoLevelFunction($functionName)
    {
        return '/' . preg_quote($functionName, '/') . '\(([^()]*(?:(?:\([^()]*\))[^()]*)*)\)/';
    }

    protected static function convertScientificToDecimal($scientificNumber)
    {
        $parts = explode('E', $scientificNumber);

        if (count($parts) === 2) {
            $exp = abs(end($parts)) + strlen($parts[0]);
            $decimal = number_format($scientificNumber, $exp);
            $parse = rtrim($decimal, '.0');
            return $parse;
        } else {
            return $scientificNumber;
        }
    }
}
