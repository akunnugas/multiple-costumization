<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Helpers\SessionManager;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\Gate\Models\Role;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\MappingPenilaianMatriks;
use Modules\SPMI\Models\TinjauanTemuan;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\PenilaianSkor;
use Modules\SPMI\Models\SuratTugasAuditor;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;
use Modules\SPMI\Models\TargetIndikator;
use Modules\SPMI\Models\TargetSkor;

class TinjauanTemuanManagementService
{
    /**
     * @var TinjauanTemuan
     */
    protected $model = TinjauanTemuan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new TinjauanTemuan;
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
        // filter berdasarkan tahun audit
        $auditPeriod = array_filter($filter, function ($item, $key) use (&$filter) {
            if (isset($item['field']) && ($item['field'] === 'id_audit_periode')) {
                unset($filter[$key]);
                return true;
            }
        }, ARRAY_FILTER_USE_BOTH);
        $auditPeriodId = array_values($auditPeriod)[0]['value'] ?? null;

        $sql = "SELECT
                    ap.tahun_audit periode_audit,
                    asch.nama_jadwal_audit,
                    pp.nama_singkat as penilaian_panduan,
                    COALESCE(ass.id, o.id) id,
                    CASE WHEN jp.id IS NOT NULL THEN concat(jp.kode_jenjang, ' - ', o.nama_unit) ELSE o.nama_unit END nama_unit,
                    CONCAT(lp.gelar_depan, ' ', lp.nama, ' ', lp.gelar_belakang) ketua_auditor,
                    COALESCE(ass.total_temuan, 0) total_temuan,
                    COALESCE(t.total_tinjauan_terisi, 0) total_tinjauan_terisi
                FROM core.unit_kerja o
                JOIN core.jenjang_pendidikan jp ON o.id_jenjang_pendidikan = jp.id
                JOIN spmi.audit_periode ap ON ap.id = ? AND ap.waktu_dihapus is null
                JOIN spmi.penilaian_audit ass ON ass.id_unit = o.id
                    AND ass.id_audit_periode = ap.id
                    AND ass.apakah_penilaian_mandiri = false
                    AND ass.waktu_dihapus is null
                JOIN spmi.jadwal_audit_unit aso ON aso.id_unit = o.id
                JOIN spmi.jadwal_audit asch ON asch.id = aso.id_jadwal_audit
                    AND asch.id_audit_periode = ap.id
                    AND asch.apakah_audit_aktif = true
                    AND asch.waktu_dihapus is null
                    AND ass.id_penilaian_panduan = aso.id_penilaian_panduan
                    AND asch.id = ass.id_jadwal_audit
                JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = asch.id_audit_periode
                    AND sa.waktu_dihapus is null
                JOIN spmi.penilaian_panduan pp ON pp.id = ass.id_penilaian_panduan
                JOIN (
                    spmi.surat_tugas_auditor_pegawai stlp
                    JOIN core.biodata lp ON lp.id = stlp.id_personil
                        AND lp.waktu_dihapus is null
                ) ON stlp.id_unit = o.id
                    AND stlp.posisi = ?
                    AND stlp.id_surat_tugas_auditor = sa.id
                    AND stlp.waktu_dihapus is null
                LEFT JOIN (
                    SELECT
                        pa.id,
                        count(1) total_tinjauan_terisi
                    FROM spmi.tinjauan_temuan tt
                    JOIN spmi.penilaian_audit pa ON pa.id = tt.id_penilaian_audit
                        AND pa.waktu_dihapus is null
                    WHERE tt.waktu_dihapus is null
                    GROUP BY pa.id
                ) t ON t.id = ass.id";

        $fieldMap = [
            'periode_audit' => 'ap.tahun_audit',
            'id_audit_periode' => 'ass.id_audit_periode',
            'id_unit_kerja' => 'o.id',
            'status_temuan' => 'total_tinjauan_terisi'
        ];

        $typeStudyProgram = [UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI];
        $typeStudyProgram = implode(',', $typeStudyProgram);
        $typeStudyProgram = "'" . str_replace(',', "','", $typeStudyProgram) . "'";

        $defaultFilter = "o.waktu_dihapus is null AND o.jenis_unit IN ($typeStudyProgram) AND COALESCE(ass.total_temuan, 0) > 0";

