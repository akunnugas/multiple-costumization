<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\JadwalAudit;

class DokumenHasilAudit extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.dokumen_hasil_audit';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_unit',
        'id_dokumen',
        'id_audit_periode',
        'id_jadwal_audit',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_unit' => ['required' => true, 'options' => UnitKerja::class], // Perguruan Tinggi
        'id_dokumen' => ['required' => true, 'file_type' => ['pdf'], 'max_size' => 5024], // Dokumen
        'id_audit_periode' => ['required' => true, 'options' => AuditPeriode::class], // Periode Audit
        'id_jadwal_audit' => ['required' => true, 'options' => JadwalAudit::class], // Jadwal Audit
    ];
}
