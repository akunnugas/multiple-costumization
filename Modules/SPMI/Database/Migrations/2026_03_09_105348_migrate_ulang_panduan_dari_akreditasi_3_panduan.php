<?php

use Illuminate\Database\Migrations\Migration;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Services\NewAkreditasiManagementServices;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $services = new NewAkreditasiManagementServices();

        $configPanduan = [
            "LAMDIK-S1-3.0" => [
                "kode_lk" => "LAMDIK5",
                "kode_led" => "EDIKS1",
            ],
            "INFOKOM2.1S1" => [
                "kode_lk" => "INFOKS1",
                "kode_led" => "DINFOS1",
            ],
            "LAMTEKNIKS1-25" => [
                "kode_lk" => "LKTEK25",
                "kode_led" => "LEDTT25",
            ]
        ];

        $listPanduanBaru = $services->getPanduanPenilaian(array_keys($configPanduan));
        $listPanduanBaru = json_decode(json_encode($listPanduanBaru), true);

        foreach ($listPanduanBaru as $panduanRaw) {
            if (empty($panduanRaw["idjenjang"])) {
                echo "\n Panduan " .
                    $panduanRaw["namapanduan"] .
                    " di lewati karena tidak memiliki jenjang";
                continue; // skip panduan tanpa jenjang
            }

            [
                "kode_lk" => $kodeLK,
                "kode_led" => $kodeLED,
            ] = $configPanduan[$panduanRaw["kodespmi"]];

            $services->syncButirLEDPanduan($kodeLED);
            $services->syncButirLKPanduan($kodeLK);

            $penilaianPanduanNew = PenilaianPanduan::where([
                "kode_penilaian_panduan" => $panduanRaw["kodespmi"],
                'apakah_data_default' => true
            ])->first();

            $services->syncButirMatriks($penilaianPanduanNew->kode_penilaian_panduan);

            $syaratPerlu = $services->getSyaratPerlu($penilaianPanduanNew->kode_penilaian_panduan);
            if (!empty($syaratPerlu)) {
                $services->syncSyaratPerlu($penilaianPanduanNew->kode_penilaian_panduan, $syaratPerlu);
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
