<?php

namespace Modules\SPMI\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\AuditTemuan;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\SpmiPeringkat;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;

class DashboardService
{
    public static function showRankSectionData($auditPeriodId, $studyProgramId = null)
    {
        $averageScorePercent = self::showAverageScorePercent($auditPeriodId, $studyProgramId);
        $rank = self::findRank($auditPeriodId, (float) $averageScorePercent);

        $rankSection['information']['average_score'] = $averageScorePercent;
        $rankSection['information']['rank_detail'] = ['summary' => $rank['summary'], 'total' => $rank['total']];
        $rankSection['information']['rank'] = $rank['data'];

        $finalScoreChart = self::showFinalScoreChart($auditPeriodId, $studyProgramId);
        $rankSection['chart']['nilai_akhir'] = $finalScoreChart;

        // Menghitung kenaikan peringkat dari periode sebelumnya
        $activePeriodRank = (float) $finalScoreChart['active_period_rank'];
        $previousPeriodRank = (float) $finalScoreChart['previous_period_rank'];
        $increaseScore = $previousPeriodRank != 0
            ? ($activePeriodRank - $previousPeriodRank) / $previousPeriodRank
            : 1;
        $increaseScorePercent = $increaseScore * 100;

        // Jika aktif periode dan periode sebelumnya memiliki nilai 0, maka kenaikannya 0
        if ($activePeriodRank == 0 && $previousPeriodRank == 0) {
            $increaseScorePercent = 0;
        }

        $rankSection['information']['rank_increase'] = number_format($increaseScorePercent, 2, '.', '');

        $targetScoreProgressPercent = self::showTargetScoreProgressPercent($auditPeriodId, $studyProgramId);
        $rankSection['progress']['nilai_target'] = $targetScoreProgressPercent;

        $fillingIndicatorProgressPercent = self::showPengisianIndikatorProgressPercent($auditPeriodId, $studyProgramId);
        $rankSection['progress']['filling_indicator'] = $fillingIndicatorProgressPercent;

        $fillingSelfIndicatorProgressPercent = self::showPengisianIndikatorProgressPercent($auditPeriodId, $studyProgramId, true);
        $rankSection['progress']['filling_self_indicator'] = $fillingSelfIndicatorProgressPercent;

        $PenilaianAuditors = self::getPenilaianAuditors($auditPeriodId, $studyProgramId);
        $PenilaianAuditorProgressPercent = array_column($PenilaianAuditors, 'total_penilaian_terisi_percent');
        $PenilaianAuditorProgressPercent = !empty($studyProgramId)
            ? array_column(array_filter($PenilaianAuditors, fn($item) => $item['id_unit'] == $studyProgramId), 'total_penilaian_terisi_percent')
            : array_column($PenilaianAuditors, 'total_penilaian_terisi_percent');

        $totalPenilaianAuditorProgressPercent = count($PenilaianAuditorProgressPercent) ?: 1;
        $averagePenilaianAuditorProgressPercent = (int) round(array_sum($PenilaianAuditorProgressPercent) / $totalPenilaianAuditorProgressPercent, 0, PHP_ROUND_HALF_UP);

        $PenilaianAuditorProgressPercent2 = array_column($PenilaianAuditors, 'total_penilaian_terisi_percent');
        $totalPenilaianAuditorProgressPercent2 = count($PenilaianAuditorProgressPercent2) ?: 1;
        $averagePenilaianAuditorProgressPercent2 = (int) round(array_sum($PenilaianAuditorProgressPercent2) / $totalPenilaianAuditorProgressPercent2, 0, PHP_ROUND_HALF_UP);

        $rankSection['progress']['auditor_assessment'] = $averagePenilaianAuditorProgressPercent;
        $rankSection['progress']['auditor_assessment2'] = $averagePenilaianAuditorProgressPercent2;
        $rankSection['raw']['auditor_assessment'] = $PenilaianAuditors;

        return $rankSection;
    }

    public static function showCriteriaChartSection($auditPeriodId, $studyProgramId = null)
    {
        return self::showRawCriteriaResultChart($auditPeriodId, $studyProgramId);
    }

    public static function getScheduledStudyProgram($auditPeriodId, $isScheduled = false)
    {
        $sql =
            "SELECT
                asch.id,
                CONCAT(d.kode_jenjang, ' - ', o.nama_unit) nama_unit,
                asch.tanggal_awal_penilaian,
                asch.tanggal_akhir_penilaian
            FROM core.unit_kerja o
            JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
                AND d.waktu_dihapus IS NULL
            LEFT JOIN spmi.jadwal_audit asch ON asch.id_audit_periode = :id_audit_periode
                AND asch.waktu_dihapus IS NULL
            LEFT JOIN spmi.jadwal_audit_unit aso ON aso.id_unit = o.id
                AND aso.id_jadwal_audit = asch.id
                AND o.apakah_aktif = true
            WHERE o.jenis_unit IN (:type1, :type2)
                AND o.apakah_aktif = true
                AND o.waktu_dihapus IS NULL";

        if ($isScheduled) {
            $sql .= " AND aso.id IS NOT NULL";
        } else {
            $sql .= " AND aso.id IS NULL";
        }

        $sql .= " ORDER BY CONCAT(d.kode_jenjang, ' - ', o.nama_unit) ASC";

        $bindings['id_audit_periode'] = $auditPeriodId;
        $bindings['type1'] = UnitKerja::STUDY_PROGRAM;
        $bindings['type2'] = UnitKerja::UNIT_NON_PRODI;

        $data = DB::select($sql, $bindings);
        $data = array_map(function ($item) use ($isScheduled) {
            $item = (array) $item;

            $mapped['id'] = $item['id'];
            $mapped['nama_unit'] = $item['nama_unit'];
            $mapped['assessment_date'] = null;

            if (isset($item['tanggal_awal_penilaian'], $item['tanggal_akhir_penilaian']) && $isScheduled) {
                $mapped['assessment_date'] = Carbon::parse($item['tanggal_awal_penilaian'])->translatedFormat('d M Y')
                    . ' s.d. ' . Carbon::parse($item['tanggal_akhir_penilaian'])->translatedFormat('d M Y');
            }

            return $mapped;
        }, $data);

        return $data;
    }

