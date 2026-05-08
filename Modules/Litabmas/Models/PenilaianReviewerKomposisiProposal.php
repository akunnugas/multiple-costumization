<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class PenilaianReviewerKomposisiProposal extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.penilaian_reviewer_komposisi_proposal';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan_reviewer',
        'id_aspek_penilaian_komposisi_proposal',
        'skala_nilai_komposisi_proposal',
    ];

    const SKALA_NILAI = [
        0 => '0 (Kosong)',
        1 => '1 (Tidak Baik)',
        2 => '2 (Kurang Baik)',
        3 => '3 (Cukup)',
        4 => '4 (Baik)',
        5 => '5 (Sangat Baik)',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan_reviewer' => ['required' => true, 'options' => PengajuanPendanaanReviewer::class],
        'id_aspek_penilaian_komposisi_proposal' => ['required' => true, 'options' => AspekPenilaianKomposisiProposal::class],
        'skala_nilai_komposisi_proposal' => ['options' => self::SKALA_NILAI], // Nilai
    ];
}
