<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Helpers\SessionManager;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\Gate\Models\Role;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\JadwalAuditUnit;
use Modules\SPMI\Models\MappingPenilaianMatriks;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\TargetIndikator;
use Modules\SPMI\Models\TargetSkor;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;

class TargetIndikatorManagementService
{
    /**
     * @var TargetIndikator
     */
    protected $model = TargetIndikator::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new TargetIndikator;
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

        // Jika belum dibuat data target capaiannya, maka diambil dari prodi
        $sql = "SELECT
                    ap.tahun_audit periode_audit,
                    ja.nama_jadwal_audit,
                    CASE WHEN at.id IS NOT NULL THEN at.id ELSE o.id END id,
                    ja.id id_jadwal_audit,
                    CONCAT(d.kode_jenjang, ' - ', o.nama_unit) nama_unit,
                    fg.nama_pengisian_panduan,
                    ag.nama_penilaian_panduan,
                    CASE WHEN at.id IS NOT NULL THEN true ELSE false END apakah_sudah_dibuat,
                    ts.total total_target_terisi,
                    ppp.total_indikator_matriks
                FROM core.unit_kerja o
                JOIN spmi.audit_periode ap ON ap.id = ? AND ap.waktu_dihapus IS NULL
                JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
                JOIN spmi.jadwal_audit ja ON ja.id_audit_periode = ap.id AND ja.waktu_dihapus IS NULL
                JOIN spmi.jadwal_audit_unit jau ON jau.id_jadwal_audit = ja.id AND jau.id_unit = o.id
                JOIN spmi.pengisian_panduan fg ON fg.id = jau.id_pengisian_panduan
                    AND fg.apakah_aktif = true
                    AND fg.waktu_dihapus IS NULL
                JOIN spmi.penilaian_panduan ag ON ag.id = jau.id_penilaian_panduan AND ag.apakah_target_aktif = true
                    AND ag.waktu_dihapus is null
                LEFT JOIN spmi.target_indikator at ON at.id_unit = o.id
                    AND at.id_penilaian_panduan = ag.id
                    AND at.id_audit_periode = ap.id
                    AND at.id_jadwal_audit = ja.id
                    AND at.waktu_dihapus is null
                JOIN (
                    SELECT pp2.id, mpm.id_audit_periode, mpm.id_unit, COUNT(mpm.id) total_indikator_matriks FROM spmi.mapping_penilaian_matriks mpm
                    JOIN spmi.penilaian_matriks pm ON pm.id = mpm.id_penilaian_matriks AND pm.kategori_penilaian = '".PenilaianMatriks::CATEGORY_INDICATOR."'
                    JOIN spmi.penilaian_panduan pp2 ON pp2.id = pm.id_penilaian_panduan
                    WHERE pm.waktu_dihapus is null
                    GROUP BY pp2.id, mpm.id_audit_periode, mpm.id_unit
                ) ppp ON ppp.id = jau.id_penilaian_panduan AND ppp.id_audit_periode = ap.id AND ppp.id_unit = jau.id_unit
                LEFT JOIN (
                    SELECT ts.id_target_indikator, pm.id_penilaian_panduan, mpm.id_audit_periode, mpm.id_unit,
                        COUNT(DISTINCT pm.id) FILTER(WHERE pm.kategori_penilaian = '".PenilaianMatriks::CATEGORY_INDICATOR."') total FROM spmi.target_skor ts
                    JOIN spmi.penilaian_matriks pm ON pm.id = ts.id_penilaian_matriks
                    JOIN spmi.mapping_penilaian_matriks mpm ON mpm.id_penilaian_matriks = pm.id
                GROUP BY ts.id_target_indikator, pm.id_penilaian_panduan, mpm.id_audit_periode, mpm.id_unit
                ) ts ON
                    ts.id_target_indikator = at.id AND ts.id_penilaian_panduan = ag.id AND ts.id_audit_periode = ap.id AND ts.id_unit = o.id";