    public static function getAuditorPerson($auditPeriodId)
    {
        $sql =
            "SELECT
                p.nama original_name,
                CONCAT(p.gelar_depan, ' ', p.nama, ' ', p.gelar_belakang) name,
                CONCAT(d.kode_jenjang, ' - ', o.nama_unit) nama_unit,
                stp.total_study_program
            FROM core.biodata p
            JOIN core.pegawai e ON e.id_biodata = p.id
                AND e.waktu_dihapus IS NULL
            LEFT JOIN core.unit_kerja o ON o.id = e.id_unit_kerja
                AND o.waktu_dihapus IS NULL
            LEFT JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
                AND d.waktu_dihapus IS NULL
            JOIN (
                SELECT
                    stp.id_personil,
                    sa.id_audit_periode,
                    stp.posisi,
                    COUNT(stp.id_unit) total_study_program
                FROM spmi.surat_tugas_auditor_pegawai stp
                JOIN spmi.surat_tugas_auditor sa ON sa.id = stp.id_surat_tugas_auditor
                    AND sa.waktu_dihapus IS NULL
                WHERE stp.waktu_dihapus IS NULL
                GROUP BY stp.id_personil, sa.id_audit_periode, stp.posisi
            ) stp ON stp.id_personil = p.id
                AND stp.posisi NOT IN (:posisi_auditee, :posisi_member_auditee)
                AND stp.id_audit_periode = :id_audit_periode";

        $data = DB::select($sql, [
            'id_audit_periode' => $auditPeriodId,
            'posisi_auditee' => SuratTugasAuditorPegawai::POSITION_AUDITEE,
            'posisi_member_auditee' => SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE,
        ]);

        $data = array_map(function ($item) {
            $item = (array) $item;
            return $item;
        }, $data);

        if (!empty($data)) {
            $sumStudyProgramPerson = array_sum(array_column($data, 'total_study_program'));
            $averageStudyProgramPerson = $sumStudyProgramPerson / count($data);
        }

        return [
            'data' => $data,
            'total_auditor' => count($data),
            'average_study_program' => (int) round(($averageStudyProgramPerson ?? 0), 0, PHP_ROUND_HALF_UP)
        ];
    }

    public static function showAuditTemuanSection($auditPeriodId, $studyProgramId = null)
    {
        $chartData = self::getAuditTemuanChart($auditPeriodId, $studyProgramId);
        $data['chart'] = $chartData;

        $data['average']['observation'] = array_sum($chartData['data']['observation']);
        $data['average']['kts_minor'] = array_sum($chartData['data']['kts_minor']);
        $data['average']['kts_mayor'] = array_sum($chartData['data']['kts_mayor']);

        return $data;
    }

    public function getMostRelevantAuditTemuan($auditPeriodId, $studyProgramId = null, $idJadwalAudit = null, $limit = null)
    {
        $sql = "SELECT
                m.nomor_penilaian,
                m.pertanyaan_penilaian,
                af.jenis_temuan,
                COUNT(m.id) AS total_finding,
                ARRAY_AGG(
                    CONCAT(
                        ass.id, '|',
                        COALESCE(jp.kode_jenjang || ' - ', ''),
                        uk.nama_unit
                    )
                ) AS program_studi
            FROM spmi.audit_temuan af
            JOIN spmi.penilaian_matriks m
                ON m.id = af.id_penilaian_matriks
                AND m.waktu_dihapus IS NULL
            JOIN spmi.penilaian_audit ass
                ON ass.id = af.id_penilaian_audit
                AND ass.waktu_dihapus IS NULL
                AND ass.apakah_penilaian_mandiri = false
            JOIN core.unit_kerja uk
                ON uk.id = ass.id_unit
                AND uk.waktu_dihapus IS NULL
            JOIN core.jenjang_pendidikan jp
                ON jp.id = uk.id_jenjang_pendidikan
                AND jp.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit_unit aso
                ON aso.id_unit = uk.id
            JOIN spmi.jadwal_audit asch
                ON asch.id = aso.id_jadwal_audit
                AND asch.id_audit_periode = ass.id_audit_periode
                AND asch.id = ass.id_jadwal_audit
                AND asch.apakah_audit_aktif = true
                AND asch.waktu_dihapus IS NULL
            JOIN spmi.penilaian_panduan pp
                ON pp.id = aso.id_penilaian_panduan
                AND m.id_penilaian_panduan = pp.id
            JOIN spmi.surat_tugas_auditor sa
                ON sa.id_audit_periode = asch.id_audit_periode
                AND sa.waktu_dihapus IS NULL
            WHERE af.waktu_dihapus IS NULL
            AND ass.id_audit_periode = :id_audit_periode
        ";

        $bindings = [
            'id_audit_periode' => $auditPeriodId
        ];

        if (!empty($idJadwalAudit)) {
            $sql .= " AND asch.id = :id_jadwal_audit ";
            $bindings['id_jadwal_audit'] = $idJadwalAudit;
        }

        if (!empty($studyProgramId)) {
            $sql .= " AND ass.id_unit = :id_unit ";
            $bindings['id_unit'] = $studyProgramId;
        }

        $sql .= "
            GROUP BY
                m.nomor_penilaian,
                m.pertanyaan_penilaian,
                af.jenis_temuan
            ORDER BY
                af.jenis_temuan DESC,
                total_finding DESC
        ";

        if (!empty($limit)) {
            $sql .= " LIMIT $limit ";
        }

        $data = DB::select($sql, $bindings);

        return array_map(function ($item) {
            $item = (array) $item;
            $item['finding_type_name'] = AuditTemuan::TYPES[$item['jenis_temuan']] ?? null;
            return $item;
        }, $data);
    }


