<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksPredikat;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;

return new class extends Migration
{
    const SCORE_VERY_GOOD = 4;
    const SCORE_GOOD = 3;
    const SCORE_ENOUGH = 2;
    const SCORE_LESS = 1;
    const SCORE_VERY_LESS = 0;

    const SCORES = [
        self::SCORE_VERY_GOOD => '4 - Sangat Baik',
        self::SCORE_GOOD => '3 - Baik',
        self::SCORE_ENOUGH => '2 - Cukup',
        self::SCORE_LESS => '1 - Kurang',
        self::SCORE_VERY_LESS => '0 - Sangat Kurang',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        $mappedScores = self::SCORES;

        $mappedScoreWithMatriks = [];
        $penilaianPanduans = PenilaianPanduan::withTrashed()->get();
        foreach ($penilaianPanduans as $panduan) {
            $idsPenilaianMatriks = PenilaianMatriks::withTrashed()
                ->where("id_penilaian_panduan", $panduan->id)
                ->pluck("id")
                ->toArray();

            if (!empty($idsPenilaianMatriks)) {
                $penilaianMatriksPredikats = PenilaianMatriksPredikat::withTrashed()
                    ->whereIn("id_penilaian_matriks", $idsPenilaianMatriks)
                    ->get();

                $skorPredikats = [];
                foreach ($mappedScores as $nilai => $deskripsi) {
                    $skorMatriksPredikatPenilaian = SkorMatriksPredikatPenilaian::create([
                        "id_penilaian_panduan" => $panduan->id,
                        "nilai" => $nilai,
                        "deskripsi" => $deskripsi
                    ]);

                    $skorPredikats[$nilai] = $skorMatriksPredikatPenilaian->id;
                }

                foreach ($penilaianMatriksPredikats as $pmp) {
                    $idSkorMatriksPredikat = $skorPredikats[$pmp->nilai];
                    $mappedScoreWithMatriks[$panduan->id][$pmp->id_penilaian_matriks][$idSkorMatriksPredikat] = $pmp->nilai;
                }
            }
        }

        DB::statement(
            "ALTER TABLE spmi.penilaian_matriks_predikat ADD COLUMN id_skor_matriks_predikat_penilaian BIGINT NULL"
        );

        foreach ($mappedScoreWithMatriks as $idPenilaianPanduan => $objs) {
            foreach ($objs as $idMatriksPenilaian => $skors) {
                foreach ($skors as $idPredikatSkor => $nilai) {
                    PenilaianMatriksPredikat::withTrashed()
                        ->where([
                            "id_penilaian_matriks" => $idMatriksPenilaian,
                            "nilai" => $nilai
                        ])
                        ->update([
                            "id_skor_matriks_predikat_penilaian" => $idPredikatSkor
                        ]);
                }
            }
        }

        $invalidCount = DB::table("spmi.penilaian_matriks_predikat as pmp")
            ->join(
                "spmi.skor_matriks_predikat_penilaian as skmpp",
                "skmpp.id",
                "=",
                "pmp.id_skor_matriks_predikat_penilaian"
            )
            ->where(function ($q) {
                $q->whereColumn("pmp.nilai", "!=", "skmpp.nilai");
            })
            ->count();

        if ($invalidCount > 0) {
            DB::rollBack();
            throw new \RuntimeException(
                "Data skor tidak konsisten antara penilaian_matriks_predikat dan skor_matriks_predikat_penilaian. " .
                    "Jumlah baris yang tidak cocok: {$invalidCount}"
            );
        }

        DB::statement(
            "ALTER TABLE spmi.penilaian_matriks_predikat DROP COLUMN nilai"
        );
        DB::statement(
            "ALTER TABLE spmi.penilaian_matriks_predikat ALTER COLUMN id_skor_matriks_predikat_penilaian SET NOT NULL"
        );
        DB::statement("ALTER TABLE spmi.penilaian_matriks_predikat
            ADD CONSTRAINT fk_pmp_skor_predikat
            FOREIGN KEY (id_skor_matriks_predikat_penilaian)
            REFERENCES spmi.skor_matriks_predikat_penilaian(id)
            ON UPDATE CASCADE ON DELETE RESTRICT");

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
