<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class PenilaianReviewerLaporanProgres extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.penilaian_reviewer_laporan_progres';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan_reviewer',
        'id_pengajuan_pendanaan_laporan_progres',
        'status_laporan_progres_reviewer',
        'feedback_laporan_progres',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan_reviewer' => ['required' => true, 'options' => PengajuanPendanaanReviewer::class],
        'id_pengajuan_pendanaan_laporan_progres' => ['required' => true, 'options' => PengajuanPendanaanLaporanProgres::class],
        'status_laporan_progres_reviewer' => ['maxlength' => 25],
        'feedback_laporan_progres' => [], // Feedback
    ];

    /**
     * Status Laporan
     */
    const STATUS_LAP_BELUM_DINILAI = 'belum_dinilai';
    const STATUS_LAP_DIREVISI = 'direvisi';
    const STATUS_LAP_DISETUJUI = 'disetujui';
    const STATUS_LAPORAN_OPTIONS = [
        self::STATUS_LAP_BELUM_DINILAI => 'Belum Dinilai',
        self::STATUS_LAP_DIREVISI => 'Direvisi',
        self::STATUS_LAP_DISETUJUI => 'Disetujui'
    ];
    const STATUS_LAPORAN_PROGRES_REVIEWER = [ // adalah nama kolom nya
        self::STATUS_LAP_BELUM_DINILAI => [
            'value' => self::STATUS_LAP_BELUM_DINILAI,
            'text' => 'Belum Dinilai',
            'variant' => 'warning',
        ],
        self::STATUS_LAP_DIREVISI => [
            'value' => self::STATUS_LAP_DIREVISI,
            'text' => 'Direvisi',
            'variant' => 'warning'
        ],
        self::STATUS_LAP_DISETUJUI => [
            'value' => self::STATUS_LAP_DISETUJUI,
            'text' => 'Disetujui',
            'variant' => 'success'
        ],
    ];
}