    public function getRecapFinalScoreData($auditPeriodId, $studyProgramId = null, int|null $page = null, int|null $perPage = null): mixed
    {
        $sql = "SELECT
                fr.id,
                ap.tahun_audit periode_audit,
                concat(jp.kode_jenjang, ' - ', o.nama_unit) as nama_unit,
                fr.nilai_iku,
                fr.nilai_ikt,
                fr.persentase_nilai_akhir,
                qr.nama_spmi_peringkat,
                ar.nama_peringkat_akreditasi,
                pp.nama_singkat AS nama_penilaian_panduan,
                pa.apakah_terfinalisasi
            FROM core.unit_kerja o
            JOIN core.jenjang_pendidikan jp ON jp.id = o.id_jenjang_pendidikan AND jp.waktu_dihapus IS NULL
            JOIN spmi.audit_periode ap ON ap.id = ? AND ap.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit ja ON ja.id_audit_periode = ap.id
            JOIN spmi.jadwal_audit_unit jau ON jau.id_jadwal_audit = ja.id AND jau.id_unit = o.id
            JOIN spmi.penilaian_panduan pp ON pp.id = jau.id_penilaian_panduan AND pp.apakah_aktif = true AND pp.waktu_dihapus IS NULL
            JOIN spmi.penilaian_audit pa ON
                pa.id_audit_periode = ap.id AND
                pa.id_unit = o.id AND
                pa.id_penilaian_panduan = pp.id AND
                pa.apakah_penilaian_mandiri = false
                -- AND pa.apakah_terfinalisasi = true
            JOIN spmi.hasil_akhir_audit fr ON fr.id_penilaian_audit = pa.id
            JOIN spmi.spmi_peringkat qr ON qr.id = fr.id_spmi_peringkat AND qr.waktu_dihapus IS NULL
            JOIN spmi.akreditasi_peringkat ar ON ar.id = fr.id_akreditasi_peringkat AND ar.waktu_dihapus IS NULL";

        $fieldMap = [
            'id_audit_periode' => 'ap.id',
            'id_unit_kerja' => 'o.id',
            'id_jenjang_pendidikan' => 'jp.id',
            'periode_audit' => 'ap.tahun_audit',
        ];

        $defaultFilter = "fr.waktu_dihapus IS NULL";

        $bindings = [(int) $auditPeriodId];

        [$sql, $bindings] = Pagination::buildQuery(
            bindings: $bindings,
            query: $sql,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    public static function getAuditTemuanData($auditPeriodId, $studyProgramId = null, int|null $page = null, int|null $perPage = null)
    {
        $sql =
            "SELECT
                ass.id,
                o.id id_unit,
                CONCAT(d.kode_jenjang, ' - ', o.nama_unit) nama_unit,
                SUM(
                    CASE WHEN af.jenis_temuan = '1' THEN 1 ELSE 0 END
                ) total_observation,
                SUM(
                    CASE WHEN af.jenis_temuan = '2' THEN 1 ELSE 0 END
                ) total_kts_minor,
                SUM(
                    CASE WHEN af.jenis_temuan = '3' THEN 1 ELSE 0 END
                ) total_kts_mayor
            FROM core.unit_kerja o
            JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
                AND d.waktu_dihapus IS NULL
            JOIN spmi.penilaian_audit ass ON ass.id_unit = o.id
                AND ass.id_audit_periode = ?
                AND ass.apakah_penilaian_mandiri = false
                AND ass.waktu_dihapus IS NULL
            JOIN spmi.audit_temuan af ON af.id_penilaian_audit = ass.id
                AND af.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit_unit aso ON aso.id_unit = o.id
            JOIN spmi.jadwal_audit asch ON asch.id = aso.id_jadwal_audit
                AND asch.id_audit_periode = ?
                AND asch.apakah_audit_aktif = true
                AND asch.waktu_dihapus is null
            JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = asch.id_audit_periode
                AND sa.waktu_dihapus is null";

        $fieldMap = [
            'nama_unit' => "CONCAT(d.kode_jenjang, ' - ', o.nama_unit)",
        ];

        $defaultFilter =
            "o.waktu_dihapus is null AND o.jenis_unit = ?";
        $sqlFilterByPenilaianPanduan =
            " AND (
                SELECT
                    d.id
                FROM core.jenjang_pendidikan d
                JOIN core.lembaga_akreditasi aa ON aa.id = COALESCE(ass.id_lembaga_akreditasi, o.id_lembaga_akreditasi)
                    AND aa.waktu_dihapus is null
                JOIN spmi.pengisian_panduan fg ON fg.id_lembaga_akreditasi = aa.id
                    AND (
                        CASE WHEN fg.id_jenjang_pendidikan IS NULL
                            THEN true
                            ELSE fg.id_jenjang_pendidikan = o.id_jenjang_pendidikan
                        END
                    )
                    AND fg.kode_level_akses = ?
                    AND fg.apakah_aktif = true
                    AND fg.waktu_dihapus is null
                JOIN spmi.penilaian_panduan ag ON (
                        CASE WHEN ass.id is not null
                            THEN ag.id = ass.id_penilaian_panduan
                            ELSE ag.id_laporan_kinerja = fg.id AND ag.id_jenjang_pendidikan = o.id_jenjang_pendidikan
                        END)
                    AND ag.apakah_aktif = true AND ag.waktu_dihapus is null
                WHERE d.id = o.id_jenjang_pendidikan
                LIMIT 1
            ) is not null";

        $defaultFilter .= $sqlFilterByPenilaianPanduan;

        $bindings[] = $auditPeriodId;
        $bindings[] = $auditPeriodId;
        $bindings[] = UnitKerja::STUDY_PROGRAM;
        $bindings[] = PengisianPanduan::LEVEL_AKSES_PS;


        if (!empty($studyProgramId)) {
            $defaultFilter .= " AND o.id = ?";
            $bindings[] = $studyProgramId;
        }

        $order = [
            ['field' => 'total_kts_mayor', 'direction' => 'desc', 'desc' => true],
            ['field' => 'total_kts_minor', 'direction' => 'desc', 'desc' => true]
        ];

        [$sql, $bindings] = Pagination::buildQuery(
            bindings: $bindings,
            query: $sql,
            order: $order ?? [],
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
            groupBy: "o.id, af.id_penilaian_audit, d.id, ass.id"
        );

        $data = Pagination::create($sql, $bindings, $page, $perPage);

        // Append periode audit
        $periodeAudit = AuditPeriode::find($auditPeriodId)?->tahun_audit;
        $data->items = array_map(function ($item) use ($periodeAudit) {
            $item['periode_audit'] = $periodeAudit;
            return $item;
        }, $data->items);

        return $data;
    }

    protected static function getAuditTemuanChart($auditPeriodId, $studyProgramId = null)
    {
        $sql =
            "SELECT
                CONCAT(d.kode_jenjang, ' - ', o.nama_unit) nama_unit,
                SUM(
                    CASE WHEN af.jenis_temuan = '1' THEN 1 ELSE 0 END
                ) total_observation,
                SUM(
                    CASE WHEN af.jenis_temuan = '2' THEN 1 ELSE 0 END
                ) total_kts_minor,
                SUM(
                    CASE WHEN af.jenis_temuan = '3' THEN 1 ELSE 0 END
                ) total_kts_mayor
            FROM core.unit_kerja o
            JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
                AND d.waktu_dihapus IS NULL
            JOIN spmi.penilaian_audit ass ON ass.id_unit = o.id
                AND ass.id_audit_periode = :id_audit_periode
                AND ass.apakah_penilaian_mandiri = false
                AND ass.waktu_dihapus IS NULL
            JOIN spmi.audit_temuan af ON af.id_penilaian_audit = ass.id
                AND af.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit_unit aso ON aso.id_unit = o.id
            JOIN spmi.jadwal_audit asch ON asch.id = aso.id_jadwal_audit
                AND asch.id_audit_periode = :id_audit_periode
                AND asch.apakah_audit_aktif = true
                AND asch.waktu_dihapus is null
            JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = asch.id_audit_periode
                AND sa.waktu_dihapus is null
            ";

        $defaultFilter = " WHERE o.jenis_unit = 'P'";
        $sqlFilterByPenilaianPanduan =
            " AND (
                SELECT
                    d.id
                FROM core.jenjang_pendidikan d
                JOIN core.lembaga_akreditasi aa ON aa.id = COALESCE(ass.id_lembaga_akreditasi, o.id_lembaga_akreditasi)
                    AND aa.waktu_dihapus is null
                JOIN spmi.pengisian_panduan fg ON fg.id_lembaga_akreditasi = aa.id
                    AND (
                        CASE WHEN fg.id_jenjang_pendidikan IS NULL
                            THEN true
                            ELSE fg.id_jenjang_pendidikan = o.id_jenjang_pendidikan
                        END
                    )
                    AND fg.kode_level_akses = :kode_level_akses
                    AND fg.apakah_aktif = true
                    AND fg.waktu_dihapus is null
                JOIN spmi.penilaian_panduan ag ON (
                        CASE WHEN ass.id is not null
                            THEN ag.id = ass.id_penilaian_panduan
                            ELSE ag.id_laporan_kinerja = fg.id AND ag.id_jenjang_pendidikan = o.id_jenjang_pendidikan
                        END)
                    AND ag.apakah_aktif = true AND ag.waktu_dihapus is null
                WHERE d.id = o.id_jenjang_pendidikan
                LIMIT 1
            ) is not null";
        $defaultFilter .= $sqlFilterByPenilaianPanduan;

        $sql .= $defaultFilter;

        if (!empty($studyProgramId)) {
            $sql .= " AND o.id = :id_unit";
            $bindings['id_unit'] = $studyProgramId;
        }

        $sql .= " GROUP BY o.id, af.id_penilaian_audit, d.id
                ORDER BY af.id_penilaian_audit";
        $bindings['id_audit_periode'] = $auditPeriodId;
        $bindings['kode_level_akses'] = PengisianPanduan::LEVEL_AKSES_PS;

        $data = DB::select($sql, $bindings);

        // NOTE: Tidak digunakan
        // Tambahkan separator untuk kebutuhan chart
        // if (count($data) < 8) {
        //     $fill = static::isOdd(count($data)) ? 7 : 8;
        //     $elementsToAdd = $fill - count($data);
        //     $before = floor($elementsToAdd / 2);
        //     $after = ceil($elementsToAdd / 2);

        //     $separator = [
        //         "nama_unit" => "",
        //         "total_observation" => 0,
        //         "total_kts_minor" => 0,
        //         "total_kts_mayor" => 0
        //     ];

        //     for ($i = 0; $i < $before; $i++) {
        //         array_unshift($data, $separator);
        //     }

        //     for ($i = 0; $i < $after; $i++) {
        //         array_push($data, $separator);
        //     }
        // }

        $pluckTotalObservation = array_column($data, 'total_observation');
        $pluckTotalKTSMinor = array_column($data, 'total_kts_minor');
        $pluckTotalKTSMayor = array_column($data, 'total_kts_mayor');
        $pluckStudyProgram = array_column($data, 'nama_unit');

        $chart['category'] = $pluckStudyProgram;
        $chart['data']['observation'] = $pluckTotalObservation;
        $chart['data']['kts_minor'] = $pluckTotalKTSMinor;
        $chart['data']['kts_mayor'] = $pluckTotalKTSMayor;

        return $chart;
    }