        $fieldMap = [
            'id_jenjang_pendidikan' => 'd.id',
            'id_unit_kerja' => 'o.id',
            'periode_audit' => 'ap.tahun_audit',
            'status_pengisian_target' => 'ts.total'
        ];

        $defaultFilter = "o.waktu_dihapus is null";

        $bindings = [(int) $auditPeriodId];
        $userRole = auth()->user()->kode_role;

        if (($userRole === Role::ROLE_AUDITOR || $userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE)) {
            $filterOperator = $userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE ? 'IN' : 'NOT IN';
            $defaultFilter .=
                " AND (
                    SELECT
                        stp.id
                    FROM spmi.surat_tugas_auditor_pegawai stp
                    JOIN spmi.jadwal_audit_unit aso ON aso.id_unit = o.id
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
            groupBy: 'o.id, ap.id, ag.id, at.id, ja.id, ts.total, d.id, ts.id_target_indikator, fg.id, ppp.total_indikator_matriks',
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    public function showInformationByStudyProgram(int $studyProgramId, array|null $periodData, int|null $idJadwalAudit = null): array|Error
    {
        if (!isset($periodData)) {
            return new Error('Tahun audit tidak ditemukan', 404);
        }

        $sql = "SELECT
                    concat(d.kode_jenjang, ' - ', o.nama_unit) nama_unit,
                    aa.nama_singkat_lembaga nama_lembaga_akreditasi,
                    ag.nama_penilaian_panduan,
                    o.id id_unit,
                    aa.id id_lembaga_akreditasi,
                    ag.id id_penilaian_panduan,
                    ag.total_indikator_matriks,
                    ja.nama_jadwal_audit,
                    d.kode_jenjang
                FROM core.unit_kerja o
                JOIN spmi.audit_periode ap ON ap.id = :id_periode
                    AND ap.waktu_dihapus IS NULL
                JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
                    AND d.waktu_dihapus IS NULL
                JOIN spmi.jadwal_audit ja ON ja.id_audit_periode = ap.id
                    AND ja.waktu_dihapus IS NULL
                JOIN spmi.jadwal_audit_unit jau ON jau.id_jadwal_audit = ja.id AND jau.id_unit = o.id
                JOIN spmi.pengisian_panduan fg ON fg.id = jau.id_pengisian_panduan
                    AND fg.apakah_aktif = true
                    AND fg.waktu_dihapus IS NULL
                JOIN spmi.penilaian_panduan ag ON ag.id = jau.id_penilaian_panduan
                    AND ag.apakah_target_aktif = true
                    AND ag.waktu_dihapus IS NULL
                JOIN core.lembaga_akreditasi aa ON aa.id = fg.id_lembaga_akreditasi
                    AND aa.waktu_dihapus IS NULL
                WHERE o.id = :id_unit";

        $bindings = [
            'id_periode' => $periodData['id'],
            'id_unit' => $studyProgramId,
        ];

        if (!empty($idJadwalAudit)) {
            $sql .= " AND ja.id = :id_jadwal_audit";
            $bindings['id_jadwal_audit'] = $idJadwalAudit;
        }

        $userRole = auth()->user()->kode_role;
        if (($userRole === Role::ROLE_AUDITOR || $userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE)) {
            $filterOperator = $userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE ? 'IN' : 'NOT IN';
            $sql .=
                " AND (
                    SELECT
                        stp.id
                    FROM spmi.surat_tugas_auditor_pegawai stp
                    JOIN spmi.jadwal_audit_unit aso ON aso.id_unit = o.id
                    JOIN spmi.jadwal_audit asch ON asch.id = aso.id_jadwal_audit
                        AND asch.id_audit_periode = ap.id
                        AND asch.apakah_audit_aktif = true
                        AND asch.waktu_dihapus is null
                    JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = asch.id_audit_periode
                        AND sa.waktu_dihapus is null
                    JOIN core.biodata p ON p.id_user = :id_user
                        AND p.waktu_dihapus is null
                    WHERE stp.id_unit = o.id
                        AND stp.id_personil = p.id
                        AND stp.posisi {$filterOperator} (:posisi, :posisi2)
                        AND stp.id_surat_tugas_auditor = sa.id
                    LIMIT 1
                ) IS NOT NULL";

            $bindings['id_user'] = auth()->id();
            $bindings['posisi'] = SuratTugasAuditorPegawai::POSITION_AUDITEE;
            $bindings['posisi2'] = SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE;
        }

        // Filter tambahan untuk cek hak akses unit
        $isInternalRole = SessionManager::isInternalRole();
        $userUnit = session()->get('user.unit_kerja');
        if (!empty($userUnit) && ($userRole !== Role::ROLE_AUDITOR && $userRole !== Role::ROLE_KAPRODI && $userRole !== Role::ROLE_AUDITEE) && !$isInternalRole) {
            $unitKerjaServices = new UnitKerjaManagementService();
            $ids = array_keys($unitKerjaServices->getUnitAncestors($userUnit->id));
            if (!empty($ids)) {
                $sql .= " AND o.id IN (" . implode(',', $ids) . ")";
            } else {
                $sql .= " AND o.id = 0"; // pastikan tidak ada data yang ditampilkan
            }
        }

        $sql .= ' LIMIT 1';

        $data = DB::select($sql, $bindings);

        // Jika data dan tahun tidak ditemukan, maka kembalikan error
        if (empty($data) || empty($periodData)) {
            return new Error('Data tidak ditemukan', 404);
        }

        $data = (array) $data[0];

        // Panduan Penilaian harus ada, jika tidak ada maka kembalikan error
        if (empty($data['nama_penilaian_panduan'])) {
            return new Error('Panduan Penilaian belum tersedia', 404);
        }

        $tahunPeriode = ((int) $periodData['tahun_audit']);
        $information = [
            'nama_jadwal_audit' => $data['nama_jadwal_audit'],
            'periode_audit' => $tahunPeriode,
            'periode_akademik' => ($tahunPeriode - 1) . '/' . $tahunPeriode,
            'nama_unit' => $data['nama_unit'],
            'nama_lembaga_akreditasi' => $data['nama_lembaga_akreditasi'],
            'nama_penilaian_panduan' => $data['nama_penilaian_panduan'],
        ];

        $raw = [
            'id_audit_periode' => $periodData['id'],
            'id_unit' => $data['id_unit'],
            'id_lembaga_akreditasi' => $data['id_lembaga_akreditasi'],
            'id_penilaian_panduan' => $data['id_penilaian_panduan'],
            'id_jadwal_audit' => $idJadwalAudit,
            'total_matriks' => $data['total_indikator_matriks'],
            'apakah_terfinalisasi' => false,
        ];

        return [
            $information,
            $raw
        ];
    }

