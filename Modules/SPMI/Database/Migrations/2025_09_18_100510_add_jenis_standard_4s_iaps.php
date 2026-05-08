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
        JenisStandar::create([
            'kode_jenis_standar' => '4S',
            'nama_jenis_standar' => '4 Standar',
            'apakah_data_default' => true,
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
