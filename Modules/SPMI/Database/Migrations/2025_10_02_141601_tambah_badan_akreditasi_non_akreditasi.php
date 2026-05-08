<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Models\LembagaAkreditasi;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        LembagaAkreditasi::create([
            'nama_lembaga' => 'Bukan Badan Akreditasi',
            'kode_lembaga' => 'NONAKRED',
            'nama_singkat_lembaga' => 'NONAKREDITASI',
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