    protected static function showAverageScorePercent($auditPeriodId, $studyProgramId = null)
    {
        $sql =
            "SELECT
                AVG(fr.persentase_nilai_akhir)
            FROM core.unit_kerja o
            JOIN spmi.audit_periode ap ON ap.id = :id_audit_periode AND ap.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit ja ON ja.id_audit_periode = ap.id
            JOIN spmi.jadwal_audit_unit jau ON jau.id_jadwal_audit = ja.id AND jau.id_unit = o.id
            JOIN spmi.penilaian_panduan pp ON pp.id = jau.id_penilaian_panduan AND pp.apakah_aktif = true AND pp.waktu_dihapus IS NULL
            JOIN spmi.penilaian_audit pa ON
                pa.id_audit_periode = ap.id AND
                pa.id_unit = o.id AND
                pa.id_penilaian_panduan = pp.id AND
                pa.apakah_penilaian_mandiri = false
                AND pa.apakah_terfinalisasi = true
            JOIN spmi.hasil_akhir_audit fr ON fr.id_penilaian_audit = pa.id";

        $bindings['id_audit_periode'] = $auditPeriodId;

        if ($studyProgramId) {
            $sql .= " AND o.id = :id_unit";
            $bindings['id_unit'] = $studyProgramId;
        }

        $data = DB::select($sql, $bindings);

        if (!empty($data)) {
            return number_format($data[0]->avg, 2, '.', '');
        }

        return 0;
    }

