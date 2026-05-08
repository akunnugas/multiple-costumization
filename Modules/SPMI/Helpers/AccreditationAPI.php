<?php

namespace Modules\SPMI\Helpers;

use Illuminate\Support\Facades\DB;
use Modules\SPMI\Helpers\AccreditationSync;

class AccreditationAPI
{

    /**
     * Save mapping data from SPME API.
     * 
     * @param string $modelSPMI
     * @param array $mapping
     * @param array $data
     * @param string $idAPI
     */
    public static function saveMapping($modelSPMI, $mapping = [], $data, $idAPI, $reference = [], $params = null, $defaultValue = [])
    {

        $addRecord = [];
        if (!empty($defaultValue)) {
            foreach ($defaultValue as $column => $value) {
                $addRecord[$column] = $value;
            }
        }

        DB::beginTransaction();

        $sum = $sucess = $error = 0;
        $records = $msg = $child = [];
        if (!empty($data)) {
            foreach ($data as $key => $row) {
                if (empty($row)) continue;
                $isError = false;
                $sum++;
                foreach ($mapping as $column => $value) {
                    if ($value == 'arrchildren') {
                        $isError = false;
                        $child[$column][$key] = $row[$column];
                        continue;
                    }

                    if (array_key_exists($value, $reference)) {
                        if (!empty($reference[$value][$row[$column]])) {
                            $isError = false;
                            $records[$key][$value] = $reference[$value][$row[$column]];
                        } else {
                            $isError = true;
                            $msg[$key][$column] = "Failed to find " . $row[$column] . " from reference data " . $value;
                            $records[$key][$value] = $row[$column];
                        }
                    } else {
                        if (strpos($value, '|') !== false) {
                            list($value, $columnref) = explode('|', $value);
                            // search key from $data
                            $keyref = array_search($row[$column], array_column($data, $columnref));

                            $isError = false;
                            $records[$key][$value] =  ($keyref !== false ? $keyref + 1 : $row[$column]);
                        } else {
                            $isError = false;
                            $defaultvalue = null;
                            if (strpos($value, '.') !== false) {
                                list($value, $defaultvalue) = explode('.', $value);
                            }

                            if (isset($row[$column])) {
                                $records[$key][$value] = $row[$column];
                            } else {
                                $records[$key][$value] = $defaultvalue ?? null;
                            }
                        }
                    }
                }

                // add record if exist
                $records[$key] = array_merge($records[$key], $addRecord);

                if ($isError) {
                    $error++;
                } else {
                    $sucess++;
                }
            }
        }

        $response = false;
        if ($error < 1) {
            try {
                $response = $modelSPMI::insert($records);
            } catch (\Exception $e) {
                $response = false;
            }
        }


        $err = false;
        $message = 'Success to sync data from SPMI API.';

        if (!$response) {
            DB::rollBack();
            $err = true;
            $message = 'Failed to sync data from SPMI API.';
        }

        DB::commit();

        return [$err, $message, $child];
    }
}
