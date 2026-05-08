<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class PenilaianMatriksPredikat extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.penilaian_matriks_predikat';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_penilaian_matriks',
        'id_skor_matriks_predikat_penilaian',
        'deskripsi',
        'kriteria',
        'rumus_penilaian',
        'apakah_nonaktif'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_penilaian_matriks' => ['required' => true, 'options' => PenilaianMatriks::class], // Matrix Penilaian
        'id_skor_matriks_predikat_penilaian' => ['required' => true, 'options' => SkorMatriksPredikatPenilaian::class], // Skor Predikat Penilaian
        'deskripsi' => ['required' => false], // Uraian Predikat
        'kriteria' => ['maxlength' => 100], // Kriteria Skor
        'rumus_penilaian' => ['required' => false], // Rumus
        'apakah_nonaktif' => ['type' => 'boolean'], // Status Skor
    ];
}
