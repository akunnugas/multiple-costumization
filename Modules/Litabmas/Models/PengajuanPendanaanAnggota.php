<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Biodata;

class PengajuanPendanaanAnggota extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengajuan_pendanaan_anggota';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan',
        'id_biodata',
        'apakah_ketua',
        'jenis_anggota',
        'id_dosen_eksternal',
        'apakah_undangan_diterima',
    ];

    const JENIS_DOSEN_INTERNAL = 1;
    const JENIS_DOSEN_EKSTERNAL = 2;
    const JENIS_MAHASISWA = 3;
    const JENIS_ANGGOTA = [
        1 => 'Dosen Internal',
        2 => 'Dosen Eksternal',
        3 => 'Mahasiswa',
    ];

    const STATUS_UNDANGAN_MENUNGGU = null;
    const STATUS_UNDANGAN_DITERIMA = true;
    const STATUS_UNDANGAN_DITOLAK = false;
    const STATUS_UNDANGAN = [
        self::STATUS_UNDANGAN_MENUNGGU => 'Menunggu Persetujuan',
        self::STATUS_UNDANGAN_DITERIMA => 'Diterima',
        self::STATUS_UNDANGAN_DITOLAK => 'Ditolak',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan' => ['required' => true, 'options' => PengajuanPendanaan::class],                  // Pengajuan Pendanaan
        'id_biodata' => ['required' => true, 'options' => Biodata::class],                                               // User
        'apakah_ketua' => ['type' => 'boolean'],                                                                   // Ketua
        'jenis_anggota' => ['required' => true, 'options' => self::JENIS_ANGGOTA],                                 // Jenis Anggota
        'id_dosen_eksternal' => ['options' => DosenEksternal::class],                                              // Dosen Eksternal
        'apakah_undangan_diterima' => ['type' => 'boolean'],                                                       // Apakah undangan diterima?
    ];

    // relation to pengajuan pendanaan
    public function pengajuanPendanaan()
    {
        return $this->belongsTo(PengajuanPendanaan::class, 'id_pengajuan_pendanaan');
    }
}
