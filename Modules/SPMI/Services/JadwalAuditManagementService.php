<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Helpers\SessionManager;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\Service;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\Gate\Models\Role;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Models\JadwalAuditUnit;
use Modules\SPMI\Models\PengisianIndikator;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;
use Modules\SPMI\Models\TargetIndikator;

class JadwalAuditManagementService extends Service
{
    /**
     * @var JadwalAudit
     */
    protected $model = JadwalAudit::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new JadwalAudit;
    }

    /**
     * Menampilkan list data
     *
     * @param int $page
     * @param int $perPage
     * @param array $order
     * @param array $filter
     *
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $table = $this->model->getTable();
        $sql = "SELECT
                    asch.id,
                    ap.tahun_audit periode_audit,
                    asch.nama_jadwal_audit,
                    (ARRAY_AGG(o.nama_unit_lengkap)) unit,
                    ototal.total total_unit,
                    asch.tanggal_awal_pengisian::text tanggal_awal_pengisian,
                    asch.tanggal_akhir_pengisian::text tanggal_akhir_pengisian,
                    asch.tanggal_awal_penilaian::text tanggal_awal_penilaian,
                    asch.tanggal_akhir_penilaian::text tanggal_akhir_penilaian,
                    asch.apakah_audit_aktif
                FROM $table asch
                LEFT JOIN spmi.audit_periode ap ON ap.id = asch.id_audit_periode
                    AND ap.waktu_dihapus IS NULL
                LEFT JOIN spmi.jadwal_audit_unit aso ON aso.id_jadwal_audit = asch.id
                JOIN (
                    SELECT o.*, CASE
                        WHEN d.kode_jenjang IS NULL THEN o.nama_unit
                        ELSE CONCAT(d.kode_jenjang, ' - ', o.nama_unit)
                    END AS nama_unit_lengkap  FROM core.unit_kerja o
                    LEFT JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
                    WHERE o." . UnitKerja::DELETED_AT . " IS NULL
                ) o ON o.id = aso.id_unit
                LEFT JOIN (
                    SELECT aso.id_jadwal_audit, count(1) total FROM spmi.jadwal_audit_unit aso
                    GROUP BY aso.id_jadwal_audit
                ) ototal ON ototal.id_jadwal_audit = asch.id";

        $fieldMap = [
            'id_unit_kerja' => 'o.id',
            'unit' => 'o.nama_unit',
            'tanggal_pengisian' => 'asch.tanggal_awal_pengisian::text',
            'tanggal_penilaian' => 'asch.tanggal_awal_penilaian::text',
            'tanggal_awal_pengisian' => 'asch.tanggal_awal_pengisian::text',
            'tanggal_akhir_pengisian' => 'asch.tanggal_akhir_pengisian::text',
            'tanggal_awal_penilaian' => 'asch.tanggal_awal_penilaian::text',
            'tanggal_akhir_penilaian' => 'asch.tanggal_akhir_penilaian::text',
            'periode_audit' => 'ap.tahun_audit',
            'apakah_audit_aktif' => "CASE WHEN asch.apakah_audit_aktif = true THEN 'Aktif' ELSE 'Tidak Aktif' END",
        ];

        $defaultFilter = "asch.waktu_dihapus is null";

        $bindings = [];
        $userRole = auth()->user()->kode_role;
        if (($userRole === Role::ROLE_AUDITOR || $userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE)) {
            $filterOperator = $userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE ? 'IN' : 'NOT IN';
            $defaultFilter .=
                " AND (
                    SELECT
                        stp.id
                    FROM spmi.surat_tugas_auditor_pegawai stp
                    JOIN spmi.jadwal_audit asch ON asch.id = aso.id_jadwal_audit
                        AND asch.id_audit_periode = ap.id
                        AND asch.apakah_audit_aktif = true
                        AND asch.waktu_dihapus is null
                    JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = asch.id_audit_periode
                        AND sa.waktu_dihapus is null
                    JOIN core.biodata p ON p.id_user = ?
                        AND p.waktu_dihapus is null
                    WHERE stp.id_unit = o.id
                        AND stp.id_personil = p.id
                        AND stp.posisi {$filterOperator} (?,?)
                        AND stp.id_surat_tugas_auditor = sa.id
                    LIMIT 1
                ) IS NOT NULL";

            $bindings[] = auth()->id();
            $bindings[] = SuratTugasAuditorPegawai::POSITION_AUDITEE;
            $bindings[] = SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE;
        }

        // Filter tambahan untuk cek hak akses unit
        $isInternalRole = SessionManager::isInternalRole();
        $userUnit = session()->get('user.unit_kerja');
        if (!empty($userUnit) && ($userRole !== Role::ROLE_AUDITOR && $userRole !== Role::ROLE_KAPRODI && $userRole !== Role::ROLE_AUDITEE) && !$isInternalRole) {
            $unitKerjaServices = new UnitKerjaManagementService();
            $ids = array_keys($unitKerjaServices->getUnitAncestors($userUnit->id));
            if (!empty($ids)) {
                $defaultFilter .= " AND o.id IN (" . implode(',', $ids) . ")";
            } else {
                $defaultFilter .= " AND o.id = 0"; // pastikan tidak ada data yang ditampilkan
            }
        }

        [$sql, $bindings] = Pagination::buildQuery(
            bindings: $bindings,
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
            groupBy: "asch.id, ap.tahun_audit, asch.nama_jadwal_audit, ototal.total"
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return JadwalAudit
     */
    public function show(int $id): JadwalAudit
    {
        $data = $this->model->findOrFail($id);

        // get program studi
        $data->program_studi = implode('::::', JadwalAuditUnit::where('id_jadwal_audit', $id)->get()->map(function ($item) {
            $unit = UnitKerja::withTrashed()->find($item->id_unit);
            $jenjang = JenjangPendidikan::find($unit->id_jenjang_pendidikan);
            return $jenjang ? $jenjang->kode_jenjang . ' - ' . $unit->nama_unit : $unit->nama_unit;
        })->toArray());

        $auditPeriode = AuditPeriode::find($data->id_audit_periode);
        if (!empty($auditPeriode)) {
            $data->periode_akademik = ($auditPeriode->tahun_audit - 1) . '/' . $auditPeriode->tahun_audit;
        }

        return $data;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return JadwalAudit|Error
     */
    public function store(array $data): JadwalAudit|Error
    {
        DB::beginTransaction();

        $listPengisianUnit = $data['selectedPengisian'] ?? [];
        $listPenilaianUnit = $data['selectedPenilaian'] ?? [];
        unset($data['selectedPengisian'], $data['selectedPenilaian']);

        $listPengisianUnit = array_filter($listPengisianUnit, function ($value) {
            return !empty($value);
        });

        $listPenilaianUnit = array_filter($listPenilaianUnit, function ($key) use ($listPengisianUnit) {
            return isset($listPengisianUnit[$key]);
        }, ARRAY_FILTER_USE_KEY);



        try {
            $model = $this->model->create($data);

            foreach ($listPengisianUnit as $idUnit => $idPengisian) {
                JadwalAuditUnit::create([
                    'id_jadwal_audit' => $model->id,
                    'id_unit' => $idUnit,
                    'id_pengisian_panduan' => $idPengisian,
                    'id_penilaian_panduan' => $listPenilaianUnit[$idUnit],
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        // Validasi periode audit
        $validPeriod = $this->validateValidPeriod($model);

        if (Error::isError($validPeriod)) {
            DB::rollBack();
            return $validPeriod;
        }

        DB::commit();

        return $model;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return JadwalAudit|Error
     */
    public function update(array $data, int $id): JadwalAudit|Error
    {
        $model = $this->model->findOrFail($id);

        DB::beginTransaction();

        $listPengisianUnit = $data['selectedPengisian'] ?? [];
        $listPenilaianUnit = $data['selectedPenilaian'] ?? [];
        unset($data['selectedPengisian'], $data['selectedPenilaian']);

        $listPengisianUnit = array_filter($listPengisianUnit, function ($value) {
            return !empty($value);
        });

        $listPenilaianUnit = array_filter($listPenilaianUnit, function ($key) use ($listPengisianUnit) {
            return isset($listPengisianUnit[$key]);
        }, ARRAY_FILTER_USE_KEY);



        $model = $this->model->findOrFail($id);

        foreach ($listPengisianUnit as $idUnit => $idPengisian) {
            JadwalAuditUnit::updateOrCreate(
                [
                    'id_jadwal_audit' => $model->id,
                    'id_unit' => $idUnit,
                ],
                [
                    'id_pengisian_panduan' => $idPengisian,
                    'id_penilaian_panduan' => $listPenilaianUnit[$idUnit],
                ]
            );
        }

        // hapus unit yang tidak dipilih
        JadwalAuditUnit::where('id_jadwal_audit', $model->id)
            ->whereNotIn('id_unit', array_keys($listPengisianUnit))
            ->delete();

        try {
            $model->update($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        // Validasi periode audit
        $validPeriod = $this->validateValidPeriod($model);
        if (Error::isError($validPeriod)) {
            DB::rollBack();
            return $validPeriod;
        }

        DB::commit();

        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return void
     */
    public function destroy(int $id)
    {
        DB::beginTransaction();

        try {
            $model = $this->model->findOrFail($id);

            $idunitprodis = JadwalAuditUnit::where('id_jadwal_audit', $model->id)->pluck('id_unit')->toArray();

            $listIndikator = DB::table('spmi.pengisian_indikator')
                ->where('id_audit_periode', $model->id_audit_periode)
                ->whereIn('id_unit', $idunitprodis)
                ->get();

            foreach ($listIndikator as $indikator) {
                $dataPengisianLK = DB::table('spmi.data_pengisian_lk')->where('id_pengisian_indikator', $indikator->id)->whereNull('waktu_dihapus')->first();
                if (!$dataPengisianLK) {
                    DB::table('spmi.pengisian_indikator')->where('id', $indikator->id)->delete();
                }
            }

            $isPenilaian = PenilaianAudit::where('id_audit_periode', $model->id_audit_periode)->whereIn('id_unit', $idunitprodis)->exists();
            $isTarget = TargetIndikator::where('id_audit_periode', $model->id_audit_periode)->whereIn('id_unit', $idunitprodis)->exists();
            $isPengisian = PengisianIndikator::where('id_audit_periode', $model->id_audit_periode)->whereIn('id_unit', $idunitprodis)->exists();

            if ($isPenilaian || $isPengisian || $isTarget) {
                DB::rollBack();
                $strMsg = "Penghapusan jadwal audit gagal, data masih dijadikan referensi di ";
                $strArr = [$isPenilaian ? 'penilaian audit' : null, $isPengisian ? 'pengisian indikator' : null, $isTarget ? 'target indikator' : null];
                $strMsg .= implode(', ', array_filter($strArr)) . '.';
                return new Error($strMsg);
            }

            $model->destroy($model->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();
    }

    /**
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return Error|null
     */
    public function destroySome($ids)
    {
        DB::beginTransaction();

        try {
            foreach ($ids as $id) {
                $model = $this->model->findOrFail($id);

                $idunitprodis = JadwalAuditUnit::where('id_jadwal_audit', $model->id)->pluck('id_unit')->toArray();

                $listIndikator = DB::table('spmi.pengisian_indikator')
                    ->where('id_audit_periode', $model->id_audit_periode)
                    ->whereIn('id_unit', $idunitprodis)
                    ->get();

                foreach ($listIndikator as $indikator) {
                    $dataPengisianLK = DB::table('spmi.data_pengisian_lk')->where('id_pengisian_indikator', $indikator->id)->whereNull('waktu_dihapus')->first();
                    if (!$dataPengisianLK) {
                        DB::table('spmi.pengisian_indikator')->where('id', $indikator->id)->delete();
                    }
                }

                $isPenilaian = PenilaianAudit::where('id_audit_periode', $model->id_audit_periode)->whereIn('id_unit', $idunitprodis)->exists();

                $isPengisian = PengisianIndikator::where('id_audit_periode', $model->id_audit_periode)->whereIn('id_unit', $idunitprodis)->exists();

                if ($isPenilaian || $isPengisian) {
                    DB::rollBack();
                    return new Error('Penghapusan jadwal audit gagal, data masih dijadikan referensi.');
                }

                $model->destroy($model->id);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        DB::commit();
    }



    public function validateValidPeriod(JadwalAudit $auditSchedule)
    {
        $auditPeriod = $auditSchedule->periode;
        $auditPeriodStart = $auditPeriod->tanggal_mulai;
        $auditPeriodEnd = $auditPeriod->tanggal_selesai;

        if (empty($auditPeriodStart) || empty($auditPeriodEnd)) {
            return new Error('Tanggal mulai dan selesai periode belum diatur.');
        }

        $auditScheduleFillingStart = $auditSchedule->tanggal_awal_pengisian;
        $auditScheduleFillingEnd = $auditSchedule->tanggal_akhir_pengisian;
        $auditScheduleAssessmentStart = $auditSchedule->tanggal_awal_penilaian;
        $auditScheduleAssessmentEnd = $auditSchedule->tanggal_akhir_penilaian;

        if ($auditScheduleFillingStart < $auditPeriodStart) {
            throw ValidationException::withMessages(['tanggal_awal_pengisian' => 'Tanggal awal pengisian tidak boleh kurang dari tanggal mulai periode.']);
        }

        if ($auditScheduleFillingEnd > $auditPeriodEnd) {
            throw ValidationException::withMessages(['tanggal_akhir_pengisian' => 'Tanggal akhir pengisian tidak boleh lebih dari tanggal selesai periode.']);
        }

        if ($auditScheduleAssessmentStart < $auditPeriodStart) {
            throw ValidationException::withMessages(['tanggal_awal_penilaian' => 'Tanggal awal penilaian tidak boleh kurang dari tanggal mulai periode.']);
        }

        if ($auditScheduleAssessmentEnd > $auditPeriodEnd) {
            throw ValidationException::withMessages(['tanggal_akhir_penilaian' => 'Tanggal akhir penilaian tidak boleh lebih dari tanggal selesai periode.']);
        }

        return null;
    }
}
