<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class TinjauanTemuan extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.tinjauan_temuan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_penilaian_audit',
        'id_penilaian_matriks',
        'uraian_temuan_audit',
        'rencana_peningkatan_mutu',
        'pelaksana',
        'tanggal_peningkatan_mutu',
        'id_predikat_matriks_penilaian',
        'nilai_target_default',
        'nilai_target',
        'akar_masalah'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_penilaian_audit' => ['required' => true, 'options' => PenilaianAudit::class], // Penilaian
        'id_penilaian_matriks' => ['required' => true, 'options' => PenilaianMatriks::class], // Matriks Penilaian
        'akar_masalah' => ['required' => false], // Akar Masalah
        'rencana_peningkatan_mutu' => ['required' => false], // Rencana Perbaikan
        'pelaksana' => ['required' => true, 'maxlength' => 255], // Pelaksana
        'tanggal_peningkatan_mutu' => ['required' => true], // Tanggal Perbaikan
        'nilai_target' => ['required' => true, 'type' => 'numeric'], // Skor Akhir
        'nilai_target_default' => ['required' => true, 'type' => 'numeric'], // Skor Akhir
    ];
}
