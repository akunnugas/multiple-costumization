<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\LembagaAkreditasi;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;

class TargetIndikator extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.target_indikator';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_audit_periode',
        'id_lembaga_akreditasi',
        'id_penilaian_panduan',
        'id_unit',
        'id_jadwal_audit',
        'total_matriks',
        'apakah_terfinalisasi'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_audit_periode' => ['required' => true, 'options' => AuditPeriode::class], // Tahun Audit
        'id_lembaga_akreditasi' => ['required' => true, 'options' => LembagaAkreditasi::class], // Lembaga Akreditasi
        'id_penilaian_panduan' => ['required' => true, 'options' => PenilaianPanduan::class], // Panduan Penilaian
        'id_unit' => ['required' => true, 'options' => UnitKerja::class], // Program Studi
        'total_matriks' => ['required' => false], // Total Matriks,
        'apakah_terfinalisasi' => ['required' => false] // Finalisasi
    ];

    public static function findByStudyProgramId($studyProgramId, $auditPeriodId, $idPanduanPenilaian, $idJadwalAudit = null)
    {
        return self::where('id_unit', $studyProgramId)
            ->where('id_audit_periode', $auditPeriodId)
            ->where('id_penilaian_panduan', $idPanduanPenilaian)
            ->when($idJadwalAudit, fn ($q) => $q->where('id_jadwal_audit', $idJadwalAudit))
            ->first();
    }
}
