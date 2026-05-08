<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Biodata;

class PengajuanPendanaanReviewerKegiatan extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengajuan_pendanaan_reviewer_kegiatan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan',
        'id_biodata',
        'reviewer_ke',
        'komentar_umum_penilaian_output',
        'komentar_umum_presentasi_progres',
        'id_dokumen_sk',
        'tipe_reviewer',
        'status_penilaian_progress_report',
        'status_penilaian_output',
        'status_penilaian_output_bersama',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan' => ['required' => true, 'options' => PengajuanPendanaan::class], // Pengajuan Pendanaan
        'id_biodata' => ['required' => true, 'options' => Biodata::class], // User
        'reviewer_ke' => ['required' => true, 'type' => 'integer', 'min' => 1], // Reviewer ke
        'komentar_umum_penilaian_output' => [],
        'komentar_umum_presentasi_progress' => [],
        'id_dokumen_sk' => ['file_type' => ['pdf'], 'max_size' => (1024 * 5)], // Dokumen SK
        'tipe_reviewer' => ['maxlength' => 100, 'options' => self::TIPE_REVIEWER_OPTIONS],
        'status_penilaian_progress_report' => ['maxlength' => 25, 'options' => self::STATUS_PENILAIAN_OPTIONS],
        'status_penilaian_output' => ['maxlength' => 25, 'options' => self::STATUS_PENILAIAN_OPTIONS],
        'status_penilaian_output_bersama' => ['maxlength' => 25, 'options' => self::STATUS_PENILAIAN_OPTIONS],
    ];

    /**
     * Max reviewer per pengajuan pendanaan.
     */
    const MAX_REVIEWER_KEGIATAN = 3;

    /**
     * Tipe Reviewer
     */
    const TIPE_REVIEWER_PROPOSAL = 'reviewer_proposal';
    const TIPE_REVIEWER_LUARAN = 'reviewer_luaran';
    const TIPE_REVIEWER_ANTARA = 'reviewer_antara';
    const TIPE_REVIEWER_OPTIONS = [
        self::TIPE_REVIEWER_PROPOSAL => 'Review Proposal',
        self::TIPE_REVIEWER_LUARAN => 'Review Luaran',
        self::TIPE_REVIEWER_ANTARA => 'Review Antara',
    ];

    /**
     * Status penilaian progres report constanta.
     */
    const STATUS_PENILAIAN_BELUM_DINILAI = 'belum_dinilai';
    const STATUS_PENILAIAN_PROSES_PENILAIAN = 'proses_penilaian';
    const STATUS_PENILAIAN_SUDAH_DINILAI = 'sudah_dinilai';
    const STATUS_PENILAIAN_OPTIONS = [
        self::STATUS_PENILAIAN_BELUM_DINILAI => 'Belum Dinilai',
        self::STATUS_PENILAIAN_PROSES_PENILAIAN => 'Proses Penilaian',
        self::STATUS_PENILAIAN_SUDAH_DINILAI => 'Sudah Dinilai'
    ];
    const STATUS_PENILAIAN = [ // adalah nama kolom nya
        self::STATUS_PENILAIAN_BELUM_DINILAI => [
            'value' => self::STATUS_PENILAIAN_BELUM_DINILAI,
            'text' => 'Belum Dinilai',
            'variant' => 'warning',
        ],
        self::STATUS_PENILAIAN_PROSES_PENILAIAN => [
            'value' => self::STATUS_PENILAIAN_PROSES_PENILAIAN,
            'text' => 'Proses Penilaian',
            'variant' => 'warning'
        ],
        self::STATUS_PENILAIAN_SUDAH_DINILAI => [
            'value' => self::STATUS_PENILAIAN_SUDAH_DINILAI,
            'text' => 'Sudah Dinilai',
            'variant' => 'success'
        ],
    ];

    /**
     * Relasi ke biodata.
     *
     * @return BelongsTo
     */
    public function biodata()
    {
        return $this->belongsTo(Biodata::class);
    }

    /**
     * Relasi ke penilaian reviewer laporan progres.
     *
     * @return HasMany
     */
    public function penilaianReviewerLaporanProgres(): HasMany
    {
        return $this->hasMany(PenilaianReviewerLaporanProgres::class, 'id_pengajuan_pendanaan_reviewer_kegiatan');
    }

    /**
     * Relasi ke penilaian reviewer output.
     *
     * @return HasMany
     */
    public function penilaianReviewerOuput(): HasMany
    {
        return $this->hasMany(PenilaianReviewerOutput::class, 'id_pengajuan_pendanaan_reviewer_kegiatan');
    }

    /**
     * Relasi ke penilaian reviewer output bersama pembuat.
     *
     * @return HasMany
     */
    public function penilaianReviewerOuputBersamaPembuat(): HasMany
    {
        return $this->hasMany(PenilaianReviewerOutputBersama::class, 'id_pengajuan_pendanaan_reviewer_pembuat');
    }

    /**
     * Relasi ke penilaian reviewer output bersama pengubah.
     *
     * @return HasMany
     */
    public function penilaianReviewerOuputBersamaPengubah(): HasMany
    {
        return $this->hasMany(PenilaianReviewerOutputBersama::class, 'id_pengajuan_pendanaan_reviewer_pengubah');
    }
}
