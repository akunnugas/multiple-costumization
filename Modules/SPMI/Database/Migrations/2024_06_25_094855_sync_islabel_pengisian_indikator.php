<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Helpers\AccreditationSync;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\PengisianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $service = new AccreditationSync;
        $pengisianPanduan =  PengisianPanduan::where('tipe_edisi', AkreditasiBuku::PERFORMANCE_REPORT)->where('kode_pengisian_panduan', 'IAPS9')->pluck('kode_pengisian_panduan', 'id')->toArray();

        foreach ($pengisianPanduan as $key => $value) {
            $indicatorPerformanceReportsSPME = $service->getIndicator($value);

            foreach ($indicatorPerformanceReportsSPME['data'] as $newindikator) {
                $oldindikator = IndikatorLaporanKinerja::where('id_pengisian_panduan', $key)->where('nomor_indikator', $newindikator['nobutir'])->first();
                if ($oldindikator) {
                    $oldindikator->update([
                        'apakah_parent' => $newindikator['islabel'] == '1'
                    ]);

                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
