<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\PenilaianMatriks;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        PenilaianMatriks::where('butir_indikator_spme', true)->update([
            'butir_indikator_iku' => true,
        ]);
        PenilaianMatriks::where('butir_indikator_spme', false)->update([
            'butir_indikator_iku' => false,
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
