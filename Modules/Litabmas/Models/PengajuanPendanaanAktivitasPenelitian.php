<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class PengajuanPendanaanAktivitasPenelitian extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengajuan_pendanaan_aktivitas_penelitian';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan',
        'jenis_aktivitas',
        'nama_aktivitas_penelitian',
        'tanggal_aktivitas_penelitian',
        'lokasi_aktivitas_penelitian',
        'id_dokumen_logbook',
    ];

    /**
     * Ekstensi file yang diizinkan untuk dokumen logbook.
     */
    const ALLOWED_EXTENSION = [
        'pdf', 'jpg', 'png', 'jpeg'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan' => ['required' => true, 'options' => PengajuanPendanaan::class], // Pengajuan pendanaan
        'jenis_aktivitas' => ['required' => true], // Jenis aktivitas
        'nama_aktivitas_penelitian' => ['required' => true, 'maxlength' => 255], // Nama aktivitas penelitian
        'tanggal_aktivitas_penelitian' => ['required' => true, 'type' => 'date'], // Tanggal aktivitas penelitian
        'lokasi_aktivitas_penelitian' => ['required' => true, 'maxlength' => 255], // Lokasi aktivitas penelitian
        'id_dokumen_logbook' => ['required' => true, 'file_type' => self::ALLOWED_EXTENSION, 'max_size' => (1024 * 2)], // Dokumen logbook
    ];
}
