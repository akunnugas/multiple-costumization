<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\DMS\Models\Dokumen;

class PengumumanPendanaan extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengumuman_pendanaan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'judul',
        'informasi',
        'id_dokumen_lampiran',
        'id_periode_pendanaan',
        'status_pengumuman',
        'waktu_dipublikasi'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'judul' => ['required' => true],
        'informasi' => ['required' => true],
        'id_dokumen_lampiran' => ['file_type' => self::FILE_TYPE_LAMPIRAN, 'max_size' => self::MAX_SIZE_FILE_LAMPIRAN],
        'id_periode_pendanaan' => ['required' => true, 'options' => PeriodePendanaan::class],
        'status_pengumuman' => ['options' => self::STATUS_OPTIONS],
        'waktu_dipublikasi' => ['required' => false, 'type' => 'date']
    ];

    /**
     * Konstanta utk id_dokumen thumbnail & petunjuk teknis.
     */
    const MAX_SIZE_FILE_LAMPIRAN = (1024 * 2);
    const FILE_TYPE_LAMPIRAN = ['jpg', 'jpeg', 'png', 'pdf'];

    /**
     * Const khusus untuk status pengumuman.
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_TERPUBLIKASI = 'terpublikasi';
    const STATUS_OPTIONS = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_TERPUBLIKASI => 'Diumumkan'
    ];
    const STATUS_BADGE = [
        self::STATUS_DRAFT => 'default',
        self::STATUS_TERPUBLIKASI => 'success'
    ];

    /**
     * Status keaktifan klaster dan untuk kebutuhan ordering.
     */
    const STATUS_KLASTER_DIBUKA = 1;
    const STATUS_KLASTER_BELUM_DIBUKA = 2;
    const STATUS_KLASTER_DITUTUP = 3;
}
