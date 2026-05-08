<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\SPMI\Models\DataPengisianLK;
use Modules\SPMI\Models\IndikatorLaporanKinerja;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('spmi.data_pengisian_lk', function (SevimaBlueprint $table) {
            $table->text('jenis_data_pengisian_lk')->nullable()->after('data_pengisian_lk')->comment('Jenis Data Pengisian LK');
        });

        $listRowDataPengisianLK = DB::table('spmi.data_pengisian_lk as dlk')
            ->select('dlk.*')
            ->join('spmi.indikator_laporan_kinerja as i', 'dlk.id_indikator_laporan_kinerja', '=', 'i.id')
            ->where('i.jenis_form', IndikatorLaporanKinerja::FORM_ROW)
            ->pluck('id')
            ->toArray();

        foreach ($listRowDataPengisianLK as $id) {
            $dataPengisianLK = DataPengisianLK::find($id);
            if (!empty($dataPengisianLK->data_pengisian_lk)) {
                $dataPengisianLK->jenis_data_pengisian_lk = $this->reMappingData(json_decode($dataPengisianLK->data_pengisian_lk, true));
                $dataPengisianLK->save();
            }
        }
    }

    function reMappingData($data_now) {
        $remapped_data = [];

        if ($this->isAssoc($data_now)) {
            // Jika $data_now adalah array asosiatif
            foreach ($data_now as $key => $value) {
                $manualArray = array_fill(0, count($value), "get");
                $remapped_data[$key] = $manualArray;
            }
        } else {
            // Jika $data_now adalah array numerik
            $remapped_data = array_fill(0, count($data_now), "get");
        }

        return $remapped_data;
    }

    function isAssoc($array) {
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
