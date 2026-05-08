<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class AspekPenilaianOutputJawaban extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.aspek_penilaian_output_jawaban';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_aspek_penilaian_output_pertanyaan',
        'jawaban_penilaian_output',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_aspek_penilaian_output_pertanyaan' => ['required' => true, 'options' => AspekPenilaianOutputPertanyaan::class],   // Periode Pendanaan
        'jawaban_penilaian_output' => ['required' => true, 'maxlength' => '255'],                                                                   // Jawaban Penilaian Output
    ];
}
