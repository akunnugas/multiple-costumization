<?php

use Illuminate\Support\Arr;
use Modules\SPMI\Models\TarikDataLK;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Modules\SPMI\Models\PengisianPanduan;
use Illuminate\Database\Migrations\Migration;
use Modules\SPMI\Models\IndikatorLaporanKinerja;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $butirTarikData = [
            '1.1.a.1',
            '1.1.a.2',
            '1.1.a.3',
            '1.1.a.4',
            '1.1.a.5',
            '1.1.b.6.1',
            '1.1.c.7',
            '1.1.c.8a',
            '1.1.c.8b',
            '1.1.c.8c',
            '1.1.c.8d',
            '1.1.d.11',
            '1.1.d.12',
            '1.1.d.13',
            '1.1.d.14',
            '1.2.1',
            '1.2.2',
            '1.2.3',
            '1.2.4',
        ];

        $mapSumberData = [
            'SDM' => 'siakad_hr',
            'AK' => 'siakad',
            'TS' => 'tracer_study',
            'MBK' => 'mbkm',
        ];

        $pengisianPanduan = PengisianPanduan::where(['kode_pengisian_panduan' => 'IAPS5.1', 'apakah_data_default' => true])->first();
        $indikatorLaporanKinerja = IndikatorLaporanKinerja::whereIn('nomor_indikator', $butirTarikData)->where('id_pengisian_panduan', $pengisianPanduan->id)->get();
        
        $dataPengisianLK = $indikatorLaporanKinerja->map(function ($indikator) use ($mapSumberData) {
            return [
                'id_indikator_laporan_kinerja' => $indikator['id'],
                'apakah_kurikulum' => false,
                'sumber' => $mapSumberData[$indikator['sumber_data']],
            ];
        });

        TarikDataLK::insert($dataPengisianLK->toArray());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
