<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;

class MappingLED extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.mapping_led';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_indikator_evaluasi_diri',
        'id_audit_periode',
        'id_unit',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_indikator_evaluasi_diri' => ['required' => true, 'options' => IndikatorEvaluasiDiri::class],
        'id_audit_periode' => ['required' => true, 'options' => AuditPeriode::class],
        'id_unit' => ['required' => true, 'options' => UnitKerja::class],
    ];
}