    public function showInformation(int $id)
    {
        $sql = "SELECT
                    ap.tahun_audit periode_audit,
                    concat(d.kode_jenjang, ' - ', o.nama_unit) nama_unit,
                    aa.nama_singkat_lembaga nama_lembaga_akreditasi,
                    ag.nama_penilaian_panduan,
                    ap.id id_audit_periode,
                    o.id id_unit,
                    aa.id id_lembaga_akreditasi,
                    ag.id id_penilaian_panduan,
                    at.apakah_terfinalisasi,
                    at.id_jadwal_audit,
                    ja.nama_jadwal_audit,
                    d.kode_jenjang,
                    CASE
                        WHEN at.apakah_terfinalisasi
                        THEN at.total_matriks
                        ELSE ag.total_indikator_matriks
                    END total_indikator_matriks
                FROM spmi.target_indikator at
                LEFT JOIN (
                    SELECT ts.id_target_indikator FROM spmi.target_skor ts GROUP BY ts.id_target_indikator
                ) ts ON ts.id_target_indikator = at.id
                JOIN core.unit_kerja o ON o.id = at.id_unit
                    AND o.waktu_dihapus IS NULL
                JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
                    AND d.waktu_dihapus IS NULL
                JOIN spmi.audit_periode ap ON ap.id = at.id_audit_periode
                    AND ap.waktu_dihapus IS NULL
                JOIN spmi.jadwal_audit_unit jau ON jau.id_unit = o.id
                JOIN spmi.jadwal_audit ja ON ja.id = jau.id_jadwal_audit
                    AND ja.id_audit_periode = ap.id
                    AND ja.waktu_dihapus IS NULL
                JOIN spmi.pengisian_panduan fg ON fg.id = jau.id_pengisian_panduan
                    AND fg.apakah_aktif = true
                    AND fg.waktu_dihapus IS NULL
                JOIN core.lembaga_akreditasi aa ON aa.id = fg.id_lembaga_akreditasi
                JOIN spmi.penilaian_panduan ag ON ag.id = at.id_penilaian_panduan
                    AND ag.apakah_target_aktif = true
                    AND ag.id = jau.id_penilaian_panduan
                JOIN (
                    SELECT
                        pm.id_penilaian_panduan,
                        COUNT(1) total_indikator_matriks
                    FROM spmi.penilaian_matriks pm
                    WHERE pm.kategori_penilaian = :category
                    GROUP BY pm.id_penilaian_panduan
                ) total ON total.id_penilaian_panduan = ag.id
                WHERE at.id = :id";

        $bindings = [
            'id' => $id,
            'category' => PenilaianMatriks::CATEGORY_INDICATOR
        ];

        $userRole = auth()->user()->kode_role;
        if (($userRole === Role::ROLE_AUDITOR || $userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE)) {
            $filterOperator = $userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE ? 'IN' : 'NOT IN';
            $sql .=
                " AND (
                    SELECT
                        stp.id
                    FROM spmi.surat_tugas_auditor_pegawai stp
                    JOIN spmi.jadwal_audit_unit aso ON aso.id_unit = o.id
                    JOIN spmi.jadwal_audit asch ON asch.id = aso.id_jadwal_audit
                        AND asch.id_audit_periode = ap.id
                        AND asch.apakah_audit_aktif = true
                        AND asch.waktu_dihapus is null
                    JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = asch.id_audit_periode
                        AND sa.waktu_dihapus is null
                    JOIN core.biodata p ON p.id_user = :id_user
                        AND p.waktu_dihapus is null
                    WHERE stp.id_unit = o.id
                        AND stp.id_personil = p.id
                        AND stp.posisi {$filterOperator} (:posisi, :posisi2)
                        AND stp.id_surat_tugas_auditor = sa.id
                    LIMIT 1
                ) IS NOT NULL";

            $bindings['id_user'] = auth()->id();
            $bindings['posisi'] = SuratTugasAuditorPegawai::POSITION_AUDITEE;
            $bindings['posisi2'] = SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE;
        }

