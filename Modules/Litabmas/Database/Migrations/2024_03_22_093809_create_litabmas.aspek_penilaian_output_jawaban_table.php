<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // TODO: resourcenya belum ada (hanya ada modelnya)
        SevimaSchema::create('litabmas.aspek_penilaian_output_jawaban', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\AspekPenilaianOutputPertanyaan::class, 'id_aspek_penilaian_output_pertanyaan');
            $table->string('jawaban_penilaian_output', 255);

            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.aspek_penilaian_output_jawaban');
    }
};
