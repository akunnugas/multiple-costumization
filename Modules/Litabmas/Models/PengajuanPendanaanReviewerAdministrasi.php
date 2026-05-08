<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Biodata;

class PengajuanPendanaanReviewerAdministrasi extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengajuan_pendanaan_reviewer_administrasi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan',
        'id_biodata',
        'reviewer_ke',
        'total_nilai_komposisi_reviewer',
        'total_nilai_presentasi_reviewer',
        'id_dokumen_sk',
        'status_penilaian_presentasi_proposal',
        'status_penilaian_isian_proposal',
        'rekomendasi_anggaran',
        'mata_uang',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan' => ['required' => true, 'options' => PengajuanPendanaan::class], // Pengajuan Pendanaan
        'id_biodata' => ['required' => true, 'options' => Biodata::class], // Biodata
        'reviewer_ke' => ['required' => true, 'type' => 'integer', 'min' => 1], // Reviewer ke
        'total_nilai_komposisi_reviewer' => ['type' => 'numeric'], // Total penilaian komposisi proposal
        'total_nilai_presentasi_reviewer' => ['type' => 'numeric'], // Total penilaian presentasi proposal
        'id_dokumen_sk' => ['file_type' => ['pdf'], 'max_size' => (1024 * 5)], // Dokumen SK
        'status_penilaian_presentasi' => ['maxlength' => 25, 'options' => self::STATUS_PENILAIAN_OPTIONS],
        'status_penilaian_isian_proposal' => ['maxlength' => 25, 'options' => self::STATUS_PENILAIAN_OPTIONS],
        'rekomendasi_anggaran' => ['type' => 'numeric', 'control' => 'currency'], // Rekomendasi anggaran
    ];

    /**
     * Max reviewer per pengajuan pendanaan.
     */
    const MAX_REVIEWER_ADMINISTRASI = 3;

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
     * Relasi ke penilaian reviewer isian proposal.
     *
     * @return HasMany
     */
    public function penilaianReviewerIsianProposal(): HasMany
    {
        return $this->hasMany(PenilaianReviewerIsianProposal::class, 'id_pengajuan_pendanaan_reviewer_administrasi');
    }

    /**
     * Relasi ke penilaian reviewer komposisi proposal.
     *
     * @return HasMany
     */
    public function penilaianReviewerKomposisiProposal(): HasMany
    {
        return $this->hasMany(PenilaianReviewerKomposisiProposal::class, 'id_pengajuan_pendanaan_reviewer_administrasi');
    }

    /**
     * Relasi ke penilaian reviewer presentasi proposal.
     *
     * @return HasMany
     */
    public function penilaianReviewerPresentasiProposal(): HasMany
    {
        return $this->hasMany(PenilaianReviewerPresentasiProposal::class, 'id_pengajuan_pendanaan_reviewer_administrasi');
    }
}
