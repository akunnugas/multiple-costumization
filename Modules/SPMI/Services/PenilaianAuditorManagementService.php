<?php

namespace Modules\SPMI\Services;

use Carbon\Carbon;
use Illuminate\Session\SessionManager as SessionSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Helpers\SessionManager;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\Gate\Models\Role;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\AkreditasiPeringkat;
use Modules\SPMI\Models\AkreditasiStatus;
use Modules\SPMI\Models\AkreditasiSyarat;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianSkor;
use Modules\SPMI\Models\HasilAkhirAudit;
use Modules\SPMI\Models\IndikatorBobot;
use Modules\SPMI\Models\MappingPenilaianMatriks;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\SpmiPeringkat;
use Modules\SPMI\Models\SuratTugasAuditor;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;
use Modules\SPMI\Services\Traits\AssessmentServiceTrait;

class PenilaianAuditorManagementService
{
    use AssessmentServiceTrait;

    /**
     * @var Assessment
     */
    protected $model = PenilaianAudit::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PenilaianAudit;
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

        // Jika belum dibuat data penilaian, maka diambil dari prodi
        $sql =
            "SELECT
                ap.tahun_audit periode_audit,
                COALESCE(pa.id, uk.id) id,
                ja.id AS id_jadwal_audit,
                ja.nama_jadwal_audit,
                CONCAT(jp.kode_jenjang, ' - ', uk.nama_unit) AS nama_unit,
                la.nama_singkat_lembaga nama_lembaga_akreditasi,
                pp.nama_singkat AS nama_penilaian_panduan,
                pp.id AS id_penilaian_panduan,
                CASE WHEN pa.id IS NOT NULL THEN true ELSE false END apakah_sudah_dibuat,
                COALESCE(ps2.total_indikator_terisi, 0) total_penilaian_terisi,
                MAX(ppp.total_indikator_matriks) total_indikator_matriks,
                ps.apakah_punya_feedback
            FROM core.unit_kerja uk
            JOIN core.jenjang_pendidikan jp ON jp.id = uk.id_jenjang_pendidikan AND jp.waktu_dihapus IS NULL
            JOIN spmi.audit_periode ap ON ap.id = ? AND ap.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit ja ON ja.id_audit_periode = ap.id AND ja.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit_unit jau ON jau.id_jadwal_audit = ja.id AND jau.id_unit = uk.id
            JOIN spmi.penilaian_panduan pp ON pp.id = jau.id_penilaian_panduan AND pp.apakah_aktif = true AND pp.waktu_dihapus IS NULL
            JOIN (
                SELECT pp2.id, mpm.id_audit_periode, mpm.id_unit, COUNT(mpm.id) total_indikator_matriks FROM spmi.mapping_penilaian_matriks mpm
                JOIN spmi.penilaian_matriks pm ON pm.id = mpm.id_penilaian_matriks AND pm.kategori_penilaian = '". PenilaianMatriks::CATEGORY_INDICATOR . "'
                JOIN spmi.penilaian_panduan pp2 ON pp2.id = pm.id_penilaian_panduan
                WHERE pm.waktu_dihapus is null
                GROUP BY pp2.id, mpm.id_audit_periode, mpm.id_unit
            ) ppp ON ppp.id = jau.id_penilaian_panduan AND ppp.id_audit_periode = ja.id_audit_periode AND ppp.id_unit = jau.id_unit
            LEFT JOIN spmi.penilaian_audit pa ON
                pa.id_audit_periode = ap.id AND
                pa.id_unit = uk.id AND
                pa.id_penilaian_panduan = pp.id AND
                pa.id_jadwal_audit = ja.id AND
                pa.apakah_penilaian_mandiri = false
            JOIN spmi.pengisian_panduan fg ON fg.id = jau.id_pengisian_panduan AND fg.apakah_aktif = true AND fg.tipe_edisi = '" . AkreditasiBuku::PERFORMANCE_REPORT . "'
            JOIN core.lembaga_akreditasi la ON la.id = fg.id_lembaga_akreditasi
            LEFT JOIN (
                SELECT
                    psk.id_penilaian_audit,
                    1 apakah_punya_feedback
                FROM spmi.penilaian_skor psk
                WHERE psk.catatan_penilaian IS NOT NULL AND psk.catatan_penilaian != ''
                GROUP BY (psk.id_penilaian_audit)
            ) ps ON ps.id_penilaian_audit = pa.id
            LEFT JOIN (
                SELECT
                    pa2.id id_penilaian_audit,
                    count(*) total_indikator_terisi
                FROM spmi.penilaian_audit pa2
                JOIN spmi.mapping_penilaian_matriks mpm ON mpm.id_audit_periode = pa2.id_audit_periode AND mpm.id_unit = pa2.id_unit
                JOIN spmi.penilaian_matriks pm ON pm.id = mpm.id_penilaian_matriks AND pm.id_penilaian_panduan = pa2.id_penilaian_panduan AND pm.kategori_penilaian = '". PenilaianMatriks::CATEGORY_INDICATOR . "'
                JOIN spmi.penilaian_skor psk ON psk.id_penilaian_audit = pa2.id AND psk.id_penilaian_matriks = pm.id
                WHERE pm.waktu_dihapus IS NULL
                GROUP BY pa2.id
            ) ps2 ON ps2.id_penilaian_audit = pa.id
            JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = ja.id_audit_periode
                AND sa.waktu_dihapus IS NULL
            JOIN (
                spmi.surat_tugas_auditor_pegawai stap
                JOIN core.biodata lp ON lp.id = stap.id_personil
                    AND lp.waktu_dihapus IS NULL
            ) ON stap.id_unit = uk.id
                AND stap.posisi = ?
                AND stap.id_surat_tugas_auditor = sa.id
                AND stap.waktu_dihapus IS null";

