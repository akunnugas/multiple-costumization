<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\PengisianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $protectedPanduan = [
            'IAPS9', // LKPS IAPS4
            'LEDPS9', // LED IAPS4
        ];

        PengisianPanduan::whereNotIn('kode_pengisian_panduan', $protectedPanduan)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
