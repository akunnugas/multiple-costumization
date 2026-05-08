<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class SpmiPeringkat extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.spmi_peringkat';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_audit_periode',
        'kode_spmi_peringkat',
        'nama_spmi_peringkat',
        'skor_minimal',
        'skor_maksimal',
        'deskripsi',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_audit_periode' => ['required' => true, 'options' => AuditPeriode::class], // Periode Audit
        'kode_spmi_peringkat' => ['required' => true, 'maxlength' => 10, 'unique' => true], // Kode Peringkat
        'nama_spmi_peringkat' => ['required' => true, 'maxlength' => 255], // Nama Peringkat Mutu
        'skor_minimal' => ['required' => true], // Nilai Minimal
        'skor_maksimal' => ['required' => true], // Nilai Maksimal
        'deskripsi' => [], // Keterangan
    ];
}
