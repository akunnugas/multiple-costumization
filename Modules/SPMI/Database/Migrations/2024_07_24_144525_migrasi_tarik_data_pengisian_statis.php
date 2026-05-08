<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\TarikDataLK;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $arrTarikData = [
            'siakad' => ['2a', '2b', '5a', '8c.1', '8c.2', '8a'],
            'karirlink' => ['8d.1b', '8d.2', '8e.1', '8e.2.Ref'],
            'siakad_hr' => [
                '3a.1',
                '3a.2',
                '3a.3',
                '3a.4',
                '3b.2',
                '3b.3',
                '3b.4a',
                '3b.4b',
                '3b.7-1',
                '3b.7-2',
                '3b.7-4',
                '4',
                '6a',
                '7',
                '8f.1a',
                '8f.1b',
                '8f.4-1',
                '8f.4-2',
                '8f.4-4',
            ],
        ];
        $arrTarikDataKurikulum = ['5a'];

        $panduanPengisian = PengisianPanduan::where('kode_pengisian_panduan', 'IAPS9')->first();

        DB::beginTransaction();
        try {
            foreach ($arrTarikData as $source => $arrButir) {
                foreach ($arrButir as $butir) {
                    $indikator = IndikatorLaporanKinerja::where('id_pengisian_panduan', $panduanPengisian->id)
                        ->where('nomor_indikator', $butir)
                        ->first();

                    TarikDataLK::create([
                        'id_indikator_laporan_kinerja' => $indikator->id,
                        'apakah_kurikulum' => in_array($butir, $arrTarikDataKurikulum),
                        'sumber' => $source,
                    ]);
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
