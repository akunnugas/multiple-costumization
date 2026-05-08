<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\DMS\Models\Dokumen;

class SkAuditor extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.sk_auditor';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_audit_periode',
        'nomor_sk',
        'tanggal_diterbitkan',
        'tanggal_awal_berlaku',
        'tanggal_akhir_berlaku',
        'id_dokumen',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_audit_periode' => ['required' => true, 'options' => AuditPeriode::class], // Periode Audit
        'nomor_sk' => ['required' => true, 'maxlength' => 255, 'unique' => true], // Nomor Surat Keputusan
        'tanggal_diterbitkan' => ['required' => true, 'type' => 'date'], // Tanggal Surat Keputusan
        'tanggal_awal_berlaku' => ['required' => true, 'type' => 'date'], // Tanggal Awal Berlaku
        'tanggal_akhir_berlaku' => ['required' => true, 'type' => 'date', 'validation' => 'after:tanggal_awal_berlaku'], // Tanggal Akhir Berlaku
        'id_dokumen' => ['required' => false, 'file_type' => Dokumen::TYPE_DOCUMENT, 'max_size' => 5024], // ID Dokumen
    ];
}
