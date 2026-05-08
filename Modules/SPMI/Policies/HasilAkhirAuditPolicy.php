<?php

namespace Modules\SPMI\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SiakadUser;
use Modules\Core\Helpers\SessionManager;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\Gate\Models\Role;
use Modules\SPMI\Models\HasilAkhirAudit;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;

class HasilAkhirAuditPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function view(SiakadUser $user, HasilAkhirAudit $hasilAkhirAudit)
    {
        $userRole = $user->kode_role;

        $isInternalRole = SessionManager::isInternalRole();

        if ($isInternalRole) {
            return true;
        }

        if (!$isInternalRole && ($userRole === Role::ROLE_AUDITOR || $userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE)) {
            return $this->authorizeRole($user, $hasilAkhirAudit);
        }

        $userUnit = session()->get('user.unit_kerja');
        if (!empty($userUnit)) {
            return $this->authorizeUnitKerja($userUnit, $hasilAkhirAudit);
        }

        return false;
    }

    protected function authorizeRole(SiakadUser $user, HasilAkhirAudit $hasilAkhirAudit)
    {
        $userRole = $user->kode_role;
        $filterOperator = $userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE ? 'IN' : 'NOT IN';
        $sql =
            "SELECT EXISTS(
                SELECT
                    stp.id
                FROM spmi.surat_tugas_auditor_pegawai stp
                JOIN spmi.jadwal_audit_unit aso ON aso.id_unit = stp.id_unit
                JOIN spmi.jadwal_audit asch ON asch.id = aso.id_jadwal_audit
                    AND asch.id_audit_periode = :id_audit_periode
                    AND asch.apakah_audit_aktif = true
                    AND asch.waktu_dihapus is null
                JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = asch.id_audit_periode
                    AND sa.waktu_dihapus is null
                JOIN core.biodata p ON p.id_user = :id_user
                    AND p.waktu_dihapus is null
                WHERE stp.id_personil = p.id
                    AND stp.posisi {$filterOperator} (:posisi, :posisi2)
                    AND stp.id_surat_tugas_auditor = sa.id
                LIMIT 1
            )";

        $data = DB::selectOne($sql, [
            'id_audit_periode' => $hasilAkhirAudit->id_audit_periode,
            'id_user' => $user->id,
            'posisi' => SuratTugasAuditorPegawai::POSITION_AUDITEE,
            'posisi2' => SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE
        ]);

        $exists = $data?->exists;

        return $exists;
    }

    protected function authorizeUnitKerja(mixed $userUnit, HasilAkhirAudit $hasilAkhirAudit)
    {
        $unitKerjaServices = new UnitKerjaManagementService();

        $ids = array_keys($unitKerjaServices->getUnitAncestors($userUnit->id));
        if (empty($ids)) {
            return false;
        }

        $userUnit = UnitKerja::whereIn('id', $ids)
            ->pluck('id')
            ->toArray();

        return in_array($hasilAkhirAudit->id_unit, $userUnit);
    }
}
