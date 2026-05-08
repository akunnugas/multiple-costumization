<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\SPMI\Models\PenilaianMatriks;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $penilaianMatriks = PenilaianMatriks::whereIn('nomor_penilaian', [60, 61, 62])
            ->get();

        DB::beginTransaction();

        $penilaianMatriks->each(function ($item) {
            // replace deskripsi dari NJ / NL menjadi NL / NJ
            $item->deskripsi = str_replace('NJ / NL', 'NL / NJ', $item->deskripsi);
            $item->save();
        });

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $penilaianMatriks = PenilaianMatriks::whereIn('nomor_penilaian', [60, 61, 62])
            ->get();

        DB::beginTransaction();

        $penilaianMatriks->each(function ($item) {
            // replace deskripsi dari NL / NJ menjadi NJ / NL
            $item->deskripsi = str_replace('NL / NJ', 'NJ / NL', $item->deskripsi);
            $item->save();
        });

        DB::commit();
    }
};
