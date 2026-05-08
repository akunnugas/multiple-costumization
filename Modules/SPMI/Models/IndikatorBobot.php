<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;

class IndikatorBobot extends IndonesianModel
{
    const TYPE_IKU = 'IKU';
    const TYPE_IKT = 'IKT';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.indikator_bobot';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'jenis_indikator_bobot',
        'id_audit_periode',
        'nama_kategori_indikator',
        'persentase',
        'id_unit',
        'id_penilaian_panduan',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'jenis_indikator_bobot' => ['required' => true, 'maxlength' => 10], // Tipe Kategori Indikator
        'id_audit_periode' => ['required' => true, 'options' => AuditPeriode::class], // ID Periode Audit
        'nama_kategori_indikator' => ['required' => true, 'maxlength' => 255], // Nama Kategori Indikator
        'persentase' => ['required' => true], // Persentase Indikator,
        'id_unit' => ['required' => true, 'options' => UnitKerja::class], // ID Unit Kerja
    ];
}