    protected static function showFinalScoreChart($auditPeriodId, $studyProgramId = null)
    {
        // Cari tahun terpilih
        $auditPeriod = AuditPeriode::find($auditPeriodId);

        $year = $auditPeriod?->tahun_audit;
        $years = range($year - 5, $year);
        $oneYearAgo = $year - 1;

        $yearPrepareBindings = array_map(fn() => "?", $years);
        $yearPrepareBindings = implode(", ", $yearPrepareBindings);

        $sql =
            "SELECT
                ap.tahun_audit AS year,
                AVG(fr.persentase_nilai_akhir) avg_nilai_akhir,
                AVG(fr.persentase_nilai_target) avg_nilai_target
            FROM core.unit_kerja o
            JOIN spmi.audit_periode ap ON ap.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit ja ON ja.id_audit_periode = ap.id
                AND ja.apakah_audit_aktif = true
                AND ja.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit_unit jau ON jau.id_jadwal_audit = ja.id
                AND jau.id_unit = o.id
            JOIN spmi.penilaian_panduan pp ON pp.id = jau.id_penilaian_panduan
                AND pp.apakah_aktif = true
                AND pp.waktu_dihapus IS NULL
            JOIN spmi.penilaian_audit pa ON
                pa.id_audit_periode = ap.id AND
                pa.id_unit = o.id AND
                pa.id_penilaian_panduan = pp.id AND
                pa.apakah_penilaian_mandiri = false AND
                pa.apakah_terfinalisasi = true
                AND pa.waktu_dihapus IS NULL
            JOIN spmi.hasil_akhir_audit fr ON fr.id_penilaian_audit = pa.id
                AND fr.waktu_dihapus IS NULL
            WHERE ap.tahun_audit::text IN (
                $yearPrepareBindings
            )";

        $bindings = [
            ...$years
        ];

        if ($studyProgramId) {
            $sql .= " AND fr.id_unit = ?";
            $bindings[] = $studyProgramId;
        }

        $sql .= " GROUP BY ap.id";

        $data = DB::select($sql, $bindings);
        $dataNilaiAkhir = array_column($data, 'avg_nilai_akhir', 'year');
        $dataNilaiTarget = array_column($data, 'avg_nilai_target', 'year');

        $resultDataNilaiAkhir = [];
        $resultDataNilaiTarget = [];
        $category = [];

        $totalData = count($data);
        if ($totalData == 1) {
            $years = range($year - 1, $year);
        } else if ($totalData <= 5) {
            $years = range($year - $totalData, $year);
        } else {
            $years = range($year - 5, $year);
        };

        foreach ($years as $year) {
            $category[] = $year;
            $resultDataNilaiAkhir[$year] = number_format($dataNilaiAkhir[$year] ?? 0, 2, '.', '');
            $resultDataNilaiTarget[$year] = number_format($dataNilaiTarget[$year] ?? 0, 2, '.', '');
        }

        return [
            'category' => $category,
            'data' => [
                'nilai_akhir' => array_values($resultDataNilaiAkhir),
                'nilai_target' => array_values($resultDataNilaiTarget)
            ],
            'active_period_rank' => $resultDataNilaiAkhir[$year] ?? 0,
            'previous_period_rank' => $resultDataNilaiAkhir[$oneYearAgo] ?? 0
        ];
    }

    protected static function showTargetScoreProgressPercent($auditPeriodId, $studyProgramId = null)
    {
        $sql =
            "SELECT
                    ap.tahun_audit periode_audit,
                    CASE WHEN at.id IS NOT NULL THEN at.id ELSE o.id END id,
                    CONCAT(d.kode_jenjang, ' - ', o.nama_unit) nama_unit,
                    fg.nama_pengisian_panduan,
                    ag.nama_penilaian_panduan,
                    CASE WHEN at.id IS NOT NULL THEN true ELSE false END apakah_sudah_dibuat,
                    ts.total total_target_terisi,
                    ppp.total_indikator_matriks
                FROM core.unit_kerja o
                JOIN spmi.audit_periode ap ON ap.id = :id_audit_periode AND ap.waktu_dihapus IS NULL
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
                    AND at.waktu_dihapus is null
                JOIN (
                    SELECT pp2.id, mpm.id_audit_periode, mpm.id_unit, COUNT(mpm.id) total_indikator_matriks FROM spmi.mapping_penilaian_matriks mpm
                    JOIN spmi.penilaian_matriks pm ON pm.id = mpm.id_penilaian_matriks AND pm.kategori_penilaian = '" . PenilaianMatriks::CATEGORY_INDICATOR . "'
                    JOIN spmi.penilaian_panduan pp2 ON pp2.id = pm.id_penilaian_panduan
                    WHERE pm.waktu_dihapus is null
                    GROUP BY pp2.id, mpm.id_audit_periode, mpm.id_unit
                ) ppp ON ppp.id = jau.id_penilaian_panduan AND ppp.id_audit_periode = ap.id AND ppp.id_unit = jau.id_unit
                LEFT JOIN (
                    SELECT ts.id_target_indikator, pm.id_penilaian_panduan, mpm.id_audit_periode, mpm.id_unit,
                        COUNT(DISTINCT pm.id) FILTER(WHERE pm.kategori_penilaian = '" . PenilaianMatriks::CATEGORY_INDICATOR . "') total FROM spmi.target_skor ts
                    JOIN spmi.penilaian_matriks pm ON pm.id = ts.id_penilaian_matriks
                    JOIN spmi.mapping_penilaian_matriks mpm ON mpm.id_penilaian_matriks = pm.id
                GROUP BY ts.id_target_indikator, pm.id_penilaian_panduan, mpm.id_audit_periode, mpm.id_unit
                ) ts ON
                    ts.id_target_indikator = at.id AND ts.id_penilaian_panduan = ag.id AND ts.id_audit_periode = ap.id AND ts.id_unit = o.id";

        $bindings = ['id_audit_periode' => $auditPeriodId];

        if (!empty($studyProgramId)) {
            $sql .= " WHERE o.id = '" . $studyProgramId . "'";
        }

        $sql .= " GROUP BY o.id, ap.id, ag.id, at.id, ts.total, d.id, ts.id_target_indikator, fg.id, ppp.total_indikator_matriks";

        $data = DB::select($sql, $bindings);

        $totalTarget = 0;
        $totalTargetTerisi = 0;
        foreach ($data as $item) {
            $totalTarget += $item->total_indikator_matriks;
            $totalTargetTerisi += $item->total_target_terisi;
        }
        $averageTarget = $totalTarget == 0 ? 0 : round(($totalTargetTerisi / $totalTarget) * 100, 0, PHP_ROUND_HALF_UP);

        return $averageTarget;
    }

    protected static function showPengisianIndikatorProgressPercent($auditPeriodId, $studyProgramId = null, $isSelfEvaluation = false)
    {
        if ($isSelfEvaluation) {
            $data = self::getProgressIndikatorEvaluasiDiri($auditPeriodId, $studyProgramId);
        } else {
            $data = self::getProgressIndikatorLaporanKinerja($auditPeriodId, $studyProgramId);
        }

        $data = array_map(function ($item) {
            $totalIndikator = $item->total_indikator == 0 ? 1 : $item->total_indikator;
            return ($item->total_indikator_terisi) / ($totalIndikator) * 100;
        }, $data);

        $averagePengisianIndikatorProgress = 0;
        if (!empty($data)) {
            $averagePengisianIndikatorProgress = round((array_sum($data) / count($data)), 0, PHP_ROUND_HALF_UP);
        }

        return $averagePengisianIndikatorProgress;
    }

