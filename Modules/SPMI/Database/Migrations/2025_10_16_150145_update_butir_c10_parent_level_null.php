<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\PenilaianMatriks;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        PenilaianMatriks::where('nomor_penilaian', 'C.10')->update(['id_parent' => null, 'info_level' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
