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
        SevimaSchema::table('spmi.penilaian_matriks', function (Blueprint $table) {
            $table->boolean('butir_indikator_iku')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
