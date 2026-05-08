<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\JenjangPendidikan;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\MappingLK;
use Modules\SPMI\Models\PengisianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $dataPengisian = PengisianPanduan::where('kode_pengisian_panduan', 'IAPS9')->first();

        if (empty($dataPengisian)) {
            return;
        }

        $dataLK = IndikatorLaporanKinerja::where('id_pengisian_panduan', $dataPengisian->id)
            ->where('apakah_data_default', true)->get();
        $jenjangPendidikan = JenjangPendidikan::where('kode_jenjang', 'S1')->first();

        if (empty($jenjangPendidikan)) {
            return;
        }

        DB::beginTransaction();

        foreach ($dataLK as $lk) {
            $mappingLK = MappingLK::where('id_indikator_laporan_kinerja', $lk->id)
                ->where('id_jenjang_pendidikan', $jenjangPendidikan->id);

            if ($mappingLK->count() > 1) {
                $itemToDeletes = $mappingLK->skip(1)->pluck('id')->toArray();

                MappingLK::whereIn('id', $itemToDeletes)->delete();
            }
        }

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
