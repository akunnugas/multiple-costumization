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
            'kode_lembaga' => LembagaAkreditasi::LAMDEPILAR,
            'nama_lembaga' => 'Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur',
            'nama_singkat_lembaga' => 'LAM Depilar',
        ]);

        LembagaAkreditasi::create([
            'kode_lembaga' => LembagaAkreditasi::LAMSPAK,
            'nama_lembaga' => 'Lembaga Akreditasi Mandiri Sosial Politik Administrasi dan Komunikasi',
            'nama_singkat_lembaga' => 'LAM Spak',
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
