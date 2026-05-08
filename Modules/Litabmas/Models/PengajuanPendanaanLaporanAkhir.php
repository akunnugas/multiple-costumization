<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\DMS\Models\Dokumen;

class PengajuanPendanaanLaporanAkhir extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengajuan_pendanaan_laporan_akhir';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan',
        'jenis_laporan_akhir',
        'id_dokumen_laporan_akhir',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan' => ['required' => true, 'options' => PengajuanPendanaan::class], // Pengajuan pendanaan
        'jenis_laporan_akhir' => ['required' => true, 'maxlength' => 25, 'options' => self::JENIS_LAPORAN_OPTIONS], // Jenis laporan akhir
        'id_dokumen_laporan_akhir' => ['file_type' => self::FILE_TYPE, 'max_size' => self::MAX_SIZE_FILE], // Dokumen laporan akhir
    ];

    /**
     * Jenis Laporan Akhir
     */
    const JENIS_LAPORAN_PENELITIAN = 'penelitian';
    const JENIS_LAPORAN_KEUANGAN = 'keuangan';
    const JENIS_LAPORAN_OPTIONS = [
        self::JENIS_LAPORAN_PENELITIAN => 'Laporan Akhir Penelitian',
        self::JENIS_LAPORAN_KEUANGAN => 'Laporan Akhir Keuangan',
    ];

    /**
     * Konstanta utk id_dokumen_laporan_akhir
     */
    const MAX_SIZE_FILE = (1024 * 2);
    const FILE_TYPE = ['pdf', 'png', 'jpg', 'jpeg'];
}