        // Filter tambahan untuk cek hak akses unit
        $isInternalRole = SessionManager::isInternalRole();
        $userUnit = session()->get('user.unit_kerja');
        if (!empty($userUnit) && ($userRole !== Role::ROLE_AUDITOR && $userRole !== Role::ROLE_KAPRODI && $userRole !== Role::ROLE_AUDITEE) && !$isInternalRole) {
            $unitKerjaServices = new UnitKerjaManagementService();
            $ids = array_keys($unitKerjaServices->getUnitAncestors($userUnit->id));
            if (!empty($ids)) {
                $sql .= " AND o.id IN (" . implode(',', $ids) . ")";
            } else {
                $sql .= " AND o.id = 0"; // pastikan tidak ada data yang ditampilkan
            }
        }

        $sql .= ' LIMIT 1';

        $data = DB::select($sql, $bindings);

        if (empty($data)) {
            return new Error('Data tidak ditemukan', 404);
        }

        $data = (array) $data[0];

        $tahunPeriode = ((int) $data['periode_audit']);
        $information = [
            'nama_jadwal_audit' => $data['nama_jadwal_audit'],
            'periode_audit' => $tahunPeriode,
            'periode_akademik' => ($tahunPeriode - 1) . '/' . $tahunPeriode,
            'nama_unit' => $data['nama_unit'],
            'nama_lembaga_akreditasi' => $data['nama_lembaga_akreditasi'],
            'nama_penilaian_panduan' => $data['nama_penilaian_panduan'],
        ];

