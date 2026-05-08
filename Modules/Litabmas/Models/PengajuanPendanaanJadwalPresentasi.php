<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class PengajuanPendanaanJadwalPresentasi extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengajuan_pendanaan_jadwal_presentasi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan',
        'nama_kegiatan',
        'tipe_kegiatan',
        'waktu_pelaksanaan',
        'tempat_pelaksanaan',
        'link_presentasi_kegiatan',
        'tipe_presentasi',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan' => ['required' => true, 'options' => PengajuanPendanaan::class],                     // Pengajuan pendanaan
        'nama_kegiatan' => ['required' => true, 'maxlength' => 255],                                                  // Nama kegiatan
        'tipe_kegiatan' => ['required' => true, 'maxlength' => 25, 'options' => self::TIPE_KEGIATAN_OPTIONS],                                                   // Tipe kegiatan
        'waktu_pelaksanaan' => ['required' => true, 'type' => 'timestamp'], // Waktu pelaksanaan
        'tempat_pelaksanaan' => ['maxlength' => 255],                                                                 // Tempat pelaksanaan
        'link_presentasi_kegiatan' => ['maxlength' => 255, 'validation' => 'url'],                                                           // Link presentasi kegiatan
        'tipe_presentasi' => ['required' => true, 'maxlength' => 100, 'options' => self::TIPE_PRESENTASI_OPTIONS]
    ];

    /**
     * Tipe Kegiatan
     */
    const TIPE_KEGIATAN_OFFLINE = 'offline';
    const TIPE_KEGIATAN_ONLINE = 'online';
    const TIPE_KEGIATAN_OPTIONS = [
        self::TIPE_KEGIATAN_OFFLINE => 'Offline',
        self::TIPE_KEGIATAN_ONLINE => 'Online'
    ];

    /**
     * Tipe Presentasi
     */
    const TIPE_PRESENTASI_PROPOSAL = AgendaKegiatan::STEP_PENILAIAN_HASIL_PRESENTASI;
    const TIPE_PRESENTASI_LAPORAN_ANTARA = AgendaKegiatan::STEP_PENILAIAN_LAPORAN_ANTARA;
    const TIPE_PRESENTASI_LUARAN = AgendaKegiatan::STEP_PENILAIAN_LUARAN;
    const TIPE_PRESENTASI_OPTIONS = [
        self::TIPE_PRESENTASI_PROPOSAL => 'Presentasi Proposal',
        self::TIPE_PRESENTASI_LAPORAN_ANTARA => 'Presentasi Laporan Antara',
        self::TIPE_PRESENTASI_LUARAN => 'Presentasi Luaran',
    ];

    /**
     * Utk validasi unique composite.
     * @return array[]
     */
    protected static function uniqueColumns(): array
    {
        return [
            'uniquePendanaanTipePresentasi' => [
                'defaultMessage' => __('validation.unique', [
                    'attribute' => __('litabmas::pengajuan_pendanaan_jadwal_presentasi.tipe_presentasi')
                ]),
                'fields' => ['id_pengajuan_pendanaan', 'tipe_presentasi']
            ],
        ];
    }
}
