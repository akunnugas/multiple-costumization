<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class PenilaianReviewerPresentasiProposal extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.penilaian_reviewer_presentasi_proposal';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan_reviewer',
        'id_aspek_penilaian_presentasi_proposal',
        'skala_nilai_presentasi_proposal',
    ];

    const SKALA_NILAI = [
        0 => 'Tidak Ada',
        1 => 'Sangat Kurang Baik',
        2 => 'Kurang Baik',
        3 => 'Cukup',
        4 => 'Baik',
        5 => 'Sangat Baik',
    ];

        /**
     * Status Laporan
     */
    const STATUS_LAP_BELUM_DINILAI = 'belum_dinilai';
    const STATUS_LAP_DINILAI = 'dinilai';
    const STATUS_LAPORAN_OPTIONS = [
        self::STATUS_LAP_BELUM_DINILAI => 'Belum Dinilai',
        self::STATUS_LAP_DINILAI => 'Dinilai'
    ];
    const STATUS_LAPORAN_PROGRES_REVIEWER = [ // adalah nama kolom nya
        self::STATUS_LAP_BELUM_DINILAI => [
            'value' => self::STATUS_LAP_BELUM_DINILAI,
            'text' => 'Belum Dinilai',
            'variant' => 'warning',
        ],
        self::STATUS_LAP_DINILAI => [
            'value' => self::STATUS_LAP_DINILAI,
            'text' => 'Dinilai',
            'variant' => 'success'
        ],
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan_reviewer' => ['required' => true, 'options' => PengajuanPendanaanReviewer::class],
        'id_aspek_penilaian_presentasi_proposal' => ['required' => true, 'options' => AspekPenilaianPresentasiProposal::class],
        'skala_nilai_presentasi_proposal' => ['options' => self::SKALA_NILAI], // Nilai
    ];
}
