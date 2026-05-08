<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Litabmas\Enums\StatusPublikasiEnum;

class PengajuanPendanaanPublikasiArtikel extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengajuan_pendanaan_publikasi_artikel';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_jenis_outcome_penelitian',
        'id_pengajuan_pendanaan',
        'judul_artikel',
        'situs_publikasi_jurnal',
        'volume_dan_nomor_terbitan',
        'url_artikel',
        'status_publikasi'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_jenis_outcome_penelitian' => ['required' => true, 'options' => JenisOutcomePenelitian::class],
        'id_pengajuan_pendanaan' => ['required' => true, 'options' => PengajuanPendanaan::class], // Pengajuan pendanaan
        'judul_artikel' => ['required' => true, 'maxlength' => 255], // Judul artikel
        'situs_publikasi_jurnal' => ['required' => true, 'maxlength' => 255], // Platform publikasi penelitian
        'volume_dan_nomor_terbitan' => ['required' => true, 'maxlength' => 255], // Volume & nomor terbitan
        'url_artikel' => ['required' => true, 'maxlength' => 255, 'validation' => 'url'], // URL artikel
        'status_publikasi' => ['maxlength' => 25, StatusPublikasiEnum::STATUS_PUBLIKASI_OPTIONS] // Status
    ];

    /**
     * Utk validasi unique composite.
     * @return array[]
     */
    protected static function uniqueColumns(): array
    {
        return [
            'firstUnique' => [
                'fields' => ['id_pengajuan_pendanaan', 'url_artikel'],
                'defaultMessage' => 'Gagal menyimpan karena duplikasi data. Data pengajuan pendanaan & URL artikel sudah ada sebelumnya.'
            ]
        ];
    }
}
