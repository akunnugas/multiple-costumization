<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\SPMI\Models\PenilaianPanduan;

class SkorMatriksPredikatPenilaian extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.skor_matriks_predikat_penilaian';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_penilaian_panduan',
        'nilai',
        'deskripsi',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_penilaian_panduan' => ['required' => true, 'options' => PenilaianPanduan::class], // Panduan Penilaian
        'nilai' => ['required' => true, 'type' => 'numeric'], // Skor
        'deskripsi' => [
            'required' => true,
            'maxlength' => 100
        ], // Uraian Skor
    ];
}
