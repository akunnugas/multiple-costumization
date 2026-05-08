<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\PenilaianMatriks;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.penilaian_matriks_referensi', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(PenilaianMatriks::class, 'id_penilaian_matriks');
            $table->unsignedBigInteger('id_butir_referensi')->comment('Reference Item');
            $table->string('jenis_referensi')->comment('Reference Type (pr: Laporan Kinerja, se: Evaluasi Diri)');

            $table->logs(false);
        });

        DB::statement('CREATE UNIQUE INDEX ON spmi.penilaian_matriks_referensi (id_penilaian_matriks, id_butir_referensi)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.penilaian_matriks_referensi');
    }
};
