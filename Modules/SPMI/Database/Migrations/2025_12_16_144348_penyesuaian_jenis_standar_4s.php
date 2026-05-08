<?php

use Modules\SPMI\Models\JenisStandar;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Modules\SPMI\Models\AkreditasiStandar;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $standarKriteria = JenisStandar::where("kode_jenis_standar", "4S")->first();
        $listStandar = [
            "1K" => "Budaya Mutu",
            "2K.1" => "Relevansi Pendidikan",
            "2K.2" => "Relevansi Penelitian",
            "2K.3" => "Relevansi Pengabdian kepada Masyarakat",
            "3K" => "Akuntabilitas",
            "4K" => "Diferensiasi Misi"
        ];
        foreach ($listStandar as $kode => $label) {
            AkreditasiStandar::updateOrCreate(
                [
                    "kode_standar" => $kode,
                    "id_jenis_standar" => $standarKriteria->id
                ],
                [
                    "nama_standar" => $label,
                    "apakah_data_default" => true
                ]
            );
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
