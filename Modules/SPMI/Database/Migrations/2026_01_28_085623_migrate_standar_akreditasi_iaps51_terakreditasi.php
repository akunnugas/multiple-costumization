<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\AkreditasiStandar;
use Modules\SPMI\Models\JenisStandar;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $mappedMapping = [
            '1K' => [
                'A', 'A1','A2','A3','A4'
            ],
            '2K.1' => [
                'B1', 'B105','B106A','B106B','B106C','B106D','B106E','B107','B108A','B108B','B108C','B109','B110A','B110B','B110C','B111','B112A','B112B','B113','B114A','B114B','B115'
            ],
            '2K.2' => [
                'B2', 'B216A','B216B','B216C','B217A','B217A','B217B','B218A','B218B','B218C','B219'
            ],
            '2K.3' => [
                'B3', 'B320A','B320B','B321A','B321B','B322A','B322B','B323A','B323B'
            ],
            '3K' => [
                'C', 'C24','C25','C26','C27','C28A','C28B','C28C','C29','C30','C31A','C31B','C32A','C32B','C33','C34','C35'
            ],
            '4K' => [
                'D', 'D36A','D36B','D37','D38','D39'
            ]
        ];

        $penilaianPanduan = PenilaianPanduan::where('kode_penilaian_panduan', 'IAPS5.1-S1-Akre')->first();
        foreach ($mappedMapping as $kode => $listButir) {
            $kodeStandar = AkreditasiStandar::where('kode_standar', $kode)
                ->where('apakah_data_default', true)
                ->first();
            if ($kodeStandar) {
                PenilaianMatriks::where('id_penilaian_panduan', $penilaianPanduan->id)
                    ->whereIn('nomor_penilaian', $listButir)
                    ->where('apakah_data_default', true)
                    ->update([
                        'id_akreditasi_standar' => $kodeStandar->id,
                    ]);
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
