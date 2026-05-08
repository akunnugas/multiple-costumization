<?php

namespace Modules\Core\Helpers;

use Doctrine\DBAL\Query\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\PMB\Jobs\DeleteDataAfterSyncFromV1;
use stdClass;

class SyncSiakadV2
{
    /**
     *  Sync data from siakad v1 to v2
     *
     * @param string $modelV2
     * @param array $mappingColumns
     * @param array $dataSiakadV1
     * @param array $primaryKeyV1
     * @param string $refKeyV2
     * @param string $methodGetDataForDeleteAfterSync
     * @param array $paramsGetDataForDeleteAfterSync
     * @param bool $isDeleteUsingQueue
     * @return array
     */
    public static function sync(
        string $modelV2,
        array $mappingColumns,
        array $dataSiakadV1,
        array $primaryKeyV1,
        string $refKeyV2,
        string $methodGetDataForDeleteAfterSync,
        array $paramsGetDataForDeleteAfterSync = [],
        bool $isDeleteUsingQueue = false
    ) {
        // transform data
        [$resultData, $parentColumns] = self::transformDataSiakadV1ToV2($mappingColumns, $dataSiakadV1, $primaryKeyV1, $refKeyV2);

        // get parent data (if exists)
        if (!empty($parentColumns)) {
            // handle ketika ada 'id_parent', tapi tidak ada di $resultData, maka get ke database v2
            $resultData = self::getParentIdFromCurrentDataV2($modelV2, $refKeyV2, $resultData, $parentColumns);
        }

        // create or update data
        if (!empty($resultData)) {
            $isError = false;
            $dataRef = [];
            $errorMessage = null;
            foreach ($resultData as $record) {
                // looping parent columns utk set $dataRef
                foreach ($parentColumns as $column => $parentColumn) {
                    // jika ada '_is_has_default_', maka skip
                    if (!empty($record['_is_has_default_' . $column])) {
                        continue;
                    }

                    $record[$column] = $dataRef[$record[$column]] ?? null;
                }

                try {
                    $response = $modelV2::updateOrCreate([$refKeyV2 => $record[$refKeyV2]], $record);

                    // set data ref
                    foreach ($parentColumns as $parentColumn) {
                        $dataRef[$response->{$parentColumn['pkv1']}] = $response->{$parentColumn['pkv2']};
                    }
                } catch (\Throwable $th) {
                    if ($th instanceof ValidationException) {
                        continue;
                    };

                    if ($th instanceof QueryException) {
                        $errorMessage = 'Terjadi kesalahan pada pemrosesan data.';
                    }

                    $isError = true;
                    $errorMessage ??= $th->getMessage();
                    break;
                }
            }

            if ($isError) {
                Log::channel('sync')->error('Process Sync Siakad:', [
                    'message' => $errorMessage,
                    'data' => $resultData,
                ]);

                return [true, $errorMessage];
            }
        }

        // jika hapus menggunakan queue
        if ($isDeleteUsingQueue) {
            // DeleteDataAfterSyncFromV1::dispatch($modelV2, $methodGetDataForDeleteAfterSync, $paramsGetDataForDeleteAfterSync, $primaryKeyV1, $refKeyV2);

            return [false, 'Sinkronisasi data berhasil. Jika terdapat penghapusan data pada Siakad V1 akan di proses secara berkala.'];
        }

        // hapus data di aplikasi v2 yang ID-nya tidak ada dalam sinkronisasi (karena dianggap telah dihapus di v1).
        $getData = $methodGetDataForDeleteAfterSync(...$paramsGetDataForDeleteAfterSync);
        $allDataV1Result = $getData['v1']; // result harus array yang isinya object
        $allDataV2Result = $getData['v2']; // result harus array yang isinya object

        // hapus data v2 (yg ada di v2, tapi tidak ada di v1)
        $dataForDeleteOnV2 = [];
        foreach ($allDataV2Result as $dataV2) {
            $isExist = false;
            foreach ($allDataV1Result as $dataV1) {
                $pkOnV2 = explode('|', $dataV2->$refKeyV2);
                $pkOnV1 = [];
                foreach ($primaryKeyV1 as $column) {
                    $pkOnV1[] = $dataV1->$column;
                }

                if ($pkOnV2 == $pkOnV1) {
                    $isExist = true;
                    break;
                }
            }

            if (!$isExist) {
                $dataForDeleteOnV2[] = $dataV2->$refKeyV2;
            }
        }

        // hapus data
        if (!empty($dataForDeleteOnV2)) {
            $notAssigned = $modelV2::whereIn($refKeyV2, $dataForDeleteOnV2)->get();
            foreach ($notAssigned as $item) { // metode delete menjadi looping
                try {
                    $item->forceDelete();
                } catch (\Throwable $th) {
                    Log::channel('sync')->error('Process Delete After Sync Siakad:', [
                        'message' => $th->getMessage(),
                        'data' => $item,
                    ]);
                }
            }
        }

        return [false, 'Sinkronisasi data berhasil.'];
    }

