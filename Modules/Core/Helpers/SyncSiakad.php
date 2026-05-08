<?php

namespace Modules\Core\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SyncSiakad
{
    /**
     * Sync data from siakad v1 to v2
     *
     * @param array $mappings
     * @param array $records
     * @param Model $model
     * @param array $pk
     * @param string $pkKey
     * @param array|null $otherRefKeys jika value dari tiap pk key ada, maka tidak akan dihapus.
     * @return array
     */
    public static function sync(
        array $mappings,
        array $records,
        mixed $model,
        array $pk = [],
        string $pkKey = 'ref_key_siakad',
        array $otherRefKeys = null,
        array $currentSiakadRefKeys = []
    ): array {
        $data = $parentColumns = [];
        foreach ($records as $key => $record) {
            // mapping data
            foreach ($mappings as $column => $columnRef) {
                $result = null;
                // if column is reference
                if (!empty($columnRef['options'])) {
                    $indexRef = null;
                    $ref = $columnRef['options'];

                    // untuk search column apabila tidak ada record dari v1
                    if (!empty($columnRef['search'])) {
                        $column = $columnRef['search'];
                    }
                    if (!empty($record[$column])) {
                        $indexRef = array_search($record[$column], $ref);
                    }

                    if (isset($indexRef) && $indexRef !== false) {
                        $result = (empty($columnRef['lowercase']) ? $indexRef : strtolower($indexRef));
                    }

                    $isDefaultValue = $columnRef['defaultValue'] ?? true;
                    if (empty($result) && $isDefaultValue) {
                        $result = isset($record[$column]) ? ($ref[$record[$column]] ?? null) : null;
                    }
                } else {
                    $value = $record[$column] ?? (isset($columnRef['default_column']) ? ($record[$columnRef['default_column']] ?? null) : ($columnRef['default'] ?? null));
                    $value = (empty($columnRef['lowercase']) ? $value : strtolower($value));
                    $result = $value;

                    if (!empty($columnRef['pkv1']) && !empty($columnRef['pkv2'])) {
                        $parentColumns[$columnRef['column']]['pkv1'] = $columnRef['pkv1'];
                        $parentColumns[$columnRef['column']]['pkv2'] = $columnRef['pkv2'];
                    }
                }

                // jika notnull maka data kalau result kosong akan dihapus
                if (empty($result) && !empty($columnRef['notnull'])) {
                    unset($data[$key]);
                    break;
                } else {
                    $data[$key][$columnRef['column']] = is_bool($result) ? $result : (Cstr::isEmpty($result) ? null : $result);
                }
            }

            // set ref_key_siakad
            $refKeySiakad = [];
            if (!empty($data[$key])) {
                foreach ($pk as $column) {
                    $refKeySiakad[] = $record[$column];
                }

                // concat pk
                $data[$key][$pkKey] = implode('|', $refKeySiakad);
            }
        }

        // update or create data
        $err = false;
        $msg = null;
        $dataRef = [];
        $siakadRefKeys = $currentSiakadRefKeys ?? [];
        foreach ($data as $key => $record) {
            // NOTE: Tidak perlu transaksi, karena hanya insert/update 1 record saja
            if (!empty($record[$pkKey])) {
                $siakadRefKeys[] = $record[$pkKey];
            }

            foreach ($parentColumns as $column => $parentColumn) {
                $record[$column] = $dataRef[$record[$column]] ?? null;
            }

            try {

                $response = $model::updateOrCreate([$pkKey => $record[$pkKey]], $record);

                foreach ($parentColumns as $column => $parentColumn) {
                    $dataRef[$response->{$parentColumn['pkv1']}] = $response->{$parentColumn['pkv2']};
                }
            } catch (\Throwable $th) {
                if ($th instanceof ValidationException) {
                    $record[$pkKey] = null;

                    continue;
                };

                $err = true;
                $msg = $th->getMessage();
                Log::channel('sync')->error('Sync Siakad:', [
                    'message' => $msg,
                    'data' => $data,
                ]);
                break;
            }
        }

        // proses hapus data hasil sync
        if (!$err) {
            self::deleteNotAssigned($model, $pkKey, $siakadRefKeys, $otherRefKeys);
        }

        return [$err, ($err) ? $msg : 'Berhasil sinkronisasi data'];
    }

    /**
     * @param $model
     * @param $pkKey
     * @param $siakadRefKeys
     * @param $otherRefKeys
     * @return void
     */
    private static function deleteNotAssigned($model, $pkKey, $siakadRefKeys, $otherRefKeys)
    {
        // ganti metode delete menjadi looping, karena untuk mengatasi error diawal yg throw dan data selanjutnya menyebabkan tidak masuk
        $notAssigned = $model::when($otherRefKeys, function ($query) use ($otherRefKeys, $pkKey) {
            // combined $other ref key & $pkKey
            // handle yg combined key nya null jangan dihapus
            $query->where(function ($query) use ($pkKey, $otherRefKeys) {
                $query->whereNotNull($pkKey);
                foreach ($otherRefKeys as $otherRefKey) {
                    $query->whereNotNull($otherRefKey);
                }
            });
        })->where(function ($query) use ($pkKey, $siakadRefKeys) {
            $query->whereNotIn($pkKey, $siakadRefKeys)
                ->orWhereNull($pkKey);
        })
            ->get();

        foreach ($notAssigned as $item) {
            try {
                // NOTE: Tidak perlu transaksi, karena hanya delete 1 record saja
                $item->forceDelete();
            } catch (\Throwable $th) {
                Log::channel('sync')->error('Sync Siakad:', [
                    'message' => $th->getMessage(),
                    'data' => $item,
                ]);
            }
        }
    }
}
