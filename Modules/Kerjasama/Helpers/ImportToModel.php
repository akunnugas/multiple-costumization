<?php

namespace Modules\Kerjasama\Helpers;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Modules\Core\Helpers\Cstr;
use Modules\Core\Helpers\Error;

class ImportToModel implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    use Importable;

    protected $successCount = 0;
    protected $failureCount = 0;
    protected $failedVariables = [];

    protected int $duplicateCount = 0;
    protected $errors = [];
    protected $modelClass;
    protected $fieldMapping;
    protected $transformers;
    protected $validators;
    protected $beforeCreate;
    protected $afterCreate;
    protected $afterAll;

    public function __construct(
        string $modelClass,
        array $fieldMapping = [],
        array $transformers = [],
        array $validators = [],
        ?callable $beforeCreate = null,
        ?callable $afterCreate = null,
        ?callable $afterAll = null
    ) {
        HeadingRowFormatter::default('none');
        $this->modelClass = $modelClass;
        $this->fieldMapping = $fieldMapping;
        $this->transformers = $transformers;
        $this->validators = $validators;
        $this->beforeCreate = $beforeCreate;
        $this->afterCreate = $afterCreate;
        $this->afterAll = $afterAll;
    }

    public function collection(Collection $rows)
    {
        Log::info('Starting the import process.');
        $this->errors = [];
        $successCount = 0;
        $rowNumber = 0;

        $this->validateHeaderMapping($rows);

        $filteredRows = $rows->map(function ($row) {
            $rowArray = $this->normalizeRowKeys($row->toArray());
            $mappedOnly = [];
            foreach ($this->fieldMapping as $excelColumn => $modelField) {
                $normalizedExcelColumn = $this->normalizeHeader($excelColumn);
                if (array_key_exists($normalizedExcelColumn, $rowArray)) {
                    $mappedOnly[$normalizedExcelColumn] = $rowArray[$normalizedExcelColumn];
                }
            }
            return collect($mappedOnly);
        })->filter(function ($row) {
            return $row->filter(function ($value) {
                return !is_null($value);
            })->isNotEmpty();
        });

        if ($filteredRows->isEmpty()) {
            Log::error('No valid data to process.');
            // Jangan throw exception, return summary saja
            return $this->getImportSummary();
        }

        foreach ($filteredRows as $row) {
            $rowNumber++;
            Log::info("Processing row number {$rowNumber}");
            DB::beginTransaction();
            try {
                $this->processRow($row->toArray(), $rowNumber);
                DB::commit();
                $successCount++;
                Log::info("Successfully processed row number {$rowNumber}");
            } catch (\Exception $e) {
                // Store multiple errors per row
                if (!isset($this->errors[$rowNumber])) {
                    $this->errors[$rowNumber] = [
                        'messages' => [],
                        'failed_variables' => $this->failedVariables[$rowNumber] ?? [],
                    ];
                }
                $this->errors[$rowNumber]['messages'][] = $e->getMessage();
                $this->failureCount++;
                DB::rollBack();
                Log::error("Error processing row {$rowNumber}: " . $e->getMessage());
            }
        }

        if (!empty($this->errors)) {
            Log::error('Import finished with errors', [
                'success_count' => $successCount,
                'error_count' => count($this->errors),
                'errors' => $this->errors
            ]);
        }

        // Return summary hasil import
        return $this->getImportSummary();
    }

    private function validateHeaderMapping(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $expectedHeaders = array_values(array_unique(array_map(function ($header) {
            return $this->normalizeHeader($header);
        }, array_keys($this->fieldMapping))));

        $firstRow = $rows->first();
        $actualHeaders = array_values(array_map(function ($header) {
            return $this->normalizeHeader($header);
        }, array_keys($firstRow->toArray())));

        $missingHeaders = array_values(array_diff($expectedHeaders, $actualHeaders));

        if (!empty($missingHeaders)) {
            $message = 'Format kolom file import tidak sesuai template. Kolom hilang: ' . implode(', ', $missingHeaders) . '.';
            throw new \InvalidArgumentException($message);
        }
    }

    private function normalizeHeader($header): string
    {
        $header = is_string($header) ? $header : (string)$header;
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
        return trim($header);
    }

    private function normalizeRowKeys(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalized[$this->normalizeHeader($key)] = $value;
        }
        return $normalized;
    }



    private function processRow(array $row, int $rowNumber)
    {
        Log::info("Mapping and transforming data for row number {$rowNumber}");
        $filteredRow = array_filter($row, function ($value, $key) {
            return in_array($key, array_keys($this->fieldMapping)) || !empty($value);
        }, ARRAY_FILTER_USE_BOTH);

        Log::info("Mapped data for row {$rowNumber}: " . json_encode($filteredRow));
        $mappedData = $this->mapFields($filteredRow);
        Log::info("Transformed data for row {$rowNumber}: " . json_encode($mappedData));
        $transformedData = $this->transformData($mappedData);

        foreach ($transformedData as $field => $value) {
            if (is_string($value)) {
                $transformedData[$field] = htmlentities(trim($value));
            }
        }

        $this->validateData($transformedData, $rowNumber);

        if (!empty($this->failedVariables[$rowNumber])) {
            throw new \Exception('Validasi gagal pada baris ' . $rowNumber);
        }

        if ($this->beforeCreate) {
            Log::info("Running beforeCreate callback for row {$rowNumber}");
            $transformedData = ($this->beforeCreate)($transformedData, $filteredRow);
            if (empty($transformedData)) {
                Log::info("BeforeCreate returned empty data for row {$rowNumber}, skipping row");
                return;
            }
        }

        $model = $this->createOrUpdateModel($transformedData, $rowNumber);

        if ($model && $this->afterCreate) {
            Log::info("Running afterCreate callback for row {$rowNumber}");
            ($this->afterCreate)($model, $transformedData, $row);
        }

        // Only increment successCount if no exception was thrown
        $this->successCount++;
        Log::info("Row {$rowNumber} processed successfully.");
    }


    private function mapFields(array $row): array
    {
        $mappedData = [];
        $row = $this->normalizeRowKeys($row);
        // Reverse mapping: modelField => [excelColumn1, excelColumn2, ...]
        $reverseMapping = [];
        foreach ($this->fieldMapping as $excelColumn => $modelField) {
            $reverseMapping[$modelField][] = $this->normalizeHeader($excelColumn);
        }
        foreach ($reverseMapping as $modelField => $excelColumns) {
            $value = null;
            foreach ($excelColumns as $excelColumn) {
                if (isset($row[$excelColumn]) && $row[$excelColumn] !== null && $row[$excelColumn] !== '') {
                    $value = $row[$excelColumn];
                    break;
                }
            }
            $mappedData[$modelField] = $value;
        }
        return $mappedData;
    }

    private function transformData(array $data): array
    {
        foreach ($this->transformers as $field => $transformer) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $transformer($data[$field]);
                Log::info("Field '{$field}' transformed: {$data[$field]}");
            }
        }
        return $data;
    }

    private function validateData(array $data, int $rowNumber): void
    {
        $failedFields = [];


        foreach ($this->validators as $field => $validator) {
            if (array_key_exists($field, $data)) {
                if (!$validator($data[$field])) {
                    $failedFields[] = $field;
                    Log::error("Validation failed for field '{$field}' in row {$rowNumber}");
                }
            }
        }


        if (!empty($failedFields)) {
            $this->failedVariables[$rowNumber] = $failedFields;
        }
    }




    private function createOrUpdateModel(array $data, int $rowNumber)
    {
        $modelClass = $this->modelClass;
        $existingModel = $data['__existing_model'] ?? null;
        unset($data['__existing_model']);

        Log::info("Checking for existing model in row {$rowNumber}");

        if (is_object($existingModel) && $existingModel instanceof $modelClass) {
            Log::info("Updating existing model for row {$rowNumber}");
            $existingModel->update($data);
            Log::info("Model updated successfully for row {$rowNumber}", ['model_id' => $existingModel->id]);
            return $existingModel;
        }

        Log::info("Creating new model for row {$rowNumber}");
        Log::info("Data to be saved: " . json_encode($data));

        try {
            $model = $modelClass::create($data);
            Log::info("Model created successfully for row {$rowNumber}", ['model_id' => $model->id]);
            return $model;
        } catch (\Exception $e) {
            Log::error("Failed to create model for row {$rowNumber}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    public static function parseDate($dateString, $format = 'Y-m-d')
    {
        Log::info("Parsing date string: {$dateString}");

        if (empty($dateString)) {
            return null;
        }
        // Handle Excel serial numbers
        if (is_numeric($dateString)) {
            try {
                $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateString);
                Log::info("Excel serial {$dateString} converted to: " . $excelDate->format('Y-m-d'));
                return $excelDate->format($format);
            } catch (\Exception $e) {
                Log::error("Error parsing Excel date {$dateString}: " . $e->getMessage());
                return null;
            }
        }
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', trim($dateString))) {
            try {
                return Carbon::createFromFormat('d/m/Y', trim($dateString))->format($format);
            } catch (\Exception $e) {
                Log::error("Error parsing DD/MM/YYYY date {$dateString}: " . $e->getMessage());
                return null;
            }
        }
        try {
            return Carbon::parse($dateString)->format($format);
        } catch (\Exception $e) {
            Log::error("Error parsing date {$dateString}: " . $e->getMessage());
            return null;
        }
    }

    public static function findOrCreateRelated($modelClass, array $searchCriteria, array $createData = [])
    {
        Log::info("Searching or creating related model for criteria", $searchCriteria);
        return $modelClass::firstOrCreate($searchCriteria, array_merge($createData, [
            'waktu_dibuat' => now(),
            'dibuat_oleh' => auth()->id(),
        ]));
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    public function getFailedVariables(): array
    {
        // if not null debug it dd

        return $this->failedVariables;
    }

    public function error()
    {
        return $this->failedVariables;
    }

    public function getImportSummary(): array
    {
        return [
            'success_count' => $this->successCount,
            'failure_count' => $this->failureCount,
            'duplicate_count' => $this->duplicateCount,
            'failed_variables' => $this->failedVariables
        ];
    }




    public function getImportSummaryHtml(array $summary): string
    {
        $html = "<strong>Hasil Import:</strong><br>";
        $html .= "Berhasil: {$summary['success_count']} baris<br>";
        $html .= "Gagal: {$summary['failure_count']} baris<br>";
        $html .= "Duplikat: {$summary['duplicate_count']} baris<br><br>";

        if (!empty($summary['failed_variables'])) {
            $html .= "<strong>Detail Error:</strong><br>";
            foreach ($summary['failed_variables'] as $row => $fields) {
                // If fields is an array with error messages and failed variables
                if (isset($fields['messages']) && isset($fields['failed_variables'])) {
                    $excelColumns = [];
                    foreach ((array)$fields['failed_variables'] as $field) {
                        $excelColumn = array_search($field, $this->fieldMapping, true);
                        $excelColumns[] = $excelColumn !== false ? $excelColumn : $field;
                    }
                    $failedVars = implode(', ', $excelColumns);
                    foreach ($fields['messages'] as $msg) {
                        $html .= "Baris {$row}: {$msg} (Variabel: {$failedVars})<br>";
                    }
                } else {
                    // fallback for old format
                    $excelColumns = [];
                    foreach ((array)$fields as $field) {
                        $excelColumn = array_search($field, $this->fieldMapping, true);
                        $excelColumns[] = $excelColumn !== false ? $excelColumn : $field;
                    }
                    $failedVars = implode(', ', $excelColumns);
                    $html .= "Baris {$row}: Gagal pada variabel: {$failedVars}<br>";
                }
            }
        }

        return $html;
    }
}
