<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class PengajuanPendanaanIsianProposal extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengajuan_pendanaan_isian_proposal';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan',
        'id_aspek_penilaian_isian_proposal',
        'isian_proposal',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan' => ['required' => true, 'options' => PengajuanPendanaan::class],                                // Pengajuan Pendanaan
        'id_aspek_penilaian_isian_proposal' => ['required' => true, 'options' => AspekPenilaianIsianProposal::class],            // Aspek Penilaian Proposal
        'isian_proposal' => ['required' => true, 'min' => 100],                                                                                                  // Isi Proposal
    ];
}
