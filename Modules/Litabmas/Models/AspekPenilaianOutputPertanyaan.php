<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class AspekPenilaianOutputPertanyaan extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.aspek_penilaian_output_pertanyaan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_periode_pendanaan',
        'no',
        'pertanyaan_penilaian_output',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_periode_pendanaan' => ['required' => true, 'options' => PeriodePendanaan::class],   // Periode Pendanaan
        'no' => ['required' => true],                                                           // No
        'pertanyaan_penilaian_output' => ['required' => true],                                  // Pertanyaan Penilaian Output
    ];

    // relation to jawaban
    public function jawaban()
    {
        return $this->hasMany(AspekPenilaianOutputJawaban::class, 'id_aspek_penilaian_output_pertanyaan');
    }

    // relation to periode pendanaan
    public function penilaianReviewerOutputBersama()
    {
        return $this->hasMany(PenilaianReviewerOutputBersama::class, 'id_aspek_penilaian_output_pertanyaan');
    }
}
