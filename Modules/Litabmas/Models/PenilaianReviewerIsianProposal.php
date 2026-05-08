<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class PenilaianReviewerIsianProposal extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.penilaian_reviewer_isian_proposal';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan_reviewer',
        'id_pengajuan_pendanaan_isian_proposal',
        'feedback_isian_proposal',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan_reviewer' => ['required' => true, 'options' => PengajuanPendanaanReviewer::class],
        'id_pengajuan_pendanaan_isian_proposal' => ['required' => true, 'options' => PengajuanPendanaanIsianProposal::class],
        'feedback_isian_proposal' => [], // Feedback
    ];

    public function pengajuan_pendanaan_reviewer()
    {
        return $this->belongsTo(PengajuanPendanaanReviewer::class, 'id_pengajuan_pendanaan_reviewer');
    }
}
