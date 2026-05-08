<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class PengajuanPendanaanLaporanOutput extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengajuan_pendanaan_laporan_output';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan',
        'id_pengajuan_pendanaan_output_penelitian',
        'id_dokumen_output',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan' => ['required' => true, 'options' => PengajuanPendanaan::class], // Pengajuan Pendanaan
        'id_pengajuan_pendanaan_output_penelitian' => ['required' => true, 'options' => PengajuanPendanaanOutputPenelitian::class], // Pengajuan Pendanaan Output
        'id_dokumen_output' => ['file_type' => ['pdf'], 'max_size' => (1024 * 10)], // Dokumen Output
        'status_output' => ['maxlength' => 25, 'options' => self::STATUS_OPTIONS], // Status
    ];

    /**
     * Status Laporan Output
     */
    const STATUS_BELUM_DINILAI = 'belum_dinilai';
    const STATUS_DIREVISI = 'direvisi';
    const STATUS_DISETUJUI = 'disetujui';
    const STATUS_OPTIONS = [
        self::STATUS_BELUM_DINILAI => 'Belum Dinilai',
        self::STATUS_DIREVISI => 'Direvisi',
        self::STATUS_DISETUJUI => 'Disetujui',
    ];
    const STATUS_OUTPUT = [
        self::STATUS_BELUM_DINILAI => [
            'value' => self::STATUS_BELUM_DINILAI,
            'text' => 'Belum Dinilai',
            'variant' => 'warning',
        ],
        self::STATUS_DIREVISI => [
            'value' => self::STATUS_DIREVISI,
            'text' => 'Direvisi',
            'variant' => 'warning'
        ],
        self::STATUS_DISETUJUI => [
            'value' => self::STATUS_DISETUJUI,
            'text' => 'Disetujui',
            'variant' => 'success'
        ],
    ];
}