    protected static function getProgressIndikatorLaporanKinerja($auditPeriodId, $studyProgramId = null)
    {
        $sql =
            "SELECT
                fg.id AS id_pengisian_panduan,
                ap.tahun_audit AS periode_audit,
                COALESCE(fi.id, o.id) AS id,
                CONCAT(d.kode_jenjang, ' - ', o.nama_unit) AS nama_unit,
                aa.nama_singkat_lembaga AS nama_lembaga_akreditasi,
                fg.nama_pengisian_panduan AS nama_pengisian_panduan,
                CASE WHEN fi.id IS NOT NULL THEN true ELSE false END AS is_created,
                COALESCE(r.total_indikator_terisi, 0) AS total_indikator_terisi,
                COALESCE(r2.total_indikator, 0) AS total_indikator,
                r2.id_indikator_laporan_kinerja
            FROM
                core.unit_kerja o
            JOIN spmi.audit_periode ap ON ap.id = :id_audit_periode AND ap.waktu_dihapus IS NULL
            JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
            JOIN spmi.jadwal_audit asch ON asch.id_audit_periode = ap.id AND asch.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit_unit aso ON aso.id_jadwal_audit = asch.id AND aso.id_unit = o.id
            JOIN spmi.pengisian_panduan fg ON fg.id = aso.id_pengisian_panduan AND fg.apakah_aktif = true AND fg.tipe_edisi = '" . AkreditasiBuku::PERFORMANCE_REPORT . "'
            JOIN core.lembaga_akreditasi aa ON aa.id = fg.id_lembaga_akreditasi
            JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = asch.id_audit_periode AND sa.waktu_dihapus IS NULL
            LEFT JOIN spmi.pengisian_indikator fi ON fi.id_unit = o.id
                AND fi.id_pengisian_panduan = aso.id_pengisian_panduan
                AND fi.id_audit_periode = ap.id
                AND fi.jenis_indikator = '" . AkreditasiBuku::PERFORMANCE_REPORT . "'
                AND fi.waktu_dihapus IS NULL
            LEFT JOIN (
                SELECT
                    ml.id_unit,
                    ml.id_audit_periode,
                    ipr.id_pengisian_panduan,
                    MIN(ipr.id) FILTER(WHERE ipr.apakah_parent = false) AS id_indikator_laporan_kinerja,
                    COUNT(DISTINCT ipr.id) FILTER(WHERE ipr.apakah_parent = false) AS total_indikator
                FROM
                    spmi.mapping_lk ml
                JOIN spmi.indikator_laporan_kinerja ipr ON ipr.id = ml.id_indikator_laporan_kinerja
                    AND ipr.waktu_dihapus IS NULL
                GROUP BY
                    ml.id_unit, ml.id_audit_periode, ipr.id_pengisian_panduan
            ) r2 ON r2.id_unit = o.id
                AND r2.id_audit_periode = ap.id
                AND r2.id_pengisian_panduan = fg.id
            LEFT JOIN (
                SELECT
                    fid.id_pengisian_indikator,
                    ml.id_audit_periode,
                    ml.id_unit,
                    COUNT(DISTINCT ipr.id) FILTER(WHERE ipr.apakah_parent = false) AS total_indikator_terisi
                FROM
                    spmi.data_pengisian_lk fid
                JOIN spmi.pengisian_indikator fi ON fi.id = fid.id_pengisian_indikator
                JOIN spmi.indikator_laporan_kinerja ipr ON ipr.id = fid.id_indikator_laporan_kinerja
                    AND ipr.waktu_dihapus IS NULL
                JOIN spmi.mapping_lk ml ON ml.id_indikator_laporan_kinerja = ipr.id
                WHERE fid.waktu_dihapus IS NULL GROUP BY
                    fid.id_pengisian_indikator, ml.id_audit_periode, ml.id_unit
            ) r ON r.id_pengisian_indikator = fi.id AND r.id_unit = o.id AND r.id_audit_periode = ap.id";

        $bindings['id_audit_periode'] = $auditPeriodId;

        if (!empty($studyProgramId)) {
            $sql .= " WHERE o.id = '" . $studyProgramId . "'";
        }

        $sql .= " GROUP BY o.id, ap.id, aa.id, fg.id, fi.id, r.total_indikator_terisi, r2.total_indikator, d.kode_jenjang, r2.id_indikator_laporan_kinerja";

        return DB::select($sql, $bindings);
    }

