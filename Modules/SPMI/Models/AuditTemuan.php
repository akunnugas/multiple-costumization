<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class AuditTemuan extends IndonesianModel
{
    const TYPE_OBSERVATION = 1;
    const TYPE_KTS_MINOR = 2;
    const TYPE_KTS_MAJOR = 3;
    const TYPES = [
        self::TYPE_OBSERVATION => 'Observasi',
        self::TYPE_KTS_MINOR => 'KTS Minor',
        self::TYPE_KTS_MAJOR => 'KTS Mayor',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.audit_temuan';

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
        'jenis_temuan',
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
        'uraian_temuan_audit' => ['required' => false], // Temuan
        'rencana_peningkatan_mutu' => ['required' => false], // Rencana Perbaikan
        'pelaksana' => ['required' => true, 'maxlength' => 255], // Pelaksana
        'tanggal_peningkatan_mutu' => ['required' => true], // Tanggal Perbaikan
        'jenis_temuan' => ['required' => true, 'maxlength' => 2], // Jenis Temuan (1: Observasi, 2: KeTidakSesuaian(KTS) Minor, 3: KeTidakSesuaian(KTS) Mayor)
        'akar_masalah' => ['required' => false], // Akar Masalah
    ];
}
