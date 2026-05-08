<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;

class MappingPenilaianMatriks extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.mapping_penilaian_matriks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_penilaian_matriks',
        'id_audit_periode',
        'id_unit',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_penilaian_matriks' => ['required' => true, 'options' => PenilaianMatriks::class],
        'id_audit_periode' => ['required' => true, 'options' => AuditPeriode::class],
        'id_unit' => ['required' => true, 'options' => UnitKerja::class],
    ];

    public function penilaianMatriks()
    {
        return $this->belongsTo(PenilaianMatriks::class, 'id_penilaian_matriks');
    }
}
