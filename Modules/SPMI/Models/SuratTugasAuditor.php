<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\DMS\Models\Dokumen;

class SuratTugasAuditor extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.surat_tugas_auditor';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_audit_periode',
        'nomor_surat_tugas',
        'tanggal_surat_tugas',
        'tanggal_mulai',
        'tanggal_selesai',
        'id_dokumen',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_audit_periode' => ['required' => true, 'options' => AuditPeriode::class], // Periode Audit
        'nomor_surat_tugas' => ['required' => true, 'maxlength' => 255, 'unique' => true], // Nomor Surat Tugas
        'tanggal_surat_tugas' => ['required' => true, 'type' => 'date'], // Tanggal Surat Tugas
        'tanggal_mulai' => ['required' => true, 'type' => 'date'], // Tanggal Awal Berlaku
        'tanggal_selesai' => ['required' => true, 'type' => 'date', 'validation' => 'after:tanggal_mulai'], // Tanggal Akhir Berlaku
        'id_dokumen' => ['required' => false, 'file_type' => Dokumen::TYPE_DOCUMENT, 'max_size' => 5024], // ID Dokumen
    ];

    public static function getAuditorByPeriodAndUnit($periodId, $idUnit)
    {
        $sql = "SELECT 
                    stp.posisi, 
                    p.* 
                FROM spmi.surat_tugas_auditor sa
                JOIN spmi.surat_tugas_auditor_pegawai stp on sa.id = stp.id_surat_tugas_auditor
                JOIN core.biodata p on stp.id_personil = p.id
                    AND p.waktu_dihapus is null
                WHERE sa.id_audit_periode = ? 
                    AND stp.id_unit = ?";

        $result = DB::select($sql, [$periodId, $idUnit]);
        if (!$result) {
            return [];
        }

        return $result;
    }
}
