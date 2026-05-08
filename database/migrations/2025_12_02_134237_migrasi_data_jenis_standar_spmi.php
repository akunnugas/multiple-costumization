<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\JenisStandar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $jenisStandar4S = JenisStandar::where('kode_jenis_standar', '4S')
            ->where('apakah_data_default', true)
            ->first();
        if ($jenisStandar4S) {
            $jenisStandar4S->update([
                'nama_jenis_standar' => '4 Standar IAPS 5.0'
            ]);
        }

        $jenisStandar9S = JenisStandar::where('kode_jenis_standar', '9S')
            ->where('apakah_data_default', true)
            ->first();
        if ($jenisStandar9S) {
            $jenisStandar9S->update([
                'nama_jenis_standar' => '9 Standar IAPS 4.0'
            ]);
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
