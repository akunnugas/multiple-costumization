<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;

class JadwalAuditUnit extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.jadwal_audit_unit';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_jadwal_audit',
        'id_unit',
        'id_pengisian_panduan',
        'id_penilaian_panduan',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_jadwal_audit' => ['required' => true, 'options' => JadwalAudit::class], //
        'id_unit' => ['required' => true, 'options' => UnitKerja::class], //
        'id_pengisian_panduan' => ['required' => true, 'options' => PengisianPanduan::class], //
        'id_penilaian_panduan' => ['required' => true, 'options' => PenilaianPanduan::class], //
    ];
}
