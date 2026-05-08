<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;

class HasilAkhirAudit extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.hasil_akhir_audit';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_audit_periode',
        'id_unit',
        'id_penilaian_audit',
        'nilai_iku',
        'nilai_ikt',
        'nilai_akhir',
        'nilai_akhir_auditee',
        'persentase_nilai_akhir',
        'id_spmi_peringkat',
        'id_akreditasi_peringkat',
        'id_status_peringkat',
        'persentase_nilai_target',
        'apakah_status_terakreditasi',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_audit_periode' => ['required' => true, 'options' => AuditPeriode::class], // Tahun Audit
        'id_unit' => ['required' => true, 'options' => UnitKerja::class], // Program Studi
        'id_penilaian_audit' => ['required' => true, 'options' => PenilaianAudit::class], // Penilaian
        'nilai_iku' => ['type' => 'numeric'], // Skor IKU
        'nilai_ikt' => ['type' => 'numeric'], // Skor IKT
        'nilai_akhir' => ['type' => 'numeric'], // Skor Akhir
        'nilai_akhir_auditee' => ['type' => 'numeric'], // Skor Auditee
        'persentase_nilai_akhir' => ['type' => 'numeric'], // Persentase Skor Akhir
        'persentase_nilai_target' => ['type' => 'numeric'], // Persentase Skor Target
        'apakah_status_terakreditasi' => ['required' => false, 'type' => 'boolean'], // Status Terakreditasi
        'id_spmi_peringkat' => ['options' => SpmiPeringkat::class], // Peringkat
        'id_status_peringkat' => ['options' => AkreditasiStatus::class], // Status Peringkat Akreditasi
        'id_akreditasi_peringkat' => ['options' => AkreditasiPeringkat::class], // Peringkat Akreditasi
    ];

    public function penilaianAudit()
    {
        return $this->belongsTo(PenilaianAudit::class, 'id_penilaian_audit');
    }

    public function penilaianPanduan()
    {
        return $this->penilaianAudit->penilaianPanduan();
    }
}
