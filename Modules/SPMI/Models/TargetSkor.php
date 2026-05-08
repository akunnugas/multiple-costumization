<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class TargetSkor extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.target_skor';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_target_indikator',
        'id_penilaian_matriks',
        'id_predikat_matriks_penilaian',
        'nilai_default',
        'nilai',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_target_indikator' => ['required' => true, 'options' => TargetIndikator::class], // Target Capaian
        'id_penilaian_matriks' => ['required' => true, 'options' => PenilaianMatriks::class], // Matriks Penilaian
        'id_predikat_matriks_penilaian' => ['required' => true, 'options' => PenilaianMatriksPredikat::class], // Skor Matriks Penilaian
        'nilai_default' => ['required' => true], // Skor Default
        'nilai' => ['required' => true], // Skor
    ];
}