    protected static function getProgressIndikatorEvaluasiDiri($auditPeriodId, $studyProgramId = null)
    {
        $sql =
            "SELECT
                fg.id AS id_pengisian_panduan,
                ap.tahun_audit AS periode_audit,
                COALESCE(fi.id, o.id) AS id,
                CONCAT(d.kode_jenjang, ' - ', o.nama_unit) AS nama_unit,
                aa.nama_singkat_lembaga AS nama_lembaga_akreditasi,
                fg.nama_pengisian_panduan AS nama_pengisian_panduan,
                CASE WHEN fi.id IS NOT NULL THEN true ELSE false END AS is_created,
                COALESCE(r.total_indikator_terisi, 0) AS total_indikator_terisi,
                COALESCE(r2.total_indikator, 0) AS total_indikator,
                r2.id_indikator_evaluasi_diri
            FROM
                core.unit_kerja o
            JOIN spmi.audit_periode ap ON ap.id = :id_audit_periode AND ap.waktu_dihapus IS NULL
            JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
            JOIN spmi.jadwal_audit asch ON asch.id_audit_periode = ap.id AND asch.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit_unit aso ON aso.id_jadwal_audit = asch.id AND aso.id_unit = o.id
            JOIN spmi.pengisian_panduan fg2 ON fg2.id = aso.id_pengisian_panduan AND fg2.apakah_aktif = true AND fg2.tipe_edisi = '" . AkreditasiBuku::PERFORMANCE_REPORT . "'
            JOIN spmi.pengisian_panduan fg ON fg.id = fg2.id_pengisian_panduan
                AND fg.apakah_aktif = true
                AND fg.tipe_edisi = '" . AkreditasiBuku::SELF_EVALUATION . "'
            JOIN core.lembaga_akreditasi aa ON aa.id = fg.id_lembaga_akreditasi
            JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = asch.id_audit_periode AND sa.waktu_dihapus IS NULL
            LEFT JOIN spmi.pengisian_indikator fi ON fi.id_unit = o.id
                AND fi.id_pengisian_panduan = fg.id
                AND fi.id_audit_periode = ap.id
                AND fi.jenis_indikator = '" . AkreditasiBuku::SELF_EVALUATION . "'
                AND fi.waktu_dihapus IS NULL
            LEFT JOIN (
                SELECT
                    ml.id_unit,
                    ml.id_audit_periode,
                    ipr.id_pengisian_panduan,
                    MIN(ipr.id) FILTER(WHERE ipr.apakah_parent = false) AS id_indikator_evaluasi_diri,
                    COUNT(DISTINCT ipr.id) FILTER(WHERE ipr.apakah_parent = false) AS total_indikator
                FROM
                    spmi.mapping_led ml
                JOIN spmi.indikator_evaluasi_diri ipr ON ipr.id = ml.id_indikator_evaluasi_diri
                    AND ipr.waktu_dihapus IS NULL
                GROUP BY
                    ml.id_unit, ml.id_audit_periode, ipr.id_pengisian_panduan
            ) r2 ON r2.id_unit = o.id
                AND r2.id_audit_periode = ap.id
                AND r2.id_pengisian_panduan = fg.id
            LEFT JOIN (
                SELECT
                    fid.id_pengisian_indikator,
                    ml.id_audit_periode,
                    ml.id_unit,
                    COUNT(DISTINCT ipr.id) FILTER(WHERE ipr.apakah_parent = false) AS total_indikator_terisi
                FROM
                    spmi.data_pengisian_led fid
                JOIN spmi.pengisian_indikator fi ON fi.id = fid.id_pengisian_indikator
                JOIN spmi.indikator_evaluasi_diri ipr ON ipr.id = fid.id_indikator_evaluasi_diri
                    AND ipr.waktu_dihapus IS NULL
                JOIN spmi.mapping_led ml ON ml.id_indikator_evaluasi_diri = ipr.id
                WHERE fid.waktu_dihapus IS NULL GROUP BY
                    fid.id_pengisian_indikator, ml.id_audit_periode, ml.id_unit
            ) r ON r.id_pengisian_indikator = fi.id AND r.id_unit = o.id AND r.id_audit_periode = ap.id";

        $bindings['id_audit_periode'] = $auditPeriodId;

        if (!empty($studyProgramId)) {
            $sql .= " WHERE o.id = '" . $studyProgramId . "'";
        }

        $sql .= " GROUP BY o.id, ap.id, aa.id, fg.id, fi.id, r.total_indikator_terisi, r2.total_indikator, d.kode_jenjang, r2.id_indikator_evaluasi_diri";

        return DB::select($sql, $bindings);
    }

    protected static function getPenilaianAuditors($auditPeriodId, $studyProgramId = null)
    {
        $sql =
            "SELECT
                ap.tahun_audit periode_audit,
                COALESCE(pa.id, uk.id) id,
                uk.id AS id_unit,
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
            JOIN spmi.audit_periode ap ON ap.id = :id_audit_periode AND ap.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit ja ON ja.id_audit_periode = ap.id AND ja.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit_unit jau ON jau.id_jadwal_audit = ja.id AND jau.id_unit = uk.id
            JOIN spmi.penilaian_panduan pp ON pp.id = jau.id_penilaian_panduan AND pp.apakah_aktif = true AND pp.waktu_dihapus IS NULL
            JOIN (
                SELECT pp2.id, mpm.id_audit_periode, mpm.id_unit, COUNT(mpm.id) total_indikator_matriks FROM spmi.mapping_penilaian_matriks mpm
                JOIN spmi.penilaian_matriks pm ON pm.id = mpm.id_penilaian_matriks AND pm.kategori_penilaian = '" . PenilaianMatriks::CATEGORY_INDICATOR . "'
                JOIN spmi.penilaian_panduan pp2 ON pp2.id = pm.id_penilaian_panduan
                WHERE pm.waktu_dihapus is null
                GROUP BY pp2.id, mpm.id_audit_periode, mpm.id_unit
            ) ppp ON ppp.id = jau.id_penilaian_panduan AND ppp.id_audit_periode = ja.id_audit_periode AND ppp.id_unit = jau.id_unit
            LEFT JOIN spmi.penilaian_audit pa ON
                pa.id_audit_periode = ap.id AND
                pa.id_unit = uk.id AND
                pa.id_penilaian_panduan = pp.id AND
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
                    psk.id_penilaian_audit,
                    count(*) total_indikator_terisi
                FROM spmi.penilaian_skor psk
                JOIN spmi.penilaian_matriks pm ON pm.id = psk.id_penilaian_matriks
                WHERE pm.kategori_penilaian = '" . PenilaianMatriks::CATEGORY_INDICATOR . "'
                GROUP BY (psk.id_penilaian_audit)
            ) ps2 ON ps2.id_penilaian_audit = pa.id
            JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = ja.id_audit_periode
                AND sa.waktu_dihapus IS NULL
            JOIN (
                spmi.surat_tugas_auditor_pegawai stap
                JOIN core.biodata lp ON lp.id = stap.id_personil
                    AND lp.waktu_dihapus IS NULL
            ) ON stap.id_unit = uk.id
                AND stap.posisi = '" . SuratTugasAuditorPegawai::POSITION_LEAD . "'
                AND stap.id_surat_tugas_auditor = sa.id
                AND stap.waktu_dihapus IS null";

        $bindings['id_audit_periode'] = $auditPeriodId;

        $sql .= " GROUP BY uk.id, ap.id, la.id, pp.id, pa.id, ps.apakah_punya_feedback, jp.kode_jenjang, ps2.total_indikator_terisi";

        $data = DB::select($sql, $bindings);
        $data = array_map(function ($item) {
            $item = (array) $item;
            $totalIndikatorMatriks = $item['total_indikator_matriks'] > 0 ? $item['total_indikator_matriks'] : 1;
            $item['total_penilaian_terisi_percent'] = ($item['total_penilaian_terisi'] / $totalIndikatorMatriks) * 100;

            return $item;
        }, $data);

