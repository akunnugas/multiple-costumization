<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class PenilaianReviewerOutputBersama extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.penilaian_reviewer_output_bersama';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan',
        'id_pengajuan_pendanaan_reviewer_pembuat',
        'id_pengajuan_pendanaan_reviewer_pengubah',
        'id_aspek_penilaian_output_pertanyaan',
        'id_aspek_penilaian_output_jawaban',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan' => ['required' => true, 'options' => PengajuanPendanaan::class],
        'id_pengajuan_pendanaan_reviewer_pembuat' => ['required' => true, 'options' => PengajuanPendanaanReviewer::class],
        'id_pengajuan_pendanaan_reviewer_pengubah' => ['options' => PengajuanPendanaanReviewer::class],
        'id_aspek_penilaian_output_pertanyaan' => ['required' => true, 'options' => AspekPenilaianOutputPertanyaan::class],
        'id_aspek_penilaian_output_jawaban' => ['required' => true, 'options' => AspekPenilaianOutputJawaban::class],
    ];
}
