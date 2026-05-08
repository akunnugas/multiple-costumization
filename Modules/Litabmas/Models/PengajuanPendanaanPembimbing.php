<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\Pegawai;
use Modules\Litabmas\Services\PengajuanPendanaanService;

class PengajuanPendanaanPembimbing extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengajuan_pendanaan_pembimbing';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan',
        'id_biodata',
        'id_dokumen_sk',
        'status_bimbingan_logbook',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan' => ['required' => true, 'options' => PengajuanPendanaan::class], // Pengajuan pendanaan
        'id_biodata' => ['required' => true, 'options' => Biodata::class], // User\
        'id_dokumen_sk' => ['file_type' => ['pdf'], 'max_size' => (1024 * 5)], // Dokumen SK
        'status_bimbingan_logbook' => ['options' => self::STATUS_BIMBINGAN_LOGBOOK_OPTIONS],
    ];

    /**
     * Status Bimbingan Logbook
     */
    const BIMBINGAN_LOGBOOK_BELUM_DINILAI = null;
    const BIMBINGAN_LOGBOOK_PROSES_PENILAIAN = 'proses_penilaian';
    const BIMBINGAN_LOGBOOK_SUDAH_DINILAI = 'sudah_dinilai';
    const STATUS_BIMBINGAN_LOGBOOK_OPTIONS = [
        self::BIMBINGAN_LOGBOOK_BELUM_DINILAI => 'Belum Dinilai',
        self::BIMBINGAN_LOGBOOK_PROSES_PENILAIAN => 'Proses Penilaian',
        self::BIMBINGAN_LOGBOOK_SUDAH_DINILAI => 'Sudah Dinilai',
    ];
    const STATUS_BIMBINGAN_LOGBOOK = [ // adalah nama kolom nya
        self::BIMBINGAN_LOGBOOK_BELUM_DINILAI => [
            'value' => self::BIMBINGAN_LOGBOOK_BELUM_DINILAI,
            'text' => 'Belum Dinilai',
            'variant' => 'warning',
        ],
        self::BIMBINGAN_LOGBOOK_PROSES_PENILAIAN => [
            'value' => self::BIMBINGAN_LOGBOOK_PROSES_PENILAIAN,
            'text' => 'Proses Penilaian',
            'variant' => 'warning',
        ],
        self::BIMBINGAN_LOGBOOK_SUDAH_DINILAI => [
            'value' => self::BIMBINGAN_LOGBOOK_SUDAH_DINILAI,
            'text' => 'Sudah Dinilai',
            'variant' => 'success'
        ],
    ];

    /**
     * Max pembimbing di satu pengajuan pendanaan.
     */
    const MAX_PEMBIMBING = 1;

    /**
     * Relasi ke feedback aktivitas penelitian.
     *
     * @return HasMany
     */
    public function penilaianPembimbingAktivitasPenelitian(): HasMany
    {
        return $this->hasMany(PenilaianPembimbingAktivitasPenelitian::class, 'id_pengajuan_pendanaan_pembimbing');
    }

    /**
     * @param int $idPengajuanPendanaan
     * @return array
     */
    public static function optionDosen(int $idPengajuanPendanaan)
    {
        // dosen yang berstatus aktif
        $allOption = collect(Pegawai::optionDosenAktif('nip'));

        // hanya dosen yang belum menjadi pembimbing
        $pembimbings = PengajuanPendanaanPembimbing::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->pluck('id_biodata')
            ->toArray();
        $validateOption = $allOption
            ->reject(function ($key, $value) use ($pembimbings) {
                return in_array($value, $pembimbings);
            });

        // hanya user yg belum pernah jadi anggota di periode aktif
        $periodeAktif = (new PeriodePendanaan)->periodeAktif();
        $biodataIds = (new PengajuanPendanaanService())->getIdBiodataYangBolehMengajukanPendanaan($periodeAktif->id);

        $validateOption = $validateOption
            ->filter(function ($key, $value) use ($biodataIds) {
                return !in_array($value, $biodataIds);
            });

        return [
            'all' => $allOption->toArray(),
            'validate' => $validateOption->toArray()
        ];
    }
}