        $bindings = [(int) $auditPeriodId, SuratTugasAuditorPegawai::POSITION_LEAD];
        $userRole = auth()->user()->kode_role;
        $isInternalRole = SessionManager::isInternalRole();
        $isValidatedRole = $userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE || $userRole === Role::ROLE_AUDITOR;
        $checkPosition = ($userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE)
            ? 'AND stp.posisi IN (?,?)'
            : 'AND stp.posisi NOT IN (?,?)';

        $checkValidAccess =
            "AND (
                SELECT
                    stp.id
                FROM spmi.surat_tugas_auditor_pegawai stp
                JOIN core.biodata p ON p.id = stp.id_personil AND p.waktu_dihapus is null
                WHERE stp.id_unit = o.id
                    AND p.ref_key_pegawai = ?
                    {$checkPosition}
                    AND stp.id_surat_tugas_auditor = sa.id
                LIMIT 1
            ) IS NOT NULL";

        $userRole = auth()->user()->kode_role;

        if ($isValidatedRole && !$isInternalRole) {
            $defaultFilter .= " $checkValidAccess";

            $bindings[] = session('token.idpegawai');
            $bindings[] = SuratTugasAuditorPegawai::POSITION_AUDITEE;
            $bindings[] = SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE;
        }

        $userUnit = session()->get('user.unit_kerja');
        if (!empty($userUnit) && ($userRole !== Role::ROLE_KAPRODI && $userRole !== Role::ROLE_AUDITOR && $userRole !== Role::ROLE_AUDITEE) && !$isInternalRole) {
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
            // groupBy: 'o.id, ap.id, aa.id, ag.id, ass.id',
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    public function showInformationByStudyProgram(int $studyProgramId, array|null $yearData, int|null $idJadwalAudit = null): array|Error
    {
        if (!isset($yearData)) {
            return new Error('Periode audit tidak ditemukan', 404);
        }

        $sql = "SELECT
                    o.id id_unit,
                    concat(d.kode_jenjang, ' - ', o.nama_unit) as nama_unit,
                    aa.nama_singkat_lembaga,
                    ag.nama_penilaian_panduan,
                    ag.id id_penilaian_panduan,
                    lp.nama nama_ketua_auditor,
                    lp.gelar_depan gelar_depan_ketua_auditor,
                    sa.id id_surat_tugas_auditor,
                    asch.nama_jadwal_audit,
                    lp.gelar_belakang gelar_belakang_ketua_auditor
                FROM core.unit_kerja o
                LEFT JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
                JOIN core.lembaga_akreditasi aa ON aa.id = o.id_lembaga_akreditasi AND aa.waktu_dihapus is null
                JOIN spmi.pengisian_panduan fg ON fg.id_lembaga_akreditasi = aa.id
                    AND (
                        CASE WHEN fg.id_jenjang_pendidikan IS NULL
                            THEN true
                            ELSE fg.id_jenjang_pendidikan = o.id_jenjang_pendidikan
                        END
                    )
                    AND fg.kode_level_akses = :kode_level_akses
                    AND fg.apakah_aktif = true
                    AND fg.waktu_dihapus IS NULL
                JOIN spmi.penilaian_panduan ag ON ag.id_laporan_kinerja = fg.id
	                AND ag.id_jenjang_pendidikan = o.id_jenjang_pendidikan
                    AND ag.apakah_aktif = true
                    AND ag.waktu_dihapus is null
                JOIN spmi.jadwal_audit_unit aso ON aso.id_unit = o.id
                    AND aso.id_penilaian_panduan = ag.id
                JOIN spmi.jadwal_audit asch ON asch.id = aso.id_jadwal_audit
                    AND asch.id_audit_periode = :id_audit_periode
                    AND asch.apakah_audit_aktif = true
                    AND asch.waktu_dihapus is null
                JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = asch.id_audit_periode
                    AND sa.waktu_dihapus is null
                JOIN (
                    spmi.surat_tugas_auditor_pegawai stlp
                    JOIN core.biodata lp ON lp.id = stlp.id_personil
                        AND lp.waktu_dihapus is null
                ) ON stlp.id_unit = o.id
                    AND stlp.posisi = :posisi_ketua_auditor
                    AND stlp.id_surat_tugas_auditor = sa.id
                    AND stlp.waktu_dihapus is null
                WHERE o.id = :id_unit
                    AND o.waktu_dihapus is null";

        $bindings = [
            'id_unit' => $studyProgramId,
            'id_audit_periode' => $yearData['id'],
            'posisi_ketua_auditor' => SuratTugasAuditorPegawai::POSITION_LEAD,
            'kode_level_akses' => PengisianPanduan::LEVEL_AKSES_PS,
        ];

        if (!empty($idJadwalAudit)) {
            $sql = str_replace('asch.id_audit_periode = :id_audit_periode', 'asch.id_audit_periode = :id_audit_periode AND asch.id = :id_jadwal_audit', $sql);
            $bindings['id_jadwal_audit'] = $idJadwalAudit;
        }

        $userRole = auth()->user()->kode_role;
        $isValidatedRole = $userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE || $userRole === Role::ROLE_AUDITOR;
        $checkPosition = ($userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE)
            ? 'AND stp.posisi IN (:posisi_auditee, :posisi_member_auditee)'
            : 'AND stp.posisi NOT IN (:posisi_auditee, :posisi_member_auditee)';

        $checkValidAccess =
            "AND (
                SELECT
                    stp.id
                FROM spmi.surat_tugas_auditor_pegawai stp
                JOIN core.biodata p ON p.id = stp.id_personil AND p.waktu_dihapus is null
                WHERE stp.id_unit = o.id
                    AND p.ref_key_pegawai = :id_pegawai
                    {$checkPosition}
                    AND stp.id_surat_tugas_auditor = sa.id
                LIMIT 1
            ) IS NOT NULL";

        if ($isValidatedRole) {
            $sql .= " $checkValidAccess";

            $bindings['id_pegawai'] = session('token.idpegawai');
            $bindings['posisi_auditee'] = SuratTugasAuditorPegawai::POSITION_AUDITEE;
            $bindings['posisi_member_auditee'] = SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE;
        }

        $userUnit = session()->get('user.unit_kerja');
        if (!empty($userUnit) && ($userRole !== Role::ROLE_KAPRODI && $userRole !== Role::ROLE_AUDITOR && $userRole !== Role::ROLE_AUDITEE)) {
            $unitKerjaServices = new UnitKerjaManagementService();
            $ids = array_keys($unitKerjaServices->getUnitAncestors($userUnit->id));
            if (!empty($ids)) {
                $sql .= " AND o.id IN (" . implode(',', $ids) . ")";
            } else {
                $sql .= " AND o.id = 0"; // pastikan tidak ada data yang ditampilkan
            }
        }

        $sql .= " LIMIT 1";

        $data = DB::select($sql, $bindings);

        // Jika data dan tahun tidak ditemukan, maka kembalikan error
        if (empty($data) || empty($yearData)) {
            return new Error('Data tidak ditemukan', 404);
        }

        $data = (array) $data[0];

        $suratTugasAuditor = SuratTugasAuditor::find($data['id_surat_tugas_auditor']);
        $suratTugasAuditorAnggota = SuratTugasAuditorPegawai::with('biodata')
            ->where('posisi', SuratTugasAuditorPegawai::POSITION_MEMBER)
            ->where('id_surat_tugas_auditor', $suratTugasAuditor->id)
            ->where('id_unit', $data['id_unit'])
            ->get();

        // Panduan Penilaian harus ada, jika tidak ada maka kembalikan error
        if (empty($data['nama_penilaian_panduan'])) {
            return new Error('Panduan Penilaian belum tersedia', 404);
        }

        $information = [
            'nama_jadwal_audit' => $data['nama_jadwal_audit'],
            'periode_audit' => $yearData['tahun_audit'],
            'periode_akademik' => ($yearData['tahun_audit'] - 1) . '/' . $yearData['tahun_audit'],
            'nama_unit' => $data['nama_unit'],
            'lembaga_akreditasi' => $data['nama_singkat_lembaga'],
            'nama_penilaian_panduan' => $data['nama_penilaian_panduan'],
            'ketua_auditor' => $data['gelar_depan_ketua_auditor'] . ' ' . $data['nama_ketua_auditor'] . ' ' . $data['gelar_belakang_ketua_auditor'],
            'anggota_auditor' => $suratTugasAuditorAnggota->count() > 0 ? $suratTugasAuditorAnggota->map(function ($item) {
                return trim(
                    ($item->biodata->gelar_depan ?? '') . ' ' .
                    $item->biodata->nama . ' ' .
                    ($item->biodata->gelar_belakang ?? '')
                );
            })->implode('<br>') : '-',
        ];

        $raw = [
            'id_penilaian_panduan' => $data['id_penilaian_panduan'],
            'id_unit' => $data['id_unit'],
        ];

        return [
            $information,
            $raw
        ];
    }

    public function showInformationByAssessment(int $assessmentId): array|Error
    {
        $sql = "SELECT
                    a.id id_penilaian_audit,
                    ap.tahun_audit periode_audit,
                    o.id id_unit,
                    concat(d.kode_jenjang, ' - ', o.nama_unit) as nama_unit,
                    aa.nama_singkat_lembaga,
                    ag.nama_penilaian_panduan,
                    ag.id id_penilaian_panduan,
                    lp.nama nama_ketua_auditor,
                    sa.id id_surat_tugas_auditor,
                    asch.nama_jadwal_audit,
                    lp.gelar_depan gelar_depan_ketua_auditor,
                    lp.gelar_belakang gelar_belakang_ketua_auditor
                FROM spmi.penilaian_audit a
                JOIN spmi.audit_periode ap ON ap.id = a.id_audit_periode
                    AND ap.waktu_dihapus is null
                JOIN core.unit_kerja o ON o.id = a.id_unit
                    AND o.waktu_dihapus is null
                JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
                JOIN spmi.penilaian_panduan ag ON ag.id = a.id_penilaian_panduan
                    AND ag.apakah_aktif = true
                    AND ag.waktu_dihapus is null
                JOIN spmi.jadwal_audit_unit aso ON aso.id_unit = o.id
                    AND aso.id_penilaian_panduan = ag.id
                JOIN spmi.jadwal_audit asch ON asch.id = aso.id_jadwal_audit
                    AND asch.id_audit_periode = a.id_audit_periode
                    AND asch.id = a.id_jadwal_audit
                    AND asch.apakah_audit_aktif = true
                    AND asch.waktu_dihapus is null
                JOIN spmi.pengisian_panduan fg on fg.apakah_aktif = true and fg.id = aso.id_pengisian_panduan
                    AND fg.waktu_dihapus IS NULL
                JOIN core.lembaga_akreditasi aa ON aa.id = fg.id_lembaga_akreditasi
                    AND aa.waktu_dihapus IS NULL
                JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = asch.id_audit_periode
                    AND sa.waktu_dihapus is null
                JOIN (
                    spmi.surat_tugas_auditor_pegawai stlp
                    JOIN core.biodata lp ON lp.id = stlp.id_personil
                        AND lp.waktu_dihapus is null
                ) ON stlp.id_unit = o.id
                    AND stlp.posisi = :posisi_ketua_auditor
                    AND stlp.id_surat_tugas_auditor = sa.id
                    AND stlp.waktu_dihapus is null
                WHERE a.id = :id
                    AND a.waktu_dihapus is null";

        $userRole = auth()->user()->kode_role;
        $isInternalRole = SessionManager::isInternalRole();
        $isValidatedRole = $userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE || $userRole === Role::ROLE_AUDITOR;
        $checkPosition = ($userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE)
            ? 'AND stp.posisi IN (:posisi_auditee, :posisi_member_auditee)'
            : 'AND stp.posisi NOT IN (:posisi_auditee, :posisi_member_auditee)';

        $checkValidAccess =
            "AND (
                SELECT
                    stp.id
                FROM spmi.surat_tugas_auditor_pegawai stp
                JOIN core.biodata p ON p.id = stp.id_personil AND p.waktu_dihapus is null
                WHERE stp.id_unit = o.id
                    AND p.ref_key_pegawai = :id_pegawai
                    {$checkPosition}
                    AND stp.id_surat_tugas_auditor = sa.id
                LIMIT 1
            ) IS NOT NULL";

        $bindings = [
            'id' => $assessmentId,
            'posisi_ketua_auditor' => SuratTugasAuditorPegawai::POSITION_LEAD,
        ];

        if ($isValidatedRole && !$isInternalRole) {
            $sql .= " $checkValidAccess";

            $bindings['id_pegawai'] = session('token.idpegawai');
            $bindings['posisi_auditee'] = SuratTugasAuditorPegawai::POSITION_AUDITEE;
            $bindings['posisi_member_auditee'] = SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE;
        }

        $userUnit = session()->get('user.unit_kerja');
        if (!empty($userUnit) && ($userRole !== Role::ROLE_KAPRODI && $userRole !== Role::ROLE_AUDITOR && $userRole !== Role::ROLE_AUDITEE) && !$isInternalRole) {
            $unitKerjaServices = new UnitKerjaManagementService();
            $ids = array_keys($unitKerjaServices->getUnitAncestors($userUnit->id));
            if (!empty($ids)) {
                $sql .= " AND o.id IN (" . implode(',', $ids) . ")";
            } else {
                $sql .= " AND o.id = 0"; // pastikan tidak ada data yang ditampilkan
            }
        }

        $sql .= " LIMIT 1";

        $data = DB::select($sql, $bindings);

        if (empty($data)) {
            return new Error('Data tidak ditemukan', 404);
        }

        $data = (array) $data[0];

        $suratTugasAuditor = SuratTugasAuditor::find($data['id_surat_tugas_auditor']);
        $suratTugasAuditorAnggota = SuratTugasAuditorPegawai::with('biodata')
            ->where('posisi', SuratTugasAuditorPegawai::POSITION_MEMBER)
            ->where('id_surat_tugas_auditor', $suratTugasAuditor->id)
            ->where('id_unit', $data['id_unit'])
            ->get();

        $information = [
            'nama_jadwal_audit' => $data['nama_jadwal_audit'],
            'periode_audit' => $data['periode_audit'],
            'periode_akademik' => ($data['periode_audit'] - 1) . '/' . $data['periode_audit'],
            'nama_unit' => $data['nama_unit'],
            'lembaga_akreditasi' => $data['nama_singkat_lembaga'],
            'nama_penilaian_panduan' => $data['nama_penilaian_panduan'],
            'ketua_auditor' => $data['gelar_depan_ketua_auditor'] . ' ' . $data['nama_ketua_auditor'] . ' ' . $data['gelar_belakang_ketua_auditor'],
            'anggota_auditor' => $suratTugasAuditorAnggota->count() > 0 ? $suratTugasAuditorAnggota->map(function ($item) {
                return trim(
                    ($item->biodata->gelar_depan ?? '') . ' ' .
                    $item->biodata->nama . ' ' .
                    ($item->biodata->gelar_belakang ?? '')
                );
            })->implode('<br>') : '-',
        ];

        $raw = [
            'id_penilaian_audit' => $data['id_penilaian_audit'],
            'id_penilaian_panduan' => $data['id_penilaian_panduan'],
            'id_unit' => $data['id_unit'],
        ];

        return [
            $information,
            $raw
        ];
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return TinjauanTemuan
     */
    public function show(int $id): TinjauanTemuan
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return TinjauanTemuan|Error
     */
    public function store(array $data): TinjauanTemuan|Error
    {
        try {
            if (!isset($data['id_predikat_matriks_penilaian'])) {
                unset($data['id_predikat_matriks_penilaian']);
            }
            return $this->model->updateOrCreate([
                'id_penilaian_audit' => $data['id_penilaian_audit'],
                'id_penilaian_matriks' => $data['id_penilaian_matriks'],
            ], $data);
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return TinjauanTemuan
     */
    public function update(array $data, int $id): TinjauanTemuan
    {
        $model = $this->model->findOrFail($id);

        $model->update($data);

        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return void
     */
    public function destroy(int $id): void
    {
        $model = $this->model->findOrFail($id);

        $model->destroy($model->id);
    }

    public function showByAuditPeriode($idAuditPeriode, $idUnit)
    {
        $selectedYear = AuditPeriode::find($idAuditPeriode)?->tahun_audit;
        $previousPeriod = AuditPeriode::findByYear((int) $selectedYear - 1);

        $penilaianAudit = PenilaianAudit::where('id_audit_periode', $previousPeriod?->id)
            ->where('id_unit', $idUnit)
            ->where('apakah_penilaian_mandiri', false)
            ->first();

        $isHasRTM = TinjauanTemuan::where('id_penilaian_audit', $penilaianAudit?->id)
            ->exists();

        if (!$isHasRTM) {
            return null;
        }

        return $penilaianAudit;
    }

    /**
     * Sinkronisasi data target di periode berikutnya.
     *
     * @param int $idPenilaianAudit
     *
     * @return string|Error
     */
    public function syncNextTarget(int $idPenilaianAudit): string|Error
    {
        $penilaianAudit = PenilaianAudit::findOrFail($idPenilaianAudit);
        $penilaianPanduan = PenilaianPanduan::findOrFail($penilaianAudit->id_penilaian_panduan);
        $oldTargetIndikator = TargetIndikator::where('id_audit_periode', $penilaianAudit->id_audit_periode)
            ->where('id_unit', $penilaianAudit->id_unit)
            ->where('id_penilaian_panduan', $penilaianPanduan->id)
            ->first()?->toArray();

        if (empty($oldTargetIndikator)) {
            return new Error('Data target indikator tidak ditemukan', 404);
        }

        // Cek periode audit berikutnya apa sudah ada
        $nextTahunAudit = ((int) $penilaianAudit->periode->tahun_audit) + 1;

        $nextPeriode = AuditPeriode::where('tahun_audit', $nextTahunAudit)
            ->where('waktu_dihapus', null)
            ->first();

        if (!$nextPeriode) {
            return new Error('Periode audit '. $nextTahunAudit . ' belum dibuat', 404);
        }

        $listMappedMatriksPenilaian = MappingPenilaianMatriks::where('id_penilaian_panduan', $penilaianPanduan->id)
                ->join('spmi.penilaian_matriks as pm', 'pm.id', '=', 'mapping_penilaian_matriks.id_penilaian_matriks')
                ->where('pm.id_penilaian_panduan', $penilaianPanduan->id)
                ->where('pm.kategori_penilaian', PenilaianMatriks::CATEGORY_INDICATOR)
                ->pluck('pm.id')
                ->toArray();

        DB::beginTransaction();

        try {
            $oldTargetIndikator['apakah_terfinalisasi'] = false;
            $newTargetIndikatorData = [
                ...$oldTargetIndikator,
                'id_audit_periode' => $nextPeriode->id,
                'id_unit' => $penilaianAudit->id_unit,
                "apakah_terfinalisasi" => false,
                "total_matriks" => $penilaianPanduan->total_indikator_matriks
            ];

            $newTargetIndikator = TargetIndikator::updateOrCreate([
                'id_audit_periode' => $nextPeriode->id,
                'id_unit' => $penilaianAudit->id_unit,
                'id_penilaian_panduan' => $penilaianPanduan->id,
            ], $newTargetIndikatorData);

            // Data Target RTM
            $tinjauanTemuan = TinjauanTemuan::where('id_penilaian_audit', $penilaianAudit->id)
                ->get([
                    'id_penilaian_matriks',
                    'id_predikat_matriks_penilaian',
                    'nilai_target_default as nilai_default',
                    'nilai_target as nilai',
                ])->toArray();

            // Set id_target_indikator
            $tinjauanTemuan = array_map(function ($item) use ($newTargetIndikator) {
                $item['id_target_indikator'] = $newTargetIndikator->id;
                return $item;
            }, $tinjauanTemuan);

            // filter has in_array mapping matriks
            $tinjauanTemuan = array_filter($tinjauanTemuan, function ($item) use ($listMappedMatriksPenilaian) {
                return in_array($item['id_penilaian_matriks'], $listMappedMatriksPenilaian);
            });

            foreach ($tinjauanTemuan as $item) {
                TargetSkor::updateOrCreate([
                    'id_target_indikator' => $item['id_target_indikator'],
                    'id_penilaian_matriks' => $item['id_penilaian_matriks'],
                ], $item);
            }
        } catch (\Throwable) {
            DB::rollBack();
            return new Error('Gagal sinkronisasi data target', 500);
        }

        DB::commit();

        return route('spmi.target-indikator.edit', [$newTargetIndikator->id]);
    }

    /**
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return Error|null
     */
    public function destroySome($ids)
    {
        return ManagementService::create($this->model)->destroySome($ids);
    }

    /**
     * Menampilkan semua matrix berdasarkan TinjauanTemuan guide. Untuk kebutuhan penilaian
     *
     * @param int $assessmentGuideId
     * @return mixed
     */
    public static function showAllMatricesByPenilaianPanduan(int $assessmentGuideId, int $studyProgramId, int $auditPeriodId, int|null $assessmentId = null, $isIndicatorOnly = false, int|null $idJadwalAudit = null): mixed
    {
        $targetJadwalFilter = '';
        if (!empty($idJadwalAudit)) {
            $targetJadwalFilter = 'AND (at.id_jadwal_audit IS NULL OR at.id_jadwal_audit = :id_jadwal_audit)';
        }

        $sql = "SELECT
                    m.id,
                    m.nomor_penilaian,
                    m.pertanyaan_penilaian,
                    m.kategori_penilaian,
                    m.jenis_penilaian,
                    m.bobot_penilaian,
                    m.id_parent,
                    m.info_level,
                    m.apakah_data_default,
                    sc.nilai nilai_auditor,
	                ats.nilai nilai_target,
                    tt.id id_tinjauan_temuan,
                    af.uraian_temuan_audit,
                    tt.rencana_peningkatan_mutu,
                    tt.akar_masalah,
                    tt.pelaksana,
                    tt.tanggal_peningkatan_mutu,
                    af.rencana_peningkatan_mutu t_rencana_peningkatan_mutu,
                    af.akar_masalah t_akar_masalah,
                    af.pelaksana t_pelaksana,
                    af.tanggal_peningkatan_mutu t_tanggal_peningkatan_mutu,
                    af.jenis_temuan,
	                CASE WHEN pl.id_penilaian_matriks IS NOT NULL THEN true ELSE false END apakah_temuan_berulang,
                    m.info_left
                FROM spmi.penilaian_matriks m
                LEFT JOIN spmi.target_indikator at ON at.id_penilaian_panduan = m.id_penilaian_panduan
                    AND at.id_audit_periode = :id_audit_periode
                    AND at.id_unit = :id_unit AND at.waktu_dihapus is null
                    {$targetJadwalFilter}
                    AND at.waktu_dihapus is null
                LEFT JOIN spmi.target_skor ats ON ats.id_target_indikator = at.id
                    AND ats.id_penilaian_matriks = m.id
                LEFT JOIN spmi.penilaian_audit a ON a.id = :id_penilaian_audit
                    AND a.waktu_dihapus is null
                LEFT JOIN spmi.audit_temuan af ON af.id_penilaian_audit = a.id
                    AND af.id_penilaian_matriks = m.id
                    AND af.waktu_dihapus is null
                LEFT JOIN spmi.tinjauan_temuan tt ON tt.id_penilaian_audit = a.id
                    AND tt.id_penilaian_matriks = m.id
                    AND tt.waktu_dihapus is null
                LEFT JOIN spmi.penilaian_skor sc ON sc.id_penilaian_audit = a.id
                    AND sc.id_penilaian_matriks = m.id
                LEFT JOIN (
                    SELECT
                        pa.id_audit_periode,
                        pa.id_unit,
                        pm.id id_penilaian_matriks
                    FROM
                        spmi.penilaian_audit pa
                        JOIN spmi.penilaian_matriks pm ON pm.id_penilaian_panduan = pa.id_penilaian_panduan
                            AND pm.waktu_dihapus IS NULL
                        JOIN spmi.penilaian_skor ps ON ps.id_penilaian_audit = pa.id
                            AND ps.id_penilaian_matriks = pm.id
                    WHERE
                        pa.waktu_dihapus IS NULL
                        AND (
                            (ps.status_penilaian = :status_belum_memenuhi_lampau OR ps.status_penilaian = :status_menyimpang_lampau)
                            AND ps.nilai is not null
                        )
                        AND pm.kategori_penilaian = :kategori_penilaian_indikator
                ) pl ON pl.id_audit_periode = :id_audit_periode_old
                    AND pl.id_unit = :id_unit_old
                    AND pl.id_penilaian_matriks = m.id
                WHERE m.waktu_dihapus is null
                    AND m.id_penilaian_panduan = :id_penilaian_panduan
                    AND (
                        (sc.status_penilaian = :status_belum_memenuhi OR sc.status_penilaian = :status_menyimpang)
                        AND sc.nilai is not null
                    )";

        if (!$isIndicatorOnly) {
            $sql .= " OR m.kategori_penilaian = :kategori_penilaian_elemen";
            $bindings['kategori_penilaian_elemen'] = PenilaianMatriks::CATEGORY_ELEMENT;
        }

        $sql .= " ORDER BY m.info_left ASC";

        // Ambil periode audit sebelumnya
        $nowAuditPeriodeYear = AuditPeriode::find($auditPeriodId)?->tahun_audit;
        if (!empty($nowAuditPeriodeYear)) {
            $idAuditPeriodeOld = AuditPeriode::findByYear((int) $nowAuditPeriodeYear - 1)?->id ?? null;
        }

        $bindings = [
            ...$bindings ?? [],
            'id_penilaian_panduan' => $assessmentGuideId,
            'id_unit' => $studyProgramId,
            'id_audit_periode' => $auditPeriodId,
            'id_penilaian_audit' => $assessmentId,
            'kategori_penilaian_indikator' => PenilaianMatriks::CATEGORY_INDICATOR,
            'status_belum_memenuhi' => PenilaianSkor::STATUS_BELUM_MEMENUHI,
            'status_belum_memenuhi_lampau' => PenilaianSkor::STATUS_BELUM_MEMENUHI,
            'status_menyimpang' => PenilaianSkor::STATUS_MENYIMPANG,
            'status_menyimpang_lampau' => PenilaianSkor::STATUS_MENYIMPANG,
            'id_unit_old' => $studyProgramId,
            'id_audit_periode_old' => $idAuditPeriodeOld ?? null,
        ];

        if (!empty($idJadwalAudit)) {
            $bindings['id_jadwal_audit'] = $idJadwalAudit;
        }

        $data = DB::select($sql, $bindings);

        return $data;
    }

    public function showByAssessment($assessmentId, $PenilaianMatriksId): array
    {
        return TinjauanTemuan::where('id_penilaian_audit', $assessmentId)
            ->where('id_penilaian_matriks', $PenilaianMatriksId)
            ->first()
            ?->toArray() ?? [];
    }

    //  Sementara tidak digunakan
    public function checkFinishAssessment($assessmentId)
    {
        $sqlTotalAssessed =
            "SELECT
                count(1) AS total
            FROM spmi.penilaian_skor sc
            JOIN spmi.penilaian_matriks m ON m.id = sc.id_penilaian_matriks
                AND m.waktu_dihapus is null
            WHERE sc.id_penilaian_audit = :id_penilaian_audit
                AND sc.nilai is not null
                AND m.kategori_penilaian = :kategori_penilaian";

        $totalAssessed = DB::select($sqlTotalAssessed, [
            'id_penilaian_audit' => $assessmentId,
            'kategori_penilaian' => PenilaianMatriks::CATEGORY_INDICATOR,
        ])[0]->total ?? 0;

        $sqlTotalAllMatrix =
            "SELECT
                count(1) AS total
            FROM spmi.penilaian_matriks m
            WHERE m.waktu_dihapus is null
                AND m.kategori_penilaian = :kategori_penilaian
                AND m.id_penilaian_panduan = (
                    SELECT id_penilaian_panduan FROM spmi.penilaian_audit WHERE id = :id_penilaian_audit
                )
                AND m.waktu_dihapus is null";

        $totalAllMatrix =  DB::select($sqlTotalAllMatrix, [
            'id_penilaian_audit' => $assessmentId,
            'kategori_penilaian' => PenilaianMatriks::CATEGORY_INDICATOR,
        ])[0]->total ?? 0;

        return $totalAssessed === $totalAllMatrix;
    }
}
