<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\DataPengisianLK;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        $listRowDataPengisianLK = DataPengisianLK::orderBy('id', 'desc')->get();

        foreach ($listRowDataPengisianLK as $dataPengisianLK) {
            if (!empty($dataPengisianLK->data_pengisian_lk)) {
                $data = $this->reMappingData(json_decode($dataPengisianLK->data_pengisian_lk, true));

                DB::table('spmi.data_pengisian_lk')
                    ->where('id', $dataPengisianLK->id)
                    ->update(['data_pengisian_lk' => json_encode($data)]);
            }
        }
    }

    function reMappingData($data_now) {
        $remapped_data = [];

        if ($this->isAssoc($data_now)) {
            // Jika $data_now adalah array asosiatif
            foreach ($data_now as $index => $arr) {
                foreach ($arr as $key => $val_array) {
                    foreach ($val_array as $key2 => $val) {
                        if (is_numeric($val)) {
                            // fix data yang seharusnya bertipe numeric
                            $val = (float) $val;
                        } else {
                            // fix data yang seharusnya bertipe string
                            $val = preg_replace('/\s+/', ' ', $val);
                        }

                        $remapped_data[$index][$key][$key2] = $val;
                    }
                }
            }
        } else {
            // Jika $data_now adalah array numerik
            foreach ($data_now as $key => $val_array) {
                foreach ($val_array as $key2 => $val) {
                    if (is_numeric($val)) {
                        // fix data yang seharusnya bertipe numeric
                        $val = (float) $val;
                    } else {
                        // fix data yang seharusnya bertipe string
                        $val = preg_replace('/\s+/', ' ', $val);
                    }

                    $remapped_data[$key][$key2] = $val;
                }
            }
        }


        return $remapped_data;
    }

    function isAssoc($array)
    {
        if (!is_array($array)) return false;
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