        return $data;
    }

    protected static function showRawCriteriaResultChart($auditPeriodId, $studyProgramId = null)
    {
        $sql =
            "SELECT
                u.id,
                fr.nomor_penilaian,
                fr.apakah_data_default,
                fr.id_akreditasi_standar,
                fr.pertanyaan_penilaian,
                fr.nama_standar,
                fr.nilai_akhir,
                fr.bobot_target,
                fr.bobot_default,
                fr.info_level,
                fr.butir_indikator_spme,
                ha.id AS id_hasil_akhir_audit
            FROM core.unit_kerja u
            JOIN spmi.audit_periode ap ON ap.id = :id_audit_periode AND ap.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit ja ON ja.id_audit_periode = ap.id
            JOIN spmi.jadwal_audit_unit jau ON jau.id_jadwal_audit = ja.id AND jau.id_unit = u.id
            JOIN spmi.penilaian_panduan pp ON pp.id = jau.id_penilaian_panduan AND pp.apakah_aktif = true AND pp.waktu_dihapus IS NULL
            JOIN spmi.penilaian_audit ass ON
                ass.id_audit_periode = ap.id AND
                ass.id_unit = u.id AND
                ass.id_penilaian_panduan = pp.id AND
                ass.apakah_penilaian_mandiri = false
                AND ass.apakah_terfinalisasi = true
            JOIN spmi.hasil_akhir_audit ha ON ha.id_penilaian_audit = ass.id
            JOIN (
                SELECT
                    sc.id_penilaian_audit,
                    m.id_penilaian_panduan,
                    m.nomor_penilaian,
                    m.apakah_data_default,
                    m.id_akreditasi_standar,
                    m.info_left,
                    m.info_level,
                    m.pertanyaan_penilaian,
                    concat(ast.kode_standar, ' ', ast.nama_standar) as nama_standar,
                    m.butir_indikator_spme,
                    sc.nilai_akhir,
                    sc.nilai_target AS bobot_target,
                    sc.max_nilai_target bobot_default
                FROM spmi.penilaian_matriks m
                JOIN spmi.akreditasi_standar ast ON ast.id = m.id_akreditasi_standar AND ast.waktu_dihapus IS NULL
                LEFT JOIN spmi.penilaian_skor sc ON sc.id_penilaian_matriks = m.id
                WHERE m.jenis_penilaian = '" . PenilaianMatriks::TYPE_FINAL_SCORE . "'
                    AND m.waktu_dihapus IS NULL
                GROUP BY sc.id, m.id, ast.nama_standar, ast.kode_standar
                ORDER BY m.info_left
            ) fr ON fr.id_penilaian_audit = ass.id
                AND fr.id_penilaian_panduan = ass.id_penilaian_panduan";

        if ($studyProgramId) {
            $sql .= " WHERE u.id = :id_unit";
            $bindings['id_unit'] = $studyProgramId;
        }

        $bindings['id_audit_periode'] = $auditPeriodId;

        $sql .= " ORDER BY fr.nomor_penilaian ASC";

        $data = DB::select($sql, $bindings);

        // Sort ascending
        usort($data, function ($a, $b) {
            $numA = (int) preg_replace('/[^0-9]/', '', $a->nama_standar);
            $numB = (int) preg_replace('/[^0-9]/', '', $b->nama_standar);

            $prefixA = substr($a->nama_standar, 0, strpos($a->nama_standar, '.'));
            $prefixB = substr($b->nama_standar, 0, strpos($b->nama_standar, '.'));

            $prefixCompare = strcmp($prefixA, $prefixB);

            if ($prefixCompare == 0) {
                return $numA - $numB;
            }

            return $prefixCompare;
        });

        $resultData = [];
        $resultBobotTarget = [];

        foreach ($data as $item) {
            $bobotDefault = $item->bobot_default;
            if (empty($bobotDefault)) {
                $bobotDefault = 1;
            }

            $percentage = ($item->nilai_akhir / $bobotDefault) * 100;
            $resultData[$item->id][$item->nama_standar] = $percentage;

            // Bobot target
            $percentageBobotTarget = ($item->bobot_target / $bobotDefault) * 100;
            $resultBobotTarget[$item->id][$item->nomor_penilaian] = $percentageBobotTarget;
        }

        $recentSum = [];
        foreach ($resultData as $k => $item) {
            if (empty($resultData[$k])) {
                continue;
            }

            foreach ($resultData[$k] as $column => $value) {
                if (empty($recentSum[$column])) {
                    $recentSum[$column] = $value;
                    continue;
                }

                $recentSum[$column] += $value;
            }
        }

        $recentSum = array_map(
            function ($item, $key) {
                return [
                    'label' => $key,
                    'value' => $item
                ];
            },
            $recentSum,
            array_keys($recentSum)
        );

        $recentSum = array_column($recentSum, 'value', 'label');

        $recentSumBobot = [];
        foreach ($resultBobotTarget as $k => $item) {
            if (empty($resultBobotTarget[$k])) {
                continue;
            }

            foreach ($resultBobotTarget[$k] as $column => $value) {
                if (empty($recentSumBobot[$column])) {
                    $recentSumBobot[$column] = $value;
                    continue;
                }

                $recentSumBobot[$column] += $value;
            }
        }

        $resultBobotSummary = array_map(
            fn($item) => number_format($item / count($resultBobotTarget), 2, '.', ''),
            $recentSumBobot
        );

        $resultSummary = array_map(
            fn($item) => number_format($item / count($resultData), 2, '.', ''),
            $recentSum
        );

        $mappedCategory = [];
        foreach ($resultSummary as $k => $item) {
            $mappedCategory[] = [$k, "$item%"];
        }

        return [
            'category' => $mappedCategory,
            'data' => [
                'ketercapaian' => array_values($resultSummary),
                'bobot_target' => array_values($resultBobotSummary)
            ]
        ];
    }

    protected static function findRank($auditPeriodId, $rankScore)
    {
        $qualityRates = SpmiPeringkat::where('id_audit_periode', $auditPeriodId)
            ->where('waktu_dihapus', null)
            ->orderBy('skor_minimal', 'asc')
            ->get();

        // Ambil Peringkat
        $rank['data'] = $qualityRates->first(function ($item) use ($rankScore) {
            return $rankScore >= $item->skor_minimal && $rankScore <= $item->skor_maksimal;
        });

        $rank['total'] = $qualityRates->count();

        $i = $rank['total'];
        foreach ($qualityRates as $val) {
            if ($rankScore >= $val->skor_minimal && $rankScore <= $val->skor_maksimal) {
                break;
            }
            $i--;
        };

        $rank['summary'] = $i;

        return $rank;
    }

    protected static function isOdd($number)
    {
        if ($number % 2 != 0) {
            return true;
        }

        return false;
    }
}
