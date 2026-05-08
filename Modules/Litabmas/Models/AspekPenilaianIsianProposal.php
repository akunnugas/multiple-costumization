<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Litabmas\Enums\JenisPendanaanEnum;

class AspekPenilaianIsianProposal extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.aspek_penilaian_isian_proposal';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_periode_pendanaan',
        'kode_jenis_pendanaan',
        'nama_isian_proposal',
        'urutan_isian_proposal'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_periode_pendanaan' => ['required' => true, 'options' => PeriodePendanaan::class],                  // Periode Pendanaan
        'kode_jenis_pendanaan' => ['required' => true, 'options' => JenisPendanaanEnum::CODES],                // Jenis Pendanaan
        'nama_isian_proposal' => ['required' => true, 'maxlength' => 255],                                     // Isian Proposal
        'urutan_isian_proposal' => ['required' => true, 'type' => 'numeric', 'min' => 1],                      // Urutan
    ];

    /**
     * Utk validasi unique composite.
     * @return array[]
     */
    protected static function uniqueColumns(): array
    {
        return [
            'uniqueNamaIsianProposal' => [
                'defaultMessage' => __('validation.unique', [
                    'attribute' => __('litabmas::aspek_penilaian_isian_proposal.nama_isian_proposal')
                ]),
                'fields' => ['id_periode_pendanaan', 'kode_jenis_pendanaan', 'nama_isian_proposal']
            ],
            'uniqueUrutanIsianProposal' => [
                'defaultMessage' => __('validation.unique', [
                    'attribute' => __('litabmas::aspek_penilaian_isian_proposal.urutan_isian_proposal')
                ]),
                'fields' => ['id_periode_pendanaan', 'kode_jenis_pendanaan', 'urutan_isian_proposal']
            ]
        ];
    }

    /**
     * relasi ke pengajuan pendanaan isian proposal
     */
    public function pengajuanPendanaanIsianProposal(){
        return $this->hasMany(PengajuanPendanaanIsianProposal::class, 'id_aspek_penilaian_isian_proposal');
    }
}
