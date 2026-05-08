<?php

namespace Modules\Litabmas\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class PenilaianPembimbingAktivitasPenelitian extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.penilaian_pembimbing_aktivitas_penelitian';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_pengajuan_pendanaan_pembimbing',
        'id_pengajuan_pendanaan_aktivitas_penelitian',
        'feedback_logbook',
        'id_dokumen_feedback_logbook',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_pengajuan_pendanaan_pembimbing' => ['required' => true, 'options' => PengajuanPendanaanPembimbing::class],
        'id_pengajuan_pendanaan_aktivitas_penelitian' => ['required' => true, 'options' => PengajuanPendanaanAktivitasPenelitian::class],
        'feedback_logbook' => [], // Feedback
        'id_dokumen_feedback_logbook' => ['file_type' => ['pdf'], 'max_size' => (1024 * 5)],
    ];
}
