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
        JenisStandar::where(['kode_jenis_standar' => '4S', 'apakah_data_default' => true])
            ->update(['nama_jenis_standar' => '4 Standar IAPS 5.1']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        JenisStandar::where(['kode_jenis_standar' => '4S', 'apakah_data_default' => true])
            ->update(['nama_jenis_standar' => '4 Standar IAPS 5.0']);
    }
};
