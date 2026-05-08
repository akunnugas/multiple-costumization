<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $skors = SkorMatriksPredikatPenilaian::all();
        foreach ($skors as $skor) {
            $arrDeskripsi = explode(' - ', $skor->deskripsi, 2);
            $skor->deskripsi = trim($arrDeskripsi[1]);
            $skor->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
