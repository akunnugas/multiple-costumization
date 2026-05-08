<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\AkreditasiStandar;
use Modules\SPMI\Models\JenisStandar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $jenisStandar4s = JenisStandar::where('kode_jenis_standar', '4S')->where('apakah_data_default', true)->first();
        $jenisStandar9s = JenisStandar::where('kode_jenis_standar', '9S')->where('apakah_data_default', true)->first();

        // Tambah standar untuk IAPS 9s
        AkreditasiStandar::updateOrCreate([
            'kode_standar' => 'C.10',
            'id_jenis_standar' => $jenisStandar9s->id,
            'apakah_data_default' => true,
        ], [
            'nama_standar' => 'Indikator Tambahan',
        ]);

        // Tambah standar untuk IAPS 4s
        AkreditasiStandar::updateOrCreate([
            'kode_standar' => 'IKT',
            'id_jenis_standar' => $jenisStandar4s->id,
            'apakah_data_default' => true,
        ], [
            'nama_standar' => 'Indikator Tambahan',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
