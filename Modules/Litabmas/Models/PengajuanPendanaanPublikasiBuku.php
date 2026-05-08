<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Litabmas\Enums\StatusPublikasiEnum;

class PengajuanPendanaanPublikasiBuku extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengajuan_pendanaan_publikasi_buku';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_jenis_outcome_penelitian',
        'id_pengajuan_pendanaan',
        'judul_buku',
        'isbn',
        'penerbit_buku',
        'tahun_terbit_buku',
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
        'judul_buku' => ['required' => true, 'maxlength' => 255], // Judul artikel
        'isbn' => ['required' => true, 'maxlength' => 255], // ISBN buku
        'penerbit_buku' => ['required' => true, 'maxlength' => 255], // Penerbit
        'tahun_terbit_buku' => ['required' => true, 'type' => 'integer', 'validation' => 'date_format:Y'], // Tahun terbit
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
                'fields' => ['id_pengajuan_pendanaan', 'isbn'],
                'defaultMessage' => 'Gagal menyimpan karena duplikasi data. Data pengajuan pendanaan
                    dan ISBN sudah ada sebelumnya.'
            ]
        ];
    }
}
