<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class PengajuanPendanaanLaporanProgres extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengajuan_pendanaan_laporan_progres';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan',
        'jenis_laporan_progres',
        'id_dokumen_laporan_progres',
        'status_laporan_progres',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan' => ['required' => true, 'option' => PengajuanPendanaan::class], // Pengajuan pendanaan
        'jenis_laporan_progres' => ['required' => true, 'maxlength' => 25, 'options' => self::JENIS_LAP], // Jenis laporan
        'id_dokumen_laporan_progres' => ['file_type' => ['pdf'], 'max_size' => (1024 * 10)], // Dokumen laporan progres
        'status_laporan_progres' => ['required' => true, 'maxlength' => 255, 'options' => self::STATUS_LAP_OPTIONS], // Status laporan progres
    ];

    /**
     * Jenis Laporan
     */
    const JENIS_LAP_PROGRES = 'lap_progres';
    const JENIS_LAP_KEUANGAN_SEMENTARA = 'lap_keuangan_sementara';
    const JENIS_LAP = [
        self::JENIS_LAP_PROGRES => 'Laporan Progres',
        self::JENIS_LAP_KEUANGAN_SEMENTARA => 'Laporan Keuangan Sementara',
    ];

    /**
     * Status Laporan
     */
    const STATUS_LAP_BELUM_DINILAI = 'belum_dinilai';
    const STATUS_LAP_DIREVISI = 'direvisi';
    const STATUS_LAP_DISETUJUI = 'disetujui';
    const STATUS_LAP_OPTIONS = [
        self::STATUS_LAP_BELUM_DINILAI => 'Belum Dinilai',
        self::STATUS_LAP_DIREVISI => 'Direvisi',
        self::STATUS_LAP_DISETUJUI => 'Disetujui'
    ];
    const STATUS_LAPORAN_PROGRES = [ // adalah nama kolom nya
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