    /**
     * @param string $modelV2
     * @param array $resultData
     * @param array $parentColumns
     * @param string $refKeyV2
     * @return array
     */
    private static function getParentIdFromCurrentDataV2(
        string $modelV2,
        string $refKeyV2,
        array $resultData,
        array $parentColumns
    ) {
        $tampungParentIds = [];
        foreach ($parentColumns as $column => $parentColumn) {
            $tampungParentIds = array_merge($tampungParentIds, array_column($resultData, $column));
        }

        // get data parent v2
        $dataParents = $modelV2::toBase()->select($refKeyV2, 'id')
            ->whereIn($refKeyV2, $tampungParentIds)
            ->get();

        // set data parent ke resultData
        foreach ($resultData as &$data) {
            foreach ($parentColumns as $column => $parentColumn) {
                // jika ada '_is_has_default_', maka skip
                if (!empty($data['_is_has_default_' . $column])) {
                    continue;
                }

                $data[$column] = $dataParents->where($refKeyV2, $data[$column])->first()->id ?? null;

                // set '_is_has_default_'
                if (!empty($data[$column])) {
                    $data['_is_has_default_' . $column] = true;
                }
            }
        }

        return $resultData;
    }

    /**
     * @param array $mappingColumns
     * @param array $dataSiakadV1
     * @param array $primaryKeyV1
     * @param string $refKeyV2
     * @return array[]
     */
    private static function transformDataSiakadV1ToV2(
        array $mappingColumns,
        array $dataSiakadV1,
        array $primaryKeyV1,
        string $refKeyV2
    ): array
    {
        $resultData = $parentColumns = [];
        foreach ($dataSiakadV1 as $key => $record) {
            foreach ($mappingColumns as $column => $columnRef) {
                // get value
                [$value, $isFromDefaultValue] = self::getValueFromColumn($record, $column, $columnRef);

                // set parent columns
                if (!empty($columnRef['pkv1']) && !empty($columnRef['pkv2'])) {
                    $parentColumns[$columnRef['column']]['pkv1'] = $columnRef['pkv1'];
                    $parentColumns[$columnRef['column']]['pkv2'] = $columnRef['pkv2'];
                }

                // formating value
                $value = self::formattingValue($value, $columnRef);

                // handle not null (jika not null dan value kosong, maka data tidak akan diinsert)
                if (empty($value) && !empty($columnRef['notnull'])) {
                    unset($resultData[$key]);
                    break;
                } else {
                    $resultData[$key][$columnRef['column']] = $value;

                    // set default value
                    if ($isFromDefaultValue) {
                        $resultData[$key]['_is_has_default_' . $columnRef['column']] = true;
                    }
                }
            }

            // set ref_key_siakad
            if (!empty($resultData[$key])) {
                // concat pk
                $refKeySiakad = array_map(fn($column) => $record->$column, $primaryKeyV1);
                $resultData[$key][$refKeyV2] = implode('|', $refKeySiakad);
            }
        }

        return [$resultData, $parentColumns];
    }

    /**
     * @param stdClass $record
     * @param string $column
     * @param array $columnRef
     * @return array
     */
    private static function getValueFromColumn(
        stdClass $record,
        string $column,
        array $columnRef,
    ): array
    {
        $isFromDefaultValue = false;

        // value dari text
        if (empty($columnRef['options'])) {
            $defaultValue = $columnRef['default'] ?? null;
            // cek is callable
            if (!empty($defaultValue) && is_callable($defaultValue)) {
                $defaultValue = $columnRef['default']($record);
            }

            $value = $record->$column ?? $defaultValue;

            // cek jika default value
            if (empty($record->$column) && !empty($defaultValue)) {
                $isFromDefaultValue = true;
            }

            return [$value, $isFromDefaultValue];
        }

        // value dari options
        $indexRef = null;
        $ref = $columnRef['options'];

        // untuk search column apabila tidak ada record dari v1
        if (!empty($columnRef['search'])) {
            $column = $columnRef['search'];
        }
        if (!empty($record->$column)) {
            $isStrict = $columnRef['is_strict_search'] ?? false;
            $indexRef = array_search($record->$column, $ref, strict: $isStrict);
        }

        if (isset($indexRef) && $indexRef !== false) {
            $value = $indexRef;
        }

        if (empty($value)) {
            $value = isset($record->$column) ? ($ref[$record->$column] ?? null) : null;
        }

        return [$value, $isFromDefaultValue];
    }

    /**
     * @param mixed $value
     * @param array $columnRef
     * @return mixed
     */
    private static function formattingValue(mixed $value, array $columnRef): mixed {
        if (!empty($columnRef['lowercase'])) {
            return strtolower($value);
        } elseif (!empty($columnRef['uppercase'])) {
            return strtoupper($value);
        }

        return $value;
    }
}
