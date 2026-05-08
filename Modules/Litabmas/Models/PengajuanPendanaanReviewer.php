<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Biodata;
use Modules\Litabmas\Models\PengajuanPendanaan;

class PengajuanPendanaanReviewer extends IndonesianModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.pengajuan_pendanaan_reviewers';

    protected $fillable = [
        'id_pengajuan_pendanaan',
        'id_biodata',
        'id_dokumen_sk',
        'reviewer_ke',
        'apakah_internal',
        'apakah_review_proposal',
        'apakah_review_luaran',
        'apakah_review_antara',
        'apakah_admin',
        'total_nilai_komposisi_reviewer',
        'total_nilai_presentasi_reviewer',
        'status_penilaian_presentasi',
        'status_penilaian_isian_proposal',
        'rekomendasi_anggaran',
        'status_penilaian_progress_report',
        'status_penilaian_output',
        'status_penilaian_output_bersama',
        'komentar_umum_reviewer_output',
        'komentar_umum_reviewer_progress_report',
    ];

    const RULES = [
        'id_pengajuan_pendanaan' => ['required' => true, 'options' => PengajuanPendanaan::class], // Pengajuan Pendanaan
        'id_biodata' => ['required' => true, 'options' => Biodata::class], // User
        'reviewer_ke' => ['required' => true, 'type' => 'integer', 'min' => 1], // Reviewer ke
        'id_dokumen_sk' => ['file_type' => ['pdf'], 'max_size' => (1024 * 5)], // Dokumen SK
        'apakah_internal' => ['type' => 'boolean'],
        'apakah_review_proposal' => ['type' => 'boolean'], // Reviewer Proposal
        'apakah_review_luaran' => ['type' => 'boolean'], // Reviewer Output
        'apakah_review_antara' => ['type' => 'boolean'], // Reviewer Progress Report
        'apakah_admin' => ['type' => 'boolean'], // Apakah Admin
        'total_nilai_komposisi_reviewer' => ['type' => 'numeric'], // Total penilaian komposisi proposal
        'total_nilai_presentasi_reviewer' => ['type' => 'numeric'], // Total penilaian presentasi proposal
        'status_penilaian_presentasi' => ['maxlength' => 25],
        'status_penilaian_isian_proposal' => ['maxlength' => 25],
        'rekomendasi_anggaran' => ['type' => 'numeric', 'control' => 'currency'], // Rekomendasi anggaran
        'status_penilaian_progress_report' => ['maxlength' => 25],
        'status_penilaian_output' => ['maxlength' => 25],
        'status_penilaian_output_bersama' => ['maxlength' => 25],
        'komentar_umum_reviewer_output' => [],
        'komentar_umum_reviewer_progress_report' => [],
    ];

    const MAX_REVIEWER = 3;

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function biodata()
    {
        return $this->belongsTo(Biodata::class);
    }
}
