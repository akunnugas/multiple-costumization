<?php

namespace Modules\SPMI\Models;

use Illuminate\Support\Facades\DB;
use Modules\Core\Models\LembagaAkreditasi;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;

class PenilaianAudit extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.penilaian_audit';

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
        'id_ketua_auditor',
        'apakah_penilaian_mandiri',
        'nilai_akhir',
        'apakah_terfinalisasi',
        'apakah_temuan_terfinalisasi',
        'total_indikator_terisi',
        'total_indikator_matriks',
        'total_temuan',
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
        'id_jadwal_audit' => ['required' => true, 'options' => JadwalAudit::class], //
        'id_ketua_auditor' => ['required' => false], // Auditor
        'apakah_penilaian_mandiri' => ['required' => true, 'type' => 'boolean'], // Self Assessment
        'nilai_akhir' => ['required' => false, 'type' => 'numeric'], // Skor Akhir
        'apakah_terfinalisasi' => ['required' => false, 'type' => 'boolean'], // Finalisasi
        'apakah_temuan_terfinalisasi' => ['required' => false, 'type' => 'boolean'], // Finalisasi Temuan
        'total_indikator_terisi' => ['required' => false, 'type' => 'numeric'], // Total Indikator Terisi
        'total_temuan' => ['required' => false, 'type' => 'numeric'], // Total Temuan
    ];

    public static function findByStudyProgramId($idPenilaianPanduan, $idUnitKerja, $auditPeriod, $isPenilaianMandiri = false, $idJadwalAudit = null)
    {
        return self::where('id_unit', $idUnitKerja)
            ->when(isset($auditPeriod), fn ($query) => $query->where('spmi.penilaian_audit.id_audit_periode', $auditPeriod))
            ->where('apakah_penilaian_mandiri', $isPenilaianMandiri)
            ->where('id_penilaian_panduan', $idPenilaianPanduan)
            ->when(!empty($idJadwalAudit), fn ($query) => $query->where('id_jadwal_audit', $idJadwalAudit))
            ->first();
    }

    public function periode()
    {
        return $this->belongsTo(AuditPeriode::class, 'id_audit_periode');
    }

    public function studyProgram()
    {
        return $this->belongsTo(UnitKerja::class, 'id_unit');
    }

    public function penilaianPanduan()
    {
        return $this->belongsTo(PenilaianPanduan::class, 'id_penilaian_panduan');
    }
}