        $raw = [
            'id_audit_periode' => $data['id_audit_periode'],
            'id_unit' => $data['id_unit'],
            'id_lembaga_akreditasi' => $data['id_lembaga_akreditasi'],
            'id_penilaian_panduan' => $data['id_penilaian_panduan'],
            'id_jadwal_audit' => $data['id_jadwal_audit'],
            'total_matriks' => $data['total_indikator_matriks'],
            'apakah_terfinalisasi' => $data['apakah_terfinalisasi'] == true,
        ];

        return [
            $information,
            $raw
        ];
    }

    public function isFinalizeAuditorAssessment($idPeriodeAudit, $idUnitKerja, $idPenilaianPanduan, $idJadwalAudit): bool
    {
        return PenilaianAudit::where('id_unit', $idUnitKerja)
            ->where('id_audit_periode', $idPeriodeAudit)
            ->where('id_penilaian_panduan', $idPenilaianPanduan)
            ->where('id_jadwal_audit', $idJadwalAudit)
            ->where('apakah_penilaian_mandiri', false)
            ->where('apakah_terfinalisasi', true)
            ->exists();
    }

    /**
     * Menampilkan semua value skor capaian target.
     *
     * @param int $id
     * @return array
     */
    public function showScoreValues(int $id): array
    {
        $raw = TargetSkor::where('id_target_indikator', $id)->get([
            'id_target_indikator',
            'id_penilaian_matriks',
            'id_predikat_matriks_penilaian',
            'nilai_default',
            'nilai',
        ])->toArray();

        $result = [];
        foreach ($raw as $value) {
            $result[$value['id_penilaian_matriks'] . '/' . $value['id_predikat_matriks_penilaian']] = $value;
        }

        return $result;
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return TargetIndikator
     */
    public function show(int $id): TargetIndikator
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return TargetIndikator|Error
     */
    public function store(array $data, array $raw, $id = null): TargetIndikator|Error
    {
        DB::beginTransaction();

        $unitKerja = UnitKerja::find($raw['id_unit']);
        if (!$unitKerja) {
            return new Error('Unit kerja tidak ditemukan', 404);
        }

        $model = TargetIndikator::where('id_audit_periode', $raw['id_audit_periode'])
            ->where('id_unit', $raw['id_unit'])
            ->where('id_penilaian_panduan', $raw['id_penilaian_panduan'])
            ->when(!empty($raw['id_jadwal_audit']), fn ($q) => $q->where('id_jadwal_audit', $raw['id_jadwal_audit']))
            ->first();

        if (empty($model)) {
            $model = TargetIndikator::create($raw);
        }

        foreach ($data as $key => $item) {
            $data[$key]['id_target_indikator'] = $model->id ?? $id;
        }

        $this->storeScore($data);

        DB::commit();

        return $model;
    }

    public function removeScore(int $achievementTargetId, int $idPenilaianMatriks)
    {
        return TargetSkor::where('id_target_indikator', $achievementTargetId)
            ->where('id_penilaian_matriks', $idPenilaianMatriks)
            ->delete();
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
     * Menyimpan skor capaian target.
     *
     * @param array $data
     * @param int $id
     * @return mixed
     */
    private function storeScore(array $data): mixed
    {
        $data = array_values($data);

        if (!empty($data)) {
            $totalStore = 0;

            try {
                foreach ($data as $value) {
                    // remove current
                    $this->removeScore($value['id_target_indikator'], $value['id_penilaian_matriks']);

                    // store new
                    TargetSkor::create($value);
                    $totalStore++;
                }
            } catch (\Exception $e) {
                return new Error(exception: $e);
            };

            return $totalStore;
        }

        return 0;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Ethnic
     */
    public function update(array $data, int $id): TargetIndikator
    {
        $model = $this->model->findOrFail($id);

        $model->update($data);

        return $model;
    }

    /**
     * Update skor capaian target.
     *
     * @param array $data
     * @param int $id
     * @param array $errors
     * @return mixed
     */
    public function updateScore(int $achievementTargetId, int $idPenilaianMatriks, $score)
    {
        $data = TargetSkor::where('id_target_indikator', $achievementTargetId)
            ->where('id_penilaian_matriks', $idPenilaianMatriks)->first();

        return $data->update(['nilai' => $score]);
    }

    /**
     * Menampilkan semua matrix berdasarkan assessment guide.
     *
     * @param int $assessmentGuideId
     * @return mixed
     */
    public function showAllMatricesByPenilaianPanduan(int $idPeriode, int $idUnit, int $assessmentGuideId, $idTargetIndikator = null): mixed
    {
        $sql = "SELECT
                    pm.id,
                    pm.nomor_penilaian,
                    pm.pertanyaan_penilaian,
                    pm.kategori_penilaian,
                    pm.id_parent,
                    pm.apakah_data_default,
                    pm.info_level,
                    pm.info_left,
                    pm.id_penilaian_panduan,
                    pp.kode_penilaian_panduan,
                    pp.apakah_data_default as apakah_panduan_default,
                    JSON_AGG(
                        CASE WHEN pmp.id_penilaian_matriks IS NOT NULL THEN
                            json_build_object(
                                'id', pmp.id,
                                'nilai', smpp.nilai,
                                'deskripsi', pmp.deskripsi,
                                'apakah_nonaktif', pmp.apakah_nonaktif
                            )
                        ELSE null END
                    ) skor_indikator
                FROM spmi.penilaian_matriks pm
                JOIN spmi.penilaian_panduan pp ON pp.id = pm.id_penilaian_panduan
                JOIN spmi.mapping_penilaian_matriks mpm ON mpm.id_penilaian_matriks = pm.id
                    AND mpm.id_audit_periode = :id_audit_periode
                    AND mpm.id_unit = :id_unit
                LEFT JOIN spmi.penilaian_matriks_predikat pmp ON pmp.id_penilaian_matriks = pm.id
                    AND pmp.waktu_dihapus is null
                LEFT JOIN spmi.skor_matriks_predikat_penilaian smpp ON smpp.id = pmp.id_skor_matriks_predikat_penilaian
                    AND smpp.waktu_dihapus is null
                LEFT JOIN spmi.target_indikator ti ON ti.id_penilaian_panduan = pm.id_penilaian_panduan
                    AND ti.id = :id_target_indikator
                    AND ti.waktu_dihapus is null
                LEFT JOIN spmi.target_skor ts ON ts.id_penilaian_matriks = pm.id
                    AND ts.id_target_indikator = ti.id
                WHERE pm.waktu_dihapus IS NULL AND pm.id_penilaian_panduan = :id_penilaian_panduan
                GROUP BY pm.id, pm.pertanyaan_penilaian, pm.kategori_penilaian,
                    pm.info_left, pmp.id_penilaian_matriks, pp.kode_penilaian_panduan, pp.apakah_data_default
                ORDER BY pm.info_left ASC";

        $data = DB::select($sql, [
            'id_penilaian_panduan' => $assessmentGuideId,
            'id_target_indikator' => $idTargetIndikator,
            'id_audit_periode' => $idPeriode,
            'id_unit' => $idUnit,
        ]);

        return $data;
    }

    /**
     * Salin data berdasarkan id periode audit dan id unit kerja
     *
     * @param int $oldIdAuditPeriode
     * @param int $newIdAuditPeriode
     * @param int $idUnitKerja
     *
     * @return Error|string
     */
    public function copyData(int $oldIdAuditPeriode, int $newIdAuditPeriode, int $idUnitKerja): Error|string
    {
        $idPenilaianPanduan = JadwalAuditUnit::join('spmi.jadwal_audit', 'spmi.jadwal_audit.id', '=', 'spmi.jadwal_audit_unit.id_jadwal_audit')
            ->where('spmi.jadwal_audit.id_audit_periode', $oldIdAuditPeriode)
            ->where('spmi.jadwal_audit_unit.id_unit', $idUnitKerja)
            ->value('id_penilaian_panduan');

        $idPenilaianPanduanNew = JadwalAuditUnit::join('spmi.jadwal_audit', 'spmi.jadwal_audit.id', '=', 'spmi.jadwal_audit_unit.id_jadwal_audit')
            ->where('spmi.jadwal_audit.id_audit_periode', $newIdAuditPeriode)
            ->where('spmi.jadwal_audit_unit.id_unit', $idUnitKerja)
            ->value('id_penilaian_panduan');

        if ($idPenilaianPanduan !== $idPenilaianPanduanNew) {
            return new Error('Panduan penilaian pada periode audit tujuan berbeda dengan periode audit asal.', 400);
        }

        $targetIndikatorToCopy = $this->model->where('id_audit_periode', $oldIdAuditPeriode)
            ->where('id_unit', $idUnitKerja)
            ->where('id_penilaian_panduan', $idPenilaianPanduan)
            ->first([
                'id',
                'id_lembaga_akreditasi',
                'id_penilaian_panduan',
            ]);

        if (empty($targetIndikatorToCopy)) {
            return new Error('Data target indikator yang disalin tidak ditemukan', 404);
        }

        $unitKerja = UnitKerja::find($idUnitKerja);
        $newAuditPeriode = AuditPeriode::find($newIdAuditPeriode);
        $oldAuditPeriode = AuditPeriode::find($oldIdAuditPeriode);

        $idsPenilaianMatriks = MappingPenilaianMatriks::where('id_audit_periode', $newAuditPeriode->id)
            ->where('id_unit', $idUnitKerja)
            ->pluck('id_penilaian_matriks')
            ->toArray();

        $kodeJenjang = $unitKerja->jenjangPendidikan?->kode_jenjang;
        $unitKerjaName = $kodeJenjang . ' - ' . $unitKerja->nama_unit;
        $auditPeriodeName = $newAuditPeriode->tahun_audit;
        $oldAuditPeriodeName = $oldAuditPeriode->tahun_audit;
        $targetSkor = TargetSkor::where('id_target_indikator', $targetIndikatorToCopy->id)
            ->whereIn('id_penilaian_matriks', $idsPenilaianMatriks)
            ->get();

        DB::beginTransaction();

        $newTargetIndikator = $this->model->firstOrCreate([
            'id_audit_periode' => $newIdAuditPeriode,
            'id_unit' => $idUnitKerja,
            'id_penilaian_panduan' => $targetIndikatorToCopy->id_penilaian_panduan,
        ], [
            'id_audit_periode' => $newIdAuditPeriode,
            'id_unit' => $idUnitKerja,
            'apakah_terfinalisasi' => false,
            'total_matriks' => null,
            ...$targetIndikatorToCopy->toArray(),
        ]);

        foreach ($targetSkor as $skor) {
            $newSkor = [
                'id_target_indikator' => $newTargetIndikator->id,
                'id_penilaian_matriks' => $skor->id_penilaian_matriks,
                'id_predikat_matriks_penilaian' => $skor->id_predikat_matriks_penilaian,
                'nilai_default' => $skor->nilai_default,
                'nilai' => $skor->nilai,
            ];

            try {
                TargetSkor::firstOrCreate([
                    'id_target_indikator' => $newTargetIndikator->id,
                    'id_penilaian_matriks' => $skor->id_penilaian_matriks,
                ], $newSkor);
            } catch (\Throwable) {
                DB::rollBack();
                return new Error('Salin data gagal, terjadi kesalahan saat menyimpan skor target indikator.');
            }
        }

        DB::commit();

        return "Target indikator pada program studi {$unitKerjaName} periode {$oldAuditPeriodeName} berhasil disalin ke periode {$auditPeriodeName}.";
    }
}
