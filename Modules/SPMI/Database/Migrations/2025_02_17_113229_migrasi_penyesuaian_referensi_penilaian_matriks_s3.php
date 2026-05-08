<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\PenilaianMatriksReferensi;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        PenilaianMatriksReferensi::whereIn('id', [918, 919, 920])
            ->update(['id_butir_referensi' => 45]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