        $checkValidSuratTugas =
            "AND (
                SELECT
                    stp.id
                FROM spmi.surat_tugas_auditor_pegawai stp
                JOIN core.biodata p ON p.id = stp.id_personil AND p.waktu_dihapus is null
                WHERE stp.id_unit = uk.id
                    and p.ref_key_pegawai = ?
                    AND stp.posisi IN (?,?)
                    AND stp.id_surat_tugas_auditor = sa.id
                LIMIT 1
            ) IS NOT NULL";

        $fieldMap = [
            'id_unit_kerja' => 'uk.id',
            'periode_audit' => 'ap.tahun_audit',
            'nama_unit' => 'uk.nama_unit',
            'nama_penilaian_panduan' => 'pp.nama_singkat',
            'id_penilaian_panduan' => 'pp.id',
            'id_jenjang_pendidikan' => 'jp.id',
            'status_penilaian' => 'total_penilaian_terisi'
        ];

        $typeStudyProgram = [UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI];
        $typeStudyProgram = implode(',', $typeStudyProgram);
        $typeStudyProgram = "'" . str_replace(',', "','", $typeStudyProgram) . "'";

        $defaultFilter = "uk.waktu_dihapus is null AND uk.jenis_unit IN ($typeStudyProgram)";

        $bindings = [(int) $auditPeriodId, SuratTugasAuditorPegawai::POSITION_LEAD];
        $userRole = auth()->user()->kode_role;

        $isInternalRole = SessionManager::isInternalRole();
        if (($userRole === Role::ROLE_AUDITOR || $userRole === Role::ROLE_AUDITEE) && !$isInternalRole) {
            $defaultFilter .= " $checkValidSuratTugas";
            $bindings[] = session('token.idpegawai');

            if ($userRole == Role::ROLE_AUDITEE) {
                $bindings[] = SuratTugasAuditorPegawai::POSITION_AUDITEE;
                $bindings[] = SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE;
            } else {
                $bindings[] = SuratTugasAuditorPegawai::POSITION_LEAD;
                $bindings[] = SuratTugasAuditorPegawai::POSITION_MEMBER;
            }
        }

        $userUnit = session()->get('user.unit_kerja');
        if (!empty($userUnit) && ($userRole !== Role::ROLE_AUDITOR && $userRole !== Role::ROLE_AUDITEE) && !$isInternalRole) {
            $unitKerjaServices = new UnitKerjaManagementService();
            $ids = array_keys($unitKerjaServices->getUnitAncestors($userUnit->id));
            if (!empty($ids)) {
                $defaultFilter .= " AND uk.id IN (" . implode(',', $ids) . ")";
            } else {
                $defaultFilter .= " AND uk.id = 0"; // pastikan tidak ada data yang ditampilkan
            }
        }

        [$sql, $bindings] = Pagination::buildQuery(
            bindings: $bindings,
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
            groupBy: 'uk.id, ap.id, la.id, pp.id, pa.id, ps.apakah_punya_feedback, jp.kode_jenjang, ps2.total_indikator_terisi, ja.id, ja.nama_jadwal_audit',
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    public function showInformationByStudyProgram(int $studyProgramId, array|null $periodData, int|null $idJadwalAudit = null): array|Error
    {
        if (!isset($periodData)) {
            return new Error('Periode audit tidak ditemukan', 404);
        }

        $sql = "SELECT
                    concat(jp.kode_jenjang, ' - ', uk.nama_unit) as nama_unit,
                    la.nama_singkat_lembaga nama_lembaga_akreditasi,
                    pp.nama_penilaian_panduan,
                    uk.id id_unit,
                    la.id id_lembaga_akreditasi,
                    pp.id id_penilaian_panduan,
                    lp.id id_ketua_auditor,
                    lp.nama nama_ketua_auditor,
                    lp.gelar_depan gelar_depan_ketua_auditor,
                    lp.gelar_belakang gelar_belakang_ketua_auditor,
                    ja.id id_jadwal_audit,
                    ja.nama_jadwal_audit,
                    sa.id id_surat_tugas_auditor,
                    ja.tanggal_awal_penilaian,
                    ja.tanggal_akhir_penilaian,
                    ja.apakah_penilaian_mandiri
                from core.unit_kerja uk
                left join core.jenjang_pendidikan jp on
                    jp.id = uk.id_jenjang_pendidikan
                join spmi.jadwal_audit ja on
                    ja.id_audit_periode = :id_audit_periode
                    and ja.waktu_dihapus is null
                join spmi.jadwal_audit_unit jau on
                    jau.id_unit = uk.id
                    and jau.id_jadwal_audit = ja.id
                JOIN spmi.pengisian_panduan fg ON fg.id = jau.id_pengisian_panduan AND fg.apakah_aktif = true AND fg.tipe_edisi = '" . AkreditasiBuku::PERFORMANCE_REPORT . "'
                join core.lembaga_akreditasi la on
                    la.id = fg.id_lembaga_akreditasi
                join spmi.penilaian_panduan pp on
                    pp.id = jau.id_penilaian_panduan
                    and pp.apakah_aktif = true
                    and pp.waktu_dihapus is null
                join spmi.surat_tugas_auditor sa on
                    sa.id_audit_periode = ja.id_audit_periode
                    and sa.waktu_dihapus is null
                    and sa.waktu_dihapus is null
                left join (
                                    spmi.surat_tugas_auditor_pegawai stap
                join core.biodata lp on
                    lp.id = stap.id_personil
                                ) on
                    stap.id_unit = uk.id
                    and stap.posisi = :posisi_ketua_auditor
                    and stap.id_surat_tugas_auditor = sa.id
                    and stap.waktu_dihapus is null
                where uk.id = :id_unit
                and uk.waktu_dihapus is null";

        if (!empty($idJadwalAudit)) {
            $sql = str_replace('ja.id_audit_periode = :id_audit_periode', 'ja.id_audit_periode = :id_audit_periode AND ja.id = :id_jadwal_audit', $sql);
        }

        $checkValidAuditor =
            "AND (
                SELECT
                    stp.id
                FROM spmi.surat_tugas_auditor_pegawai stp
                JOIN core.biodata p ON p.id = stp.id_personil AND p.waktu_dihapus is null
                WHERE stp.id_unit = uk.id
                    and p.ref_key_pegawai = :id_pegawai
                    AND stp.posisi IN (:posisi_ketua, :posisi_member)
                    AND stp.id_surat_tugas_auditor = sa.id
                LIMIT 1
            ) IS NOT NULL";

        $bindings = [
            'id_unit' => $studyProgramId,
            'id_audit_periode' => $periodData['id'],
            'posisi_ketua_auditor' => SuratTugasAuditorPegawai::POSITION_LEAD
        ];

        if (!empty($idJadwalAudit)) {
            $bindings['id_jadwal_audit'] = $idJadwalAudit;
        }

        $userRole = auth()->user()->kode_role;
        $isInternalRole = SessionManager::isInternalRole();
        if (($userRole === Role::ROLE_AUDITOR || $userRole === Role::ROLE_AUDITEE) && !$isInternalRole) {
            $sql .= " $checkValidAuditor";
            $bindings['id_pegawai'] = session('token.idpegawai');
            if ($userRole == Role::ROLE_AUDITEE) {
                $bindings['posisi_ketua'] = SuratTugasAuditorPegawai::POSITION_AUDITEE;
                $bindings['posisi_member'] = SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE;
            } else {
                $bindings['posisi_ketua'] = SuratTugasAuditorPegawai::POSITION_LEAD;
                $bindings['posisi_member'] = SuratTugasAuditorPegawai::POSITION_MEMBER;
            }
        }

        $userUnit = session()->get('user.unit_kerja');

        if (!empty($userUnit) && ($userRole !== Role::ROLE_AUDITOR && $userRole !== Role::ROLE_AUDITEE) && !$isInternalRole) {
            $unitKerjaServices = new UnitKerjaManagementService();
            $ids = array_keys($unitKerjaServices->getUnitAncestors($userUnit->id));
            if (!empty($ids)) {
                $sql .= " AND uk.id IN (" . implode(',', $ids) . ")";
            } else {
                $sql .= " AND uk.id = 0"; // pastikan tidak ada data yang ditampilkan
            }
        }

        $sql .= " LIMIT 1";

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

        $suratTugasAuditor = SuratTugasAuditor::find($data['id_surat_tugas_auditor']);
        $suratTugasAuditorAnggota = SuratTugasAuditorPegawai::with('biodata')
            ->where('posisi', SuratTugasAuditorPegawai::POSITION_MEMBER)
            ->where('id_surat_tugas_auditor', $suratTugasAuditor->id)
            ->where('id_unit', $data['id_unit'])
            ->get();

        $tahunPeriode = (int) $periodData['tahun_audit'];
        $information = [
            'nama_jadwal_audit' => $data['nama_jadwal_audit'],
            'periode_audit' => $tahunPeriode,
            'periode_akademik' => ($tahunPeriode - 1) . '/' . $tahunPeriode,
            'nama_unit' => $data['nama_unit'],
            'nama_lembaga_akreditasi' => $data['nama_lembaga_akreditasi'],
            'nama_penilaian_panduan' => $data['nama_penilaian_panduan'],
            'tanggal_penilaian' => Carbon::parse($data['tanggal_awal_penilaian'])->translatedFormat('d F Y')
                . ' s.d. ' . Carbon::parse($data['tanggal_akhir_penilaian'])->translatedFormat('d F Y'),
            'ketua_auditor' => $data['gelar_depan_ketua_auditor'] . ' ' . $data['nama_ketua_auditor'] . ' ' . $data['gelar_belakang_ketua_auditor'],
            'anggota_auditor' => $suratTugasAuditorAnggota->count() > 0 ? $suratTugasAuditorAnggota->map(function ($item) {
                    return trim(
                        ($item->biodata->gelar_depan ?? '') . ' ' .
                        $item->biodata->nama . ' ' .
                        ($item->biodata->gelar_belakang ?? '')
                    );
                })->implode('<br>') : '-',
            'nilai_akhir' => '-',
        ];

        $raw = [
            'id_jadwal_audit' => $data['id_jadwal_audit'],
            'id_audit_periode' => $periodData['id'],
            'id_unit' => $data['id_unit'],
            'id_lembaga_akreditasi' => $data['id_lembaga_akreditasi'],
            'id_penilaian_panduan' => $data['id_penilaian_panduan'],
            'id_ketua_auditor' => $data['id_ketua_auditor'],
            'tanggal_awal_penilaian' => $data['tanggal_awal_penilaian'],
            'tanggal_akhir_penilaian' => $data['tanggal_akhir_penilaian'],
            'apakah_penilaian_mandiri' => $data['apakah_penilaian_mandiri'],
        ];

        return [
            $information,
            $raw
        ];
    }

    public function showInformation(int $id): array|Error
    {
        $sql = "SELECT
                    concat(d.kode_jenjang, ' - ', o.nama_unit) as nama_unit,
                    aa.nama_singkat_lembaga nama_lembaga_akreditasi,
                    ag.nama_penilaian_panduan,
                    o.id id_unit,
                    aa.id id_lembaga_akreditasi,
                    ag.id id_penilaian_panduan,
                    lp.id id_ketua_auditor,
                    lp.nama nama_ketua_auditor,
                    lp.gelar_depan gelar_depan_ketua_auditor,
                    lp.gelar_belakang gelar_belakang_ketua_auditor,
                    asch.id id_jadwal_audit,
                    asch.nama_jadwal_audit,
                    sa.id id_surat_tugas_auditor,
                    asch.tanggal_awal_penilaian,
                    asch.tanggal_akhir_penilaian,
                    asch.apakah_penilaian_mandiri,
                    case when ag.apakah_menggunakan_peringkat then coalesce(haa.persentase_nilai_akhir::text, '-') else coalesce(haa.nilai_akhir::text, '-') end as nilai_akhir,
                    sa.id_audit_periode
                FROM spmi.penilaian_audit a
                JOIN core.unit_kerja o ON o.id = a.id_unit
                    AND o.waktu_dihapus is null
                LEFT JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
                LEFT JOIN spmi.hasil_akhir_audit haa ON haa.id_penilaian_audit = a.id
                JOIN spmi.penilaian_panduan ag ON ag.id = a.id_penilaian_panduan
                    AND ag.apakah_aktif = true
                    AND ag.waktu_dihapus is null
                JOIN spmi.jadwal_audit_unit aso ON aso.id_unit = o.id
                    AND aso.id_penilaian_panduan = ag.id
                JOIN spmi.jadwal_audit asch ON asch.id = aso.id_jadwal_audit
                    AND asch.id_audit_periode = a.id_audit_periode
                    AND (a.id_jadwal_audit IS NULL OR asch.id = a.id_jadwal_audit)
                    AND asch.waktu_dihapus is null
                JOIN spmi.pengisian_panduan fg ON fg.id = aso.id_pengisian_panduan AND fg.apakah_aktif = true AND fg.tipe_edisi = '" . AkreditasiBuku::PERFORMANCE_REPORT . "'
                JOIN core.lembaga_akreditasi aa ON aa.id = fg.id_lembaga_akreditasi
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
                WHERE a.id = :id AND a.waktu_dihapus is null";

        $checkValidAuditor =
            "AND (
                SELECT
                    stp.id
                FROM spmi.surat_tugas_auditor_pegawai stp
                JOIN core.biodata p ON p.id = stp.id_personil AND p.waktu_dihapus is null
                WHERE stp.id_unit = o.id
                    and p.ref_key_pegawai = :id_pegawai
                    AND stp.posisi IN (:posisi_ketua, :posisi_member)
                    AND stp.id_surat_tugas_auditor = sa.id
                LIMIT 1
            ) IS NOT NULL";

        $bindings = [
            'id' => $id,
            'posisi_ketua_auditor' => SuratTugasAuditorPegawai::POSITION_LEAD
        ];

        $userRole = auth()->user()->kode_role;
        $isInternalRole = SessionManager::isInternalRole();
        if (($userRole === Role::ROLE_AUDITOR || $userRole === Role::ROLE_AUDITEE) && !$isInternalRole) {
            $sql .= " $checkValidAuditor";
            $bindings['id_pegawai'] = session('token.idpegawai');

            if ($userRole == Role::ROLE_AUDITEE) {
                $bindings['posisi_ketua'] = SuratTugasAuditorPegawai::POSITION_AUDITEE;
                $bindings['posisi_member'] = SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE;
            } else {
                $bindings['posisi_ketua'] = SuratTugasAuditorPegawai::POSITION_LEAD;
                $bindings['posisi_member'] = SuratTugasAuditorPegawai::POSITION_MEMBER;
            }
        }

        $userUnit = session()->get('user.unit_kerja');

        if (!empty($userUnit) && ($userRole !== Role::ROLE_AUDITOR && $userRole !== Role::ROLE_AUDITEE) && !$isInternalRole) {
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

        $periodData = AuditPeriode::find($data['id_audit_periode']);

        $suratTugasAuditor = SuratTugasAuditor::find($data['id_surat_tugas_auditor']);
        $suratTugasAuditorAnggota = SuratTugasAuditorPegawai::with('biodata')
            ->where('posisi', SuratTugasAuditorPegawai::POSITION_MEMBER)
            ->where('id_surat_tugas_auditor', $suratTugasAuditor->id)
            ->where('id_unit', $data['id_unit'])
            ->get();

            $tahunPeriode = (int) $periodData['tahun_audit'];
        $information = [
            'nama_jadwal_audit' => $data['nama_jadwal_audit'],
            'periode_audit' => $tahunPeriode,
            'periode_akademik' => ($tahunPeriode - 1) . '/' . $tahunPeriode,
            'nama_unit' => $data['nama_unit'],
            'nama_lembaga_akreditasi' => $data['nama_lembaga_akreditasi'],
            'nama_penilaian_panduan' => $data['nama_penilaian_panduan'],
            'tanggal_penilaian' => Carbon::parse($data['tanggal_awal_penilaian'])->translatedFormat('d F Y')
                . ' s.d. ' . Carbon::parse($data['tanggal_akhir_penilaian'])->translatedFormat('d F Y'),
            'ketua_auditor' => $data['gelar_depan_ketua_auditor'] . ' ' . $data['nama_ketua_auditor'] . ' ' . $data['gelar_belakang_ketua_auditor'],
            'anggota_auditor' => $suratTugasAuditorAnggota->count() > 0 ? $suratTugasAuditorAnggota->map(function ($item) {
                    return trim(
                        ($item->biodata->gelar_depan ?? '') . ' ' .
                        $item->biodata->nama . ' ' .
                        ($item->biodata->gelar_belakang ?? '')
                    );
                })->implode('<br>') : '-',
            'nilai_akhir' => $data['nilai_akhir'],
        ];

        $raw = [
            'id_jadwal_audit' => $data['id_jadwal_audit'],
            'id_audit_periode' => $data['id_audit_periode'],
            'id_unit' => $data['id_unit'],
            'id_lembaga_akreditasi' => $data['id_lembaga_akreditasi'],
            'id_penilaian_panduan' => $data['id_penilaian_panduan'],
            'id_ketua_auditor' => $data['id_ketua_auditor'],
            'tanggal_awal_penilaian' => $data['tanggal_awal_penilaian'],
            'tanggal_akhir_penilaian' => $data['tanggal_akhir_penilaian'],
            'apakah_penilaian_mandiri' => $data['apakah_penilaian_mandiri'],
        ];

        return [
            $information,
            $raw
        ];
    }

    public function showScore($assessmentId, $idMatriksPenilaian): array
    {
        return PenilaianSkor::where('id_penilaian_audit', $assessmentId)
            ->where('id_penilaian_matriks', $idMatriksPenilaian)
            ->first([
                'nilai',
                'status_penilaian',
                'catatan_penilaian',
                'nilai_default',
                'id_penilaian_matriks',
                'id_predikat_matriks_penilaian'
            ])
            ?->toArray() ?? [];
    }

    public static function getAuditorMember($assessmentId)
    {
        $sql = "SELECT
                    p.id,
                    p.nama,
                    p.gelar_depan,
                    p.gelar_belakang,
                    stp.posisi,
                    CASE WHEN stp.posisi = :posisi_ketua THEN true ELSE false END apakah_ketua_auditor,
                    CASE WHEN stp.posisi = :posisi_auditee THEN true ELSE false END apakah_auditee
                FROM spmi.penilaian_audit a
                JOIN core.unit_kerja o ON o.id = a.id_unit
                    AND o.waktu_dihapus is null
                JOIN spmi.jadwal_audit_unit aso ON aso.id_unit = o.id
                JOIN spmi.jadwal_audit asch ON asch.id = aso.id_jadwal_audit
                    AND asch.id_audit_periode = a.id_audit_periode
                    AND asch.waktu_dihapus is null
                JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = asch.id_audit_periode
                    AND sa.waktu_dihapus is null
                JOIN spmi.surat_tugas_auditor_pegawai stp ON stp.id_surat_tugas_auditor = sa.id
                    AND stp.id_unit = o.id
                    AND stp.waktu_dihapus is null
                JOIN core.biodata p ON p.id = stp.id_personil
                    AND p.waktu_dihapus is null
                WHERE a.id = :id_penilaian_audit
                    AND a.waktu_dihapus is null";

        $data = DB::select($sql, [
            'id_penilaian_audit' => $assessmentId,
            'posisi_ketua' => SuratTugasAuditorPegawai::POSITION_LEAD,
            'posisi_auditee' => SuratTugasAuditorPegawai::POSITION_AUDITEE
        ]);

        return $data;
    }

    public function getReferenceIndicator($idMatriksPenilaian, $studyProgramId, $auditPeriodId)
    {
        $sql = "SELECT DISTINCT ON (pmr.id, pmr.jenis_referensi)
                CASE
                    WHEN pmr.jenis_referensi = '".AkreditasiBuku::SELF_EVALUATION."' THEN se.id
                    ELSE pr.id
                END AS id,
                CASE
                    WHEN fp.apakah_data_default = pp.apakah_data_default
                    AND (
                        (
                            pmr.jenis_referensi = '".AkreditasiBuku::PERFORMANCE_REPORT."'
                            AND EXISTS (
                                SELECT 1
                                FROM spmi.jadwal_audit ja
                                JOIN spmi.jadwal_audit_unit jau
                                    ON jau.id_jadwal_audit = ja.id
                                AND jau.id_unit = fi.id_unit
                                AND jau.id_pengisian_panduan = fi.id_pengisian_panduan
                                WHERE ja.id_audit_periode = fi.id_audit_periode
                            )
                        )
                        OR
                        (
                            pmr.jenis_referensi = '".AkreditasiBuku::SELF_EVALUATION."'
                            AND EXISTS (
                                SELECT 1
                                FROM spmi.jadwal_audit ja
                                JOIN spmi.jadwal_audit_unit jau
                                    ON jau.id_jadwal_audit = ja.id
                                AND jau.id_unit = fi.id_unit
                                JOIN spmi.pengisian_panduan pp_lk
                                    ON pp_lk.id = jau.id_pengisian_panduan
                                AND pp_lk.waktu_dihapus IS NULL
                                WHERE ja.id_audit_periode = fi.id_audit_periode
                                AND pp_lk.id_pengisian_panduan = fi.id_pengisian_panduan
                            )
                        )
                    )
                    THEN fi.id
                    ELSE NULL
                END AS id_indikator_pengisian,
                CASE
                    WHEN pmr.jenis_referensi = '".AkreditasiBuku::SELF_EVALUATION."'
                        THEN se.nama_indikator_evaluasi_diri
                    ELSE pr.nama_indikator_laporan_kinerja
                END AS name,
                pmr.jenis_referensi
            FROM spmi.penilaian_matriks_referensi pmr
            JOIN spmi.penilaian_matriks pm
                ON pm.id = pmr.id_penilaian_matriks
            AND pm.waktu_dihapus IS NULL
            JOIN spmi.penilaian_panduan pp
                ON pp.id = pm.id_penilaian_panduan
            AND pp.waktu_dihapus IS NULL
            LEFT JOIN spmi.indikator_evaluasi_diri se
                ON se.id = pmr.id_butir_referensi
            AND se.waktu_dihapus IS NULL
            LEFT JOIN spmi.indikator_laporan_kinerja pr
                ON pr.id = pmr.id_butir_referensi
            AND pr.waktu_dihapus IS NULL
            LEFT JOIN spmi.pengisian_indikator fi
                ON fi.id_audit_periode = :id_audit_periode
            AND fi.id_unit = :id_unit
            AND fi.jenis_indikator = pmr.jenis_referensi
            AND fi.waktu_dihapus IS NULL
            LEFT JOIN spmi.pengisian_panduan fp
                ON fp.id = fi.id_pengisian_panduan
            AND fp.waktu_dihapus IS NULL
            WHERE pmr.id_penilaian_matriks = :id_penilaian_matriks
            ORDER BY pmr.id, pmr.jenis_referensi, fi.id DESC";

        $data = DB::select($sql, [
            'id_unit' => $studyProgramId,
            'id_audit_periode' => $auditPeriodId,
            'id_penilaian_matriks' => $idMatriksPenilaian
        ]);

        $data = array_map(function ($item) {
            $item = (array) $item;
            $routeName = $item['jenis_referensi'] == AkreditasiBuku::PERFORMANCE_REPORT ? 'pengisian-indikator' : 'pengisian-indikator-led';
            $param = $item['jenis_referensi'] == AkreditasiBuku::PERFORMANCE_REPORT ? 'id_indikator_laporan_kinerja' : 'id_indikator_evaluasi_diri';

            $item['link'] = "#";
            if (isset($item['id_indikator_pengisian'])) {
                $item['link'] = url(
                    'spmi/' . $routeName . '/' . $item['id_indikator_pengisian'] . '/edit' . '?' . $param . '=' . $item['id']
                );
            }
            return (array) $item;
        }, $data);

        return $data;
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return PenilaianAudit
     */
    public function show(int $id): PenilaianAudit
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return PenilaianAudit|Error
     */
    public function store(array $data): PenilaianAudit|Error
    {
        try {
            return $this->model->firstOrCreate(
                Arr::only($data, ['id_unit', 'id_audit_periode']),
                $data
            );
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
     * @return PenilaianAudit
     */
    public function update(array $data, int $id): PenilaianAudit|Error
    {
        $model = $this->model->findOrFail($id);

        try {
            $model->update($data);
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }

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
     * Menampilkan semua matrix berdasarkan assessment guide. Untuk kebutuhan penilaian
     *
     * @param int $assessmentGuideId
     * @return mixed
     */
    public function showAllMatricesByPenilaianPanduan(int $assessmentGuideId, int $studyProgramId, int $auditPeriodId, int|null $assessmentId = null, int|null $idJadwalAudit = null): mixed
    {
        $targetJadwalFilter = '';
        if (!empty($idJadwalAudit)) {
            $targetJadwalFilter = 'AND (at.id_jadwal_audit IS NULL OR at.id_jadwal_audit = :id_jadwal_audit)';
        }

        $sql =
            "SELECT
                pm.id,
                pm.nomor_penilaian,
                pm.pertanyaan_penilaian,
                pm.kategori_penilaian,
                pm.deskripsi,
                pm.jenis_penilaian,
                pm.bobot_penilaian,
                pm.rumus_penilaian,
                pm.id_parent,
                pm.info_level,
                pm.apakah_data_default,
                sc.nilai nilai_auditor,
                CASE WHEN asch.apakah_penilaian_mandiri = true
                    THEN scu.nilai
                END nilai_auditee,
                sc.catatan_penilaian,
                ats.nilai nilai_target,
                sc.nilai_akhir,
                sc.status_penilaian status_penilaian_auditor,
                sc.id_predikat_matriks_penilaian,
                sc.nilai_default,
                pm.info_left
            FROM spmi.penilaian_matriks pm
            JOIN core.unit_kerja o ON o.id = :id_unit
                AND o.waktu_dihapus is null
            LEFT JOIN spmi.target_indikator at ON at.id_penilaian_panduan = pm.id_penilaian_panduan
                AND at.id_audit_periode = :id_audit_periode
                AND at.id_unit = o.id
                {$targetJadwalFilter}
                AND at.waktu_dihapus is null
            LEFT JOIN spmi.target_skor ats ON ats.id_target_indikator = at.id
                AND ats.id_penilaian_matriks = pm.id
            LEFT JOIN spmi.penilaian_audit aa ON aa.id = :id_penilaian_audit
                AND aa.id_audit_periode = at.id_audit_periode
                AND aa.id_unit = at.id_unit
                AND aa.apakah_penilaian_mandiri = false
                AND at.waktu_dihapus is null
            LEFT JOIN spmi.penilaian_skor sc ON sc.id_penilaian_audit = aa.id
                AND sc.id_penilaian_matriks = pm.id
            LEFT JOIN spmi.penilaian_audit aau ON aau.id_audit_periode = at.id_audit_periode
                AND aau.id_unit = at.id_unit
                AND aau.id_penilaian_panduan = at.id_penilaian_panduan
                AND aau.apakah_penilaian_mandiri = true
                AND (aa.id_jadwal_audit IS NULL OR aau.id_jadwal_audit = aa.id_jadwal_audit)
                AND aau.waktu_dihapus is null
            LEFT JOIN (
                spmi.jadwal_audit_unit aso
                JOIN spmi.jadwal_audit asch ON asch.id = aso.id_jadwal_audit
            ) ON aso.id_unit = o.id
                AND asch.id_audit_periode = at.id_audit_periode
                AND asch.apakah_penilaian_mandiri = true
                AND (aa.id_jadwal_audit IS NULL OR asch.id = aa.id_jadwal_audit)
                AND asch.waktu_dihapus is null
            LEFT JOIN spmi.penilaian_skor scu ON scu.id_penilaian_audit = aau.id AND scu.id_penilaian_matriks = pm.id
            JOIN spmi.mapping_penilaian_matriks mpm ON mpm.id_penilaian_matriks = pm.id
                AND mpm.id_audit_periode = :id_audit_periode
                AND mpm.id_unit = o.id
            WHERE pm.waktu_dihapus is null AND pm.id_penilaian_panduan = :id_penilaian_panduan
            ORDER BY pm.info_left ASC";

        $bindings = [
            'id_penilaian_panduan' => $assessmentGuideId,
            'id_audit_periode' => $auditPeriodId,
            'id_unit' => $studyProgramId,
            'id_penilaian_audit' => $assessmentId,
        ];

        if (!empty($idJadwalAudit)) {
            $bindings['id_jadwal_audit'] = $idJadwalAudit;
        }

        $data = DB::select($sql, $bindings);
        return $data;
    }

    public function showTargetIndikatorStatus(int $idPenilaianPanduan, int $studyProgramId, int $auditPeriodId, int|null $idJadwalAudit = null): array
    {
        $sql = "SELECT
                    at.id,
                    at.apakah_terfinalisasi,
                    COUNT(ats.id) total_target_terisi
                FROM spmi.target_indikator at
                JOIN core.unit_kerja o ON o.id = at.id_unit
                LEFT JOIN spmi.target_skor ats ON ats.id_target_indikator = at.id
                    AND ats.id_target_indikator = at.id
                WHERE at.id_audit_periode = :id_audit_periode AND o.id = :id_unit AND at.id_penilaian_panduan = :id_penilaian_panduan
                    AND at.waktu_dihapus is null";

        $bindings = [
            'id_penilaian_panduan' => $idPenilaianPanduan,
            'id_audit_periode' => $auditPeriodId,
            'id_unit' => $studyProgramId
        ];

        if (!empty($idJadwalAudit)) {
            $sql .= " AND (at.id_jadwal_audit IS NULL OR at.id_jadwal_audit = :id_jadwal_audit)";
            $bindings['id_jadwal_audit'] = $idJadwalAudit;
        }

        $sql .= " GROUP BY at.id, at.apakah_terfinalisasi LIMIT 1";

        $data = DB::select($sql, $bindings);

        $data = (array) ($data[0] ?? null);

        if (empty($data)) {
            return [];
        }

        return $data;
    }

    public function finalize($auditPeriodId, $assessmentId, $studyProgramId)
    {
        $assessment = PenilaianAudit::find($assessmentId);

        if (!$assessment) {
            return new Error('Penilaian audit tidak ditemukan', 404);
        }

        DB::beginTransaction();

        $indicatorPercentageIKU = (float) IndikatorBobot::where('id_audit_periode', $auditPeriodId)
            ->where('id_unit', $studyProgramId)
            ->where('id_penilaian_panduan', $assessment->id_penilaian_panduan)
            ->where('jenis_indikator_bobot', IndikatorBobot::TYPE_IKU)
            ->first('persentase')?->persentase ?? 0;

        $indicatorPercentageIKT = (float) IndikatorBobot::where('id_audit_periode', $auditPeriodId)
            ->where('id_unit', $studyProgramId)
            ->where('id_penilaian_panduan', $assessment->id_penilaian_panduan)
            ->where('jenis_indikator_bobot', IndikatorBobot::TYPE_IKT)
            ->first('persentase')?->persentase ?? 0;

        $totalPercentage = $indicatorPercentageIKU + $indicatorPercentageIKT;

        //error msg berdasarkan role
        $msg = 'Persentase bobot IKU dan IKT belum diatur.';
        $isAdminPenjaminanMutu = auth()->user()?->kode_role === Role::ROLE_ADMIN_PENJAMINAN_MUTU;

        if (SessionManager::isInternalRole() || $isAdminPenjaminanMutu){
            $msg .= ' Silakan atur <a href="' . url('spmi/setting-indikator-bobot') . '"><b>disini</b></a>';
        }

        if ($totalPercentage == 0) {
            return new Error($msg, 500);
        }

        $penilaianPanduan = PenilaianPanduan::find($assessment->id_penilaian_panduan);
        $apakahDataDefault = $penilaianPanduan->apakah_data_default;
        $isHasButirSPME = PenilaianMatriks::where('id_penilaian_panduan', $assessment->id_penilaian_panduan)
            ->where('kategori_penilaian', PenilaianMatriks::CATEGORY_INDICATOR)
            ->where('butir_indikator_spme', true)
            ->exists();

        // Skor Auditee
        $auditeeAssessment = $this->showAuditeeAssessment($assessment->id_penilaian_panduan, $studyProgramId, $auditPeriodId, $assessment->id_jadwal_audit);

        // Pemeringkatan Skor Auditor
        $finalScoreIKU = $this->getFinalPenilaianSkor($assessmentId, IndikatorBobot::TYPE_IKU);
        $finalScoreIKT = $this->getFinalPenilaianSkor($assessmentId, IndikatorBobot::TYPE_IKT);

        $compositionTargetAll = $this->compositionScoreTarget($assessmentId);

        // HARCODED untuk akreditasi S1 Akre IAPS 5.1
        if (in_array($penilaianPanduan->kode_penilaian_panduan, [
            'IAPS5.1-S1-Akre',
            'IAPS5.1-S1-Ung',
            'IAPS5.1-S2-Akre',
            'IAPS5.1-S2-Ung',
        ])) {
            $finalScoreIKU = (int) $finalScoreIKU;
            $finalScoreIKT = (int) $finalScoreIKT;
            $maxNilaiIKU = MappingPenilaianMatriks::where('id_audit_periode', $auditPeriodId)
                ->where('id_unit', $studyProgramId)
                ->whereHas('penilaianMatriks', function ($query) use ($assessment) {
                    $query->where('id_penilaian_panduan', $assessment->id_penilaian_panduan)
                        ->where('kategori_penilaian', PenilaianMatriks::CATEGORY_INDICATOR)
                        ->where('apakah_data_default', true);
                })
                ->count();
            $isTerakreditasi = ($finalScoreIKU / $maxNilaiIKU) * 100 >= 80; // 80%

            // Rank score: bila bukan default, murni dari IKT
            $rankScore = $finalScoreIKU + $finalScoreIKT;
            $rankScore = (float) number_format($rankScore, 2, '.', ',');

            // Ambil rentang peringkat
            $qualityRates = SpmiPeringkat::where('id_audit_periode', $auditPeriodId)
                ->whereNull('waktu_dihapus')
                ->orderBy('skor_minimal', 'asc')
                ->get();

            // Ambil Peringkat
            $tempRankScore = round($rankScore);
            $rank = $qualityRates->first(function ($item) use ($tempRankScore) {
                return $tempRankScore >= $item->skor_minimal && $tempRankScore <= $item->skor_maksimal;
            });

            if (empty($rank)) {
                return new Error('Tidak ada peringkat yang sesuai, skor AMI Anda adalah: ' . $rankScore, 500);
            }

            $akreditasiStatus = $this->generateAkreditasiStatus(
                $tempRankScore,
                $assessment->id_penilaian_panduan
            );

            if (empty($akreditasiStatus) && $isHasButirSPME) {
                return new Error('Tidak ada peringkat SPME yang sesuai, skor SPME Anda adalah: ' . $finalScoreIKU, 500);
            }

            // Simpan hasil akhir (nilai_akhir: jika bukan default, hanya IKT)
            HasilAkhirAudit::updateOrCreate([
                'id_audit_periode'    => $auditPeriodId,
                'id_unit'             => $studyProgramId,
                'id_penilaian_audit'  => $assessmentId,
            ], [
                'id_audit_periode'        => $auditPeriodId,
                'id_unit'                 => $studyProgramId,
                'id_penilaian_audit'      => $assessmentId,
                'nilai_iku'               => $finalScoreIKU,
                'nilai_ikt'               => $finalScoreIKT,
                'nilai_akhir'             => ($finalScoreIKU + $finalScoreIKT),
                'nilai_akhir_auditee'     => $auditeeAssessment?->nilai_akhir,
                'persentase_nilai_akhir'  => ($finalScoreIKU / $maxNilaiIKU) * 100,
                'persentase_nilai_target' => ($finalScoreIKU + $finalScoreIKT) / ($compositionTargetAll['max_nilai_target'] ?? 1) * 100,
                'apakah_status_terakreditasi' => $isTerakreditasi,
                'id_spmi_peringkat'       => $rank->id,
                'id_status_peringkat'     => $akreditasiStatus?->id,
            ]);

            // Ambil penilaian panduan, untuk mendapatkan total matriks
            $penilaianPanduan = PenilaianPanduan::find($assessment->id_penilaian_panduan);

            if (empty($penilaianPanduan)) {
                return new Error('Penilaian Panduan tidak ditemukan', 500);
            }

            // Update status penilaian sudah di finalisasi
            $assessment->update([
                'apakah_terfinalisasi'    => true,
                'total_indikator_matriks'  => $penilaianPanduan->total_indikator_matriks,
            ]);

            $revalidateTemuan = (new AuditTemuanManagementService)->revalidateTemuan($assessmentId);

            if ($revalidateTemuan instanceof Error) {
                DB::rollBack();
                return $revalidateTemuan;
            }
            DB::commit();

            return $assessment;
        }

        // DYNAMIC FINAL SCORE CALCULATION
        $totalFinalScoreIKU = $finalScoreIKU * ($indicatorPercentageIKU / 100);
        $totalFinalScoreIKT = $finalScoreIKT * ($indicatorPercentageIKT / 100);

        // Ambil komposisi target
        $compositionTargetIKU = $this->compositionScoreTarget($assessmentId, true);
        $compositionTargetIKT = $this->compositionScoreTarget($assessmentId, isIKT: true);

        $maxNilaiTargetIKU = $compositionTargetIKU['max_nilai_target'];
        $maxNilaiTargetIKT = $compositionTargetIKT['max_nilai_target'];
        $maxNilaiTargetAll = $compositionTargetAll['max_nilai_target'];
        $nilaiTarget = $compositionTargetAll['nilai_target'];
        $maxIKTScore = $finalScoreIKT;

        // Persentasekan nilai target vs max nilai target
        $totalTargetPercentage = ($maxNilaiTargetAll > 0)
            ? ($nilaiTarget / $maxNilaiTargetAll) * 100
            : 0;
        $totalTargetPercentage = number_format($totalTargetPercentage, 2, '.', ',');

        // Normalisasi skor (IKU nol bila !apakahDataDefault)
        $normalizedIKUScore = 0;
        if ($apakahDataDefault && $maxNilaiTargetIKU > 0) {
            $normalizedIKUScore = ($totalFinalScoreIKU / $maxNilaiTargetIKU) * 100;
        }

        $normalizedIKTScore = 0;
        if ($maxIKTScore > 0 && $maxNilaiTargetIKT > 0) {
            $normalizedIKTScore = ($totalFinalScoreIKT / $maxNilaiTargetIKT) * 100;
        }

        // Rank score: bila bukan default, murni dari IKT
        $rankScore = $normalizedIKTScore + ($apakahDataDefault ? $normalizedIKUScore : 0);
        $rankScore = (float) number_format($rankScore, 2, '.', ',');

        // Ambil rentang peringkat
        $qualityRates = SpmiPeringkat::where('id_audit_periode', $auditPeriodId)
            ->whereNull('waktu_dihapus')
            ->orderBy('skor_minimal', 'asc')
            ->get();

        // Ambil Peringkat
        $tempRankScore = round($rankScore);
        $rank = $qualityRates->first(function ($item) use ($tempRankScore) {
            return $tempRankScore >= $item->skor_minimal && $tempRankScore <= $item->skor_maksimal;
        });

        if (empty($rank)) {
            return new Error('Tidak ada peringkat AMI yang sesuai, skor AMI Anda adalah: ' . $rankScore, 500);
        }

        // HARCODED untuk akreditasi LAMINFOKOM 2.1 S1
        if ($penilaianPanduan->kode_penilaian_panduan == 'INFOKOM2.1S1') {
            $finalScoreIKU = $finalScoreIKU / 4;
        }

        // Dasar akreditasi: IKU jika default, selain itu pakai IKT
        $baseScoreForAccreditation = $apakahDataDefault ? (int) $finalScoreIKU : (int) $finalScoreIKT;

        if ($isHasButirSPME) {
            $akreditasiPeringkat = $this->generateAkreditasiPeringkat(
                $baseScoreForAccreditation,
                $auditPeriodId,
                $studyProgramId,
                $assessment->id_penilaian_panduan
            );

            if (empty($akreditasiPeringkat)) {
                return new Error('Tidak ada peringkat SPME yang sesuai, skor SPME Anda adalah: ' . $baseScoreForAccreditation, 500);
            }
        }

        // Simpan hasil akhir (nilai_akhir: jika bukan default, hanya IKT)
        HasilAkhirAudit::updateOrCreate([
            'id_audit_periode'    => $auditPeriodId,
            'id_unit'             => $studyProgramId,
            'id_penilaian_audit'  => $assessmentId,
        ], [
            'id_audit_periode'        => $auditPeriodId,
            'id_unit'                 => $studyProgramId,
            'id_penilaian_audit'      => $assessmentId,
            'nilai_iku'               => $finalScoreIKU,         // 0 bila !apakahDataDefault
            'nilai_ikt'               => $finalScoreIKT,
            'nilai_akhir'             => $apakahDataDefault ? ($finalScoreIKU + $finalScoreIKT) : $finalScoreIKT,
            'nilai_akhir_auditee'     => $auditeeAssessment?->nilai_akhir,
            'persentase_nilai_akhir'  => $rankScore,
            'persentase_nilai_target' => $totalTargetPercentage,
            'id_spmi_peringkat'       => $rank->id,
            'id_akreditasi_peringkat' => $akreditasiPeringkat->id ?? null,
        ]);

        // Ambil penilaian panduan, untuk mendapatkan total matriks
        $penilaianPanduan = PenilaianPanduan::find($assessment->id_penilaian_panduan);

        if (empty($penilaianPanduan)) {
            return new Error('Penilaian Panduan tidak ditemukan', 500);
        }

        // Update status penilaian sudah di finalisasi
        $assessment->update([
            'apakah_terfinalisasi'    => true,
            'total_indikator_matriks'  => $penilaianPanduan->total_indikator_matriks,
        ]);

        $revalidateTemuan = (new AuditTemuanManagementService)->revalidateTemuan($assessmentId);

        if ($revalidateTemuan instanceof Error) {
            DB::rollBack();
            return $revalidateTemuan;
        }
        DB::commit();

        return $assessment;
    }

    protected function generateAkreditasiPeringkat($finalScore, $auditPeriodId, $studyProgramId, $idPenilaianPanduan)
    {
        $akreditasiPeringkat = AkreditasiPeringkat::where('waktu_dihapus', null)
            ->where('id_penilaian_panduan', $idPenilaianPanduan)
            ->orderBy('nilai_minimal', 'asc')
            ->get();

        if ($akreditasiPeringkat->isEmpty()) {
            return null;
        }

        // Ambil Peringkat akreditasi
        $tempFinalScore = round($finalScore);
        $akreditasiPeringkat = $akreditasiPeringkat->first(function ($item) use ($tempFinalScore) {
            return $tempFinalScore >= $item->nilai_minimal && $tempFinalScore <= $item->nilai_maksimal;
        });

        if (empty($akreditasiPeringkat)) {
            return null;
        }

        // Cek apakah skor akreditasi memenuhi
        $akreditasiPeringkatCode = [
            3 => AkreditasiPeringkat::CODE_SUPERIOR,
            2 => AkreditasiPeringkat::CODE_VERY_GOOD,
            1 => AkreditasiPeringkat::CODE_GOOD,
            0 => AkreditasiPeringkat::CODE_MINIMUM,
        ];
        $akreditasiPeringkatCode = array_keys(AkreditasiPeringkat::CODES);

        $comparedScoreByAccreditation = $this->getComparedScoreByRequirement($auditPeriodId, $studyProgramId, $akreditasiPeringkat->id);

        $filterNotAccreditedRequirement = array_filter(
            $comparedScoreByAccreditation,
            fn($item) => !$item['apakah_terpenuhi'] && $item['jenis_syarat_akreditasi'] === AkreditasiSyarat::TYPE_ACCREDITED
        );

        $filterNotAccreditedRank = array_filter(
            $comparedScoreByAccreditation,
            fn($item) => !$item['apakah_terpenuhi'] && $item['jenis_syarat_akreditasi'] === AkreditasiSyarat::TYPE_RANK
        );

        $rankIndex = array_filter($akreditasiPeringkatCode, fn($item) => $item === $akreditasiPeringkat->kode_peringkat);
        $rankIndex = array_keys($rankIndex)[0] ?? null;

        // Jika syarat perlu akreditasi tidak terpenuhi salah satu maka set peringkat tidak terakreditasi
        if (!empty($filterNotAccreditedRequirement)) {
            $akreditasiPeringkat = $akreditasiPeringkat->where('kode_peringkat', AkreditasiPeringkat::CODE_MINIMUM)->first();
        }

        // Jika peringkat tidak kosong maka set peringkat akreditasi dikurangi total skor
        if (!empty($filterNotAccreditedRank) && $akreditasiPeringkat->kode_peringkat !== AkreditasiPeringkat::CODE_MINIMUM) {
            $changeRankIndex = $rankIndex - 1;
            $changeRankIndex = $changeRankIndex < 0 ? 0 : $changeRankIndex;
            $akreditasiPeringkat = $akreditasiPeringkat->where('kode_peringkat', $akreditasiPeringkatCode[$changeRankIndex] ?? null)->first();
        }

        return $akreditasiPeringkat;
    }

    protected function generateAkreditasiStatus($finalScore, $idPenilaianPanduan)
    {
        $akreditasiStatus = AkreditasiStatus::where('waktu_dihapus', null)
            ->where('id_penilaian_panduan', $idPenilaianPanduan)
            ->orderBy('nilai_minimal', 'asc')
            ->get();

        if ($akreditasiStatus->isEmpty()) {
            return null;
        }

        // Ambil Peringkat akreditasi
        $tempFinalScore = round($finalScore);
        $akreditasiStatus = $akreditasiStatus->first(function ($item) use ($tempFinalScore) {
            return $tempFinalScore >= $item->nilai_minimal && $tempFinalScore <= $item->nilai_maksimal;
        });

        return $akreditasiStatus;
    }

    protected function compositionScoreTarget($idPenilaianAudit, $isIKU = false, $isIKT = false)
    {
        $sql =
            "SELECT
                COALESCE(SUM(sc.nilai_target), 0) nilai_target,
                COALESCE(SUM(sc.max_nilai_target)) max_nilai_target
            FROM spmi.penilaian_skor sc
            JOIN spmi.penilaian_matriks pm ON pm.id = sc.id_penilaian_matriks
                AND pm.kategori_penilaian = :kategori_penilaian
                AND pm.waktu_dihapus IS NULL
            WHERE id_penilaian_audit = :id_penilaian_audit";

        if ($isIKU) {
            $sql .= " AND pm.apakah_data_default = true";
        }

        if ($isIKT) {
            $sql .= " AND pm.apakah_data_default = false";
        }

        $data = DB::select($sql, [
            'kategori_penilaian' => PenilaianMatriks::CATEGORY_INDICATOR,
            'id_penilaian_audit' => $idPenilaianAudit
        ]);

        return [
            'nilai_target' => (float) ($data[0]?->nilai_target ?? 0),
            'max_nilai_target' => (float) ($data[0]?->max_nilai_target ?? 0)
        ];
    }

    public static function getComparedScoreByRequirement($auditPeriodId, $studyProgramId, $idAkreditasiPeringkat)
    {
        $sql =
            "SELECT
                pm.pertanyaan_penilaian,
                pm.nomor_penilaian,
                ar.jenis_syarat_akreditasi,
                COALESCE(
                    ROUND((sc.nilai_akhir / COALESCE(pm.bobot_penilaian, 1)), 2),
                    0
                ) nilai,
                ar.nilai_syarat_akreditasi syarat_nilai
            FROM spmi.penilaian_audit ass
            JOIN spmi.penilaian_matriks pm ON pm.id_penilaian_panduan = ass.id_penilaian_panduan
                AND pm.waktu_dihapus IS NULL
            JOIN spmi.akreditasi_syarat ar ON ar.id_penilaian_matriks = pm.id AND ar.id_penilaian_panduan = ass.id_penilaian_panduan AND ar.id_akreditasi_peringkat = :id_akreditasi_peringkat
                AND ar.waktu_dihapus IS NULL
            LEFT JOIN spmi.penilaian_skor sc ON sc.id_penilaian_audit = ass.id
                AND sc.id_penilaian_matriks = pm.id
            WHERE ass.id_audit_periode = :id_audit_periode AND ass.id_unit = :id_unit
                AND ass.apakah_penilaian_mandiri = false
                AND ass.waktu_dihapus IS NULL";

        $data = DB::select($sql, [
            'id_akreditasi_peringkat' => $idAkreditasiPeringkat,
            'id_audit_periode' => $auditPeriodId,
            'id_unit' => $studyProgramId
        ]);

        $mappedData = array_map(function ($item) {
            $item = (array) $item;
            $currentScore = (float) $item['nilai'];
            $requirementScore = (float) $item['syarat_nilai'];

            $item['apakah_terpenuhi'] = $currentScore >= $requirementScore;

            return $item;
        }, $data);

        return $mappedData;
    }

    public function showAuditeeAssessment($idPenilaianPanduan, $studyProgramId, $auditPeriodId, $idJadwalAudit = null)
    {
        return PenilaianAudit::findByStudyProgramId($idPenilaianPanduan, $studyProgramId, $auditPeriodId, true, $idJadwalAudit);
    }

    public function getAllReferenceIndicators(array $matrixIds, $studyProgramId, $auditPeriodId)
    {
        if (empty($matrixIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($matrixIds), '?'));

        $sql =
            "SELECT
                pmr.id_penilaian_matriks,
                CASE WHEN pmr.jenis_referensi = 'se'
                    THEN se.id
                    ELSE pr.id
                END id,
                fi.id id_indikator_pengisian,
                CASE WHEN pmr.jenis_referensi = 'se'
                    THEN se.nama_indikator_evaluasi_diri
                    ELSE pr.nama_indikator_laporan_kinerja
                END name,
                pmr.jenis_referensi
            FROM spmi.penilaian_matriks_referensi pmr
            LEFT JOIN spmi.indikator_evaluasi_diri se ON se.id = pmr.id_butir_referensi
                AND se.waktu_dihapus is null
            LEFT JOIN spmi.indikator_laporan_kinerja pr ON pr.id = pmr.id_butir_referensi
                AND pr.waktu_dihapus is null
            LEFT JOIN spmi.pengisian_indikator fi ON fi.id_audit_periode = ?
                AND fi.id_unit = ?
                AND fi.jenis_indikator = pmr.jenis_referensi
                AND fi.waktu_dihapus is null
            WHERE pmr.id_penilaian_matriks IN ({$placeholders})";

        $bindings = array_merge([$auditPeriodId, $studyProgramId], $matrixIds);

        $data = DB::select($sql, $bindings);

        // Group by matrix id
        $grouped = [];
        foreach ($data as $item) {
            $item = (array) $item;
            $matrixId = $item['id_penilaian_matriks'];

            if (!isset($grouped[$matrixId])) {
                $grouped[$matrixId] = [];
            }

            $grouped[$matrixId][] = $item['name'];
        }

        return $grouped;
    }

    protected function getFinalPenilaianSkor($assessmentId, $type)
    {
        $sql =
            "SELECT
                COALESCE(SUM(sc.nilai_akhir), 0) final_score
            FROM spmi.penilaian_audit pa
            JOIN spmi.penilaian_skor sc ON sc.id_penilaian_audit = pa.id
            JOIN spmi.mapping_penilaian_matriks mpm ON mpm.id_audit_periode = pa.id_audit_periode AND mpm.id_unit = pa.id_unit
            JOIN spmi.penilaian_matriks pm ON pm.id = mpm.id_penilaian_matriks AND pm.id = sc.id_penilaian_matriks
                AND pm.id_penilaian_panduan = pa.id_penilaian_panduan
                AND pm.waktu_dihapus IS NULL
            WHERE " . ($type == IndikatorBobot::TYPE_IKU ? ' (pm.apakah_data_default = true or pm.butir_indikator_iku = true) ' : ' (pm.apakah_data_default = false) ') . "
                AND pm.kategori_penilaian = :kategori_penilaian
                AND pa.id = :id_penilaian_audit";

        $bindings = [
            'id_penilaian_audit' => $assessmentId,
            'kategori_penilaian' => PenilaianMatriks::CATEGORY_INDICATOR
        ];

        $data = DB::select($sql, $bindings);

        return (float) ($data[0]?->final_score ?? 0);
    }
}
