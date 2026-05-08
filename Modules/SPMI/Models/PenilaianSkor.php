<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class PenilaianSkor extends IndonesianModel
{
    const STATUS_MENYIMPANG = 1;
    const STATUS_BELUM_MEMENUHI = 2;
    const STATUS_MEMENUHI = 3;
    const STATUS_MELAMPAUI = 4;
    const STATUS = [
        self::STATUS_MELAMPAUI => 'Melampaui',
        self::STATUS_MEMENUHI => 'Memenuhi',
        self::STATUS_BELUM_MEMENUHI => 'Belum Memenuhi',
        self::STATUS_MENYIMPANG => 'Menyimpang',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.penilaian_skor';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_penilaian_audit',
        'id_penilaian_matriks',
        'id_predikat_matriks_penilaian',
        'nilai_default',
        'nilai',
        'nilai_target',
        'max_nilai_target',
        'nilai_akhir',
        'status_penilaian',
        'catatan_penilaian',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_penilaian_audit' => ['required' => true, 'options' => PenilaianAudit::class], // Penilaian
        'id_penilaian_matriks' => ['required' => false, 'options' => PenilaianMatriks::class], // Matriks Penilaian
        'id_predikat_matriks_penilaian' => ['required' => false, 'options' => PenilaianMatriksPredikat::class], // Skor Matriks Penilaian
        'nilai_default' => ['required' => false, 'type' => 'numeric'], // Skor Default
        'nilai' => ['required' => false, 'type' => 'numeric'], // Skor
        'nilai_target' => ['required' => true, 'type' => 'numeric'], // Skor Target
        'nilai_akhir' => ['required' => false, 'type' => 'numeric'], // Skor Akhir
        'status_penilaian' => ['required' => false, 'maxlength' => 2], // Status (1: Menyimpang, 2: Belum Memeuhi, 3: Memenuhi, 4: Sangat Melampaui)
        'catatan_penilaian' => [], // Feedback
    ];
}
