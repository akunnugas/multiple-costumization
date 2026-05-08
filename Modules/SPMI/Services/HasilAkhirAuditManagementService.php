<?php

namespace Modules\SPMI\Services;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Helpers\SessionManager;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\Service;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\Gate\Models\Role;
use Modules\SPMI\Models\AkreditasiPeringkat;
use Modules\SPMI\Models\AkreditasiStatus;
use Modules\SPMI\Models\AkreditasiSyarat;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\AuditTemuan;
use Modules\SPMI\Models\HasilAkhirAudit;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Models\MappingPenilaianMatriks;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\SuratTugasAuditor;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;

class HasilAkhirAuditManagementService extends Service
{
    /**
     * @var HasilAkhirAudit
     */
    protected $model = HasilAkhirAudit::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new HasilAkhirAudit;
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

        $sql =
            "SELECT
                fr.id,
                ap.tahun_audit periode_audit,
                ja.nama_jadwal_audit,
                concat(jp.kode_jenjang, ' - ', o.nama_unit) as nama_unit,
                CASE
                    WHEN ast.id IS NOT NULL
                        THEN fr.persentase_nilai_akhir::text
                    ELSE
                        fr.nilai_iku::text
                END AS nilai_iku,
                fr.nilai_ikt,
                CASE
                    WHEN ast.id IS NOT NULL
                        THEN fr.nilai_akhir::text
                    ELSE
                        fr.persentase_nilai_akhir::text
                END AS persentase_nilai_akhir,
                qr.nama_spmi_peringkat,
                coalesce(ar.nama_peringkat_akreditasi, ast.nama_status, '-') as nama_peringkat_akreditasi,
                pp.nama_singkat AS nama_penilaian_panduan
            FROM core.unit_kerja o
            JOIN core.jenjang_pendidikan jp ON jp.id = o.id_jenjang_pendidikan AND jp.waktu_dihapus IS NULL
            JOIN spmi.audit_periode ap ON ap.id = ? AND ap.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit ja ON ja.id_audit_periode = ap.id AND ja.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit_unit jau ON jau.id_jadwal_audit = ja.id AND jau.id_unit = o.id
            JOIN spmi.penilaian_panduan pp ON pp.id = jau.id_penilaian_panduan AND pp.apakah_aktif = true AND pp.waktu_dihapus IS NULL
            JOIN spmi.penilaian_audit pa ON
                pa.id_audit_periode = ap.id AND
                pa.id_unit = o.id AND
                pa.id_penilaian_panduan = pp.id AND
                pa.id_jadwal_audit = ja.id AND
                pa.apakah_penilaian_mandiri = false
                AND pa.apakah_terfinalisasi = true
            JOIN spmi.hasil_akhir_audit fr ON fr.id_penilaian_audit = pa.id
            LEFT JOIN spmi.spmi_peringkat qr ON qr.id = fr.id_spmi_peringkat AND qr.waktu_dihapus IS NULL
            LEFT JOIN spmi.akreditasi_peringkat ar ON ar.id = fr.id_akreditasi_peringkat AND ar.waktu_dihapus IS NULL
            LEFT JOIN spmi.akreditasi_status ast ON ast.id = fr.id_status_peringkat AND ast.waktu_dihapus IS NULL
            JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = ja.id_audit_periode
                AND sa.waktu_dihapus IS NULL
            JOIN spmi.surat_tugas_auditor_pegawai stap ON stap.id_surat_tugas_auditor = sa.id
                AND stap.id_unit = o.id
                AND stap.posisi = ?
                AND stap.waktu_dihapus IS NULL
            JOIN core.biodata lp ON lp.id = stap.id_personil
                AND lp.waktu_dihapus IS NULL";

        $fieldMap = [
            'id_audit_periode' => 'ap.id',
            'id_unit_kerja' => 'o.id',
            'id_jenjang_pendidikan' => 'jp.id',
            'periode_audit' => 'ap.tahun_audit',
        ];

        $defaultFilter = "fr.waktu_dihapus IS NULL";

        $bindings = [(int) $auditPeriodId, SuratTugasAuditorPegawai::POSITION_LEAD];

        $isInternalRole = SessionManager::isInternalRole();
        if (!$isInternalRole) {
            [$sql, $bindings] = $this->showIndexByRole($sql, $bindings);
            [$sql, $bindings] = $this->showIndexByUnitKerja($sql, $bindings);
        }

        [$sql, $bindings] = Pagination::buildQuery(
            bindings: ($bindings),
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    public function showRawScoreChart($idPenilaianAudit)
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
                fr.butir_indikator_spme
            FROM spmi.hasil_akhir_audit ha
            JOIN core.unit_kerja u ON u.id = ha.id_unit
                AND u.waktu_dihapus IS NULL
            JOIN spmi.penilaian_audit ass ON ass.id_unit = u.id
                AND ass.id = ha.id_penilaian_audit
                AND ass.apakah_terfinalisasi = true
                AND ass.waktu_dihapus IS NULL
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
                AND fr.id_penilaian_panduan = ass.id_penilaian_panduan
            WHERE ha.id = :id_penilaian_audit
            ORDER BY fr.info_left ASC";

        $bindings['id_penilaian_audit'] = $idPenilaianAudit;

        $data = DB::select($sql, $bindings);
        $result['chart']["ami"] = $this->processCountChart($data, "ami");
        $result['chart']["spme"] = $this->processCountChart($data, "spme");
        $result["data_table"] = $this->processElementScore($data);

        return $result;
    }

    protected function processCountChart($data, $category = "ami")
    {
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

        // Jika spme maka hanya ambil kolom apakah_data_default yang bernilai true
        if ($category === 'spme') {
            $data = array_filter($data, fn($item) => ($item->apakah_data_default === true || $item->butir_indikator_spme === true));
        }

        // Akumulasi raw values dari query
        $rawNilaiAkhir = [];
        $rawBobotDefault = [];
        $rawBobotTarget = [];

        foreach ($data as $item) {
            $bobotDefault = $item->bobot_default;
            if (empty($bobotDefault)) {
                $bobotDefault = 1;
            }

            if (!isset($rawNilaiAkhir[$item->id][$item->nama_standar])) {
                $rawNilaiAkhir[$item->id][$item->nama_standar] = 0;
                $rawBobotDefault[$item->id][$item->nama_standar] = 0;
                $rawBobotTarget[$item->id][$item->nama_standar] = 0;
            }

            $rawNilaiAkhir[$item->id][$item->nama_standar] += (float) $item->nilai_akhir;
            $rawBobotDefault[$item->id][$item->nama_standar] += (float) $bobotDefault;
            $rawBobotTarget[$item->id][$item->nama_standar] += (float) $item->bobot_target;
        }

        // Hitung persentase dari akumulasi raw values
        $resultData = [];
        $resultBobotTarget = [];

        foreach ($rawNilaiAkhir as $id => $standars) {
            foreach ($standars as $namaStandar => $totalNilaiAkhir) {
                $totalBobotDefault = $rawBobotDefault[$id][$namaStandar];
                if (empty($totalBobotDefault)) {
                    $totalBobotDefault = 1;
                }

                $resultData[$id][$namaStandar] = ($totalNilaiAkhir / $totalBobotDefault) * 100;

                // Bobot target
                $percentageBobotTarget = ($totalBobotDefault / $totalBobotDefault) * 100;

                // Jika ami maka target capaian dicompare dengan bobot default
                if ($category === "ami") {
                    $totalBobotTargetVal = $rawBobotTarget[$id][$namaStandar];
                    $percentageBobotTarget = ($totalBobotTargetVal / $totalBobotDefault) * 100;
                }

                $resultBobotTarget[$id][$namaStandar] = $percentageBobotTarget;
            }
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

        $dataKategoriBelumTercapai = array_map(function ($ketercapaian, $bobot, $namaKategori) {
            if ($ketercapaian < $bobot) {
                return $namaKategori;
            }

            return false;
        }, $resultSummary, $resultBobotSummary, array_keys($resultSummary));

        $dataKategoriBelumTercapai = array_values(array_filter($dataKategoriBelumTercapai));

        return [
            'category' => $mappedCategory,
            'data' => [
                'ketercapaian' => array_values($resultSummary),
                'bobot_target' => array_values($resultBobotSummary),
                'kategori_belum_tercapai' => $dataKategoriBelumTercapai
            ]
        ];
    }

    public function processElementScore($data)
    {
        // Mapping skor akhir
        $data = array_map(function ($item) {
            $item = (array) $item;
            $defaultTarget = $item['bobot_default'];

            if (empty($defaultTarget)) {
                $defaultTarget = 1;
            }

            $persentaseNilaiAkhir = (((float) $item['nilai_akhir']) / $defaultTarget) * 100;
            $persentaseBobotTarget = (((float) $item['bobot_target']) / $defaultTarget) * 100;
            $item['persentase_nilai_akhir'] = number_format($persentaseNilaiAkhir, 2, '.', ',');
            $item['persentase_bobot_target'] = number_format($persentaseBobotTarget, 2, '.', ',');

            return $item;
        }, $data);

        $totalShowData = count($data);
        $totalShowData = $totalShowData > 0 ? $totalShowData : 1;

        // Menambahkan array total diakhir
        $total = [
            'nomor_penilaian' => 'total',
            'id_akreditasi_standar' => null,
            'apakah_nilai_ditampilkan' => true,
            'info_level' => 0,
            'pertanyaan_penilaian' => 'Total',
            'nilai_akhir' => number_format(array_sum(array_column($data, 'nilai_akhir')), 2, '.', ','),
            'bobot_target' => number_format(array_sum(array_column($data, 'bobot_target')), 2, '.', ','),
            'persentase_nilai_akhir' => number_format(array_sum(array_column($data, 'persentase_nilai_akhir')) / $totalShowData, 2, '.', ','),
            'persentase_bobot_target' => number_format(array_sum(array_column($data, 'persentase_bobot_target')) / $totalShowData, 2, '.', ',')
        ];

        $data = array_merge($data, [$total]);

        return $data;
    }

    public function getElementScore(int $id)
    {
        $hasilAkhirAudit = HasilAkhirAudit::find($id);
        $sql =
            "SELECT
                m.nomor_penilaian,
                m.pertanyaan_penilaian,
                m.id_akreditasi_standar,
                m.info_level,
                m.apakah_nilai_ditampilkan,
                m.apakah_data_default,
                sc.nilai_akhir skor_penilaian,
                sc.nilai_target
            FROM spmi.penilaian_matriks m
            LEFT JOIN spmi.penilaian_skor sc ON sc.id_penilaian_matriks = m.id
                AND sc.id_penilaian_audit = :id_penilaian_audit
            WHERE m.kategori_penilaian = :kategori_penilaian_elemen
                AND (m.info_level = 1 OR m.info_level = 0)
                AND m.apakah_nilai_ditampilkan = true
                AND m.waktu_dihapus IS NULL
            ORDER BY m.info_left, m.apakah_data_default ASC";

        $data = DB::select($sql, [
            'id_penilaian_audit' => $hasilAkhirAudit?->id_penilaian_audit,
            'kategori_penilaian_elemen' => PenilaianMatriks::CATEGORY_ELEMENT
        ]);

        // Mapping skor akhir
        $data = array_map(function ($item) {
            $item = (array) $item;
            $item['nilai_target'] = (float)number_format($item['nilai_target'], 2, '.', ',');
            $targetWeight = $item['nilai_target'];

            if (empty($targetWeight)) {
                $targetWeight = 1;
            }

            if ($item['nomor_penilaian'] === 'C') {
                $item['nilai_akhir'] = null;
                $item['nilai_target'] = null;
                $item['skor_penilaian'] = null;
            } else {
                $finalScore = (((float) $item['skor_penilaian']) / $targetWeight) * 100;
                $item['nilai_akhir'] = number_format($finalScore, 2, '.', ',');
            }

            return $item;
        }, $data);

        $totalShowData = count(array_filter($data, fn($item) => $item['nomor_penilaian'] !== 'C'));
        $totalShowData = $totalShowData > 0 ? $totalShowData : 1;

        // Menambahkan array total diakhir
        $total = [
            'nomor_penilaian' => 'total',
            'id_akreditasi_standar' => null,
            'apakah_nilai_ditampilkan' => true,
            'info_level' => 0,
            'pertanyaan_penilaian' => 'Total',
            'skor_penilaian' => number_format(array_sum(array_column($data, 'skor_penilaian')), 2, '.', ','),
            'nilai_target' => number_format(array_sum(array_column($data, 'nilai_target')), 2, '.', ','),
            'nilai_akhir' => number_format(array_sum(array_column($data, 'nilai_akhir')) / $totalShowData, 2, '.', ',')
        ];

        $data = array_merge($data, [$total]);

        return $data;
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return HasilAkhirAudit|Error
     */
    public function show(int $id): HasilAkhirAudit|Error
    {
        $table = $this->model->getTable();

        $positionLead = SuratTugasAuditorPegawai::POSITION_LEAD;

        $sql =
            "SELECT
                fr.*,
                ag.apakah_menggunakan_peringkat,
                ag.nama_singkat panduan_penilaian,
                CONCAT(p.gelar_depan, ' ', p.nama, ' ', p.gelar_belakang) ketua_auditor,
                qr.nama_spmi_peringkat,
                ar.kode_peringkat kode_peringkat_akreditasi,
                d.kode_jenjang,
                d.nama_jenjang,
                ap.tahun_audit periode_audit,
                asch.nama_jadwal_audit,
                asch.tanggal_awal_penilaian,
                asch.tanggal_akhir_penilaian,
                ass.id_penilaian_panduan,
                sta.id as id_surat_tugas_auditor,
                CONCAT(d.kode_jenjang, ' - ', o.nama_unit) nama_prodi,
                fc.nama_unit nama_fakultas,
                ar.nama_peringkat_akreditasi,
                CONCAT(pau.gelar_depan, ' ', pau.nama, ' ', pau.gelar_belakang) kaprodi
            FROM $table fr
            JOIN spmi.penilaian_audit ass ON ass.id = fr.id_penilaian_audit
                AND ass.apakah_terfinalisasi = true
                AND ass.apakah_penilaian_mandiri = false
                AND ass.waktu_dihapus IS NULL
            JOIN spmi.surat_tugas_auditor sta on sta.id_audit_periode = fr.id_audit_periode
                AND sta.waktu_dihapus is null
            JOIN spmi.surat_tugas_auditor_pegawai stap on sta.id = stap.id_surat_tugas_auditor and stap.posisi = '$positionLead'
                AND stap.id_unit = fr.id_unit
            JOIN spmi.audit_periode ap ON ap.id = fr.id_audit_periode
                AND ap.waktu_dihapus IS NULL
            JOIN spmi.penilaian_panduan ag ON ag.id = ass.id_penilaian_panduan
                AND ag.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit_unit aso ON aso.id_unit = fr.id_unit
                AND aso.id_penilaian_panduan = ag.id
            JOIN spmi.jadwal_audit asch ON asch.id = aso.id_jadwal_audit
                AND asch.id_audit_periode = fr.id_audit_periode
                AND asch.id = ass.id_jadwal_audit
                AND asch.apakah_audit_aktif = true
                AND asch.waktu_dihapus is null
            JOIN core.biodata p ON p.id = stap.id_personil
                AND p.waktu_dihapus IS NULL
            JOIN core.unit_kerja o ON o.id = fr.id_unit
                AND ag.waktu_dihapus IS NULL
            LEFT JOIN core.pegawai pwa ON pwa.id = o.id_pimpinan
                AND pwa.waktu_dihapus IS NULL
            LEFT JOIN core.biodata pau ON pau.id = pwa.id_biodata
                AND pau.waktu_dihapus IS NULL
            LEFT JOIN core.unit_kerja fc ON fc.id = o.id_parent
                AND fc.waktu_dihapus IS NULL
            JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
                AND d.waktu_dihapus IS NULL
            LEFT JOIN spmi.spmi_peringkat qr ON qr.id = fr.id_spmi_peringkat
                AND qr.waktu_dihapus IS NULL
            LEFT JOIN spmi.akreditasi_status ast ON ast.id = fr.id_status_peringkat
                AND ast.waktu_dihapus IS NULL
            LEFT JOIN spmi.akreditasi_peringkat ar ON ar.id = fr.id_akreditasi_peringkat
                AND ar.waktu_dihapus IS NULL
            WHERE fr.id = :id AND fr.waktu_dihapus IS NULL
            LIMIT 1";

        $data = DB::select($sql, [
            'id' => $id
        ]);

        if (empty($data)) {
            abort(redirect()->to(route('spmi.reports.document-reports.show'))->with('error', 'Data hasil akhir audit tidak ditemukan.'));
        }

        $model = $this->model::hydrate((array) $data);
        $model = $model->where('id', $id)->first();

        $suratTugasAuditor = SuratTugasAuditor::find($model->id_surat_tugas_auditor);
        $suratTugasAuditorAnggota = SuratTugasAuditorPegawai::with('biodata')
            ->where('posisi', SuratTugasAuditorPegawai::POSITION_MEMBER)
            ->where('id_surat_tugas_auditor', $suratTugasAuditor->id)
            ->where('id_unit', $model->id_unit)
            ->get();

        if (empty($model)) {
            throw new ModelNotFoundException();
        }

        $isHasButirSPME = PenilaianMatriks::where('id_penilaian_panduan', $model->id_penilaian_panduan)
            ->where('kategori_penilaian', PenilaianMatriks::CATEGORY_INDICATOR)
            ->where('butir_indikator_spme', true)
            ->exists();
        if ($isHasButirSPME) {
            if ($model->apakah_menggunakan_peringkat) {
                $akreditasiPeringkat = AkreditasiPeringkat::where('waktu_dihapus', null)
                    ->where('id_penilaian_panduan', $model->id_penilaian_panduan)
                    ->orderBy('nilai_minimal', 'asc')
                    ->get();

                // Ambil Peringkat akreditasi
                $finalScore = $model->nilai_iku;
                $akreditasiPeringkat = $akreditasiPeringkat->first(function ($item) use ($finalScore) {
                    return $finalScore >= $item->nilai_minimal && $finalScore <= $item->nilai_maksimal;
                });

                $comparedScoreByRequirements = PenilaianAuditorManagementService::getComparedScoreByRequirement($model->id_audit_periode, $model->id_unit, $akreditasiPeringkat->id);
                $AkreditasiSyarat = array_filter(
                    $comparedScoreByRequirements,
                    fn($item) => $item['jenis_syarat_akreditasi'] === AkreditasiSyarat::TYPE_ACCREDITED
                );
                $totalAkreditasiSyarat = count($AkreditasiSyarat);
                $totalAkreditasiSyaratAchieved = count(array_filter(
                    $AkreditasiSyarat,
                    fn($item) => $item['apakah_terpenuhi'] === true
                ));

                $isAkreditasiSyaratAchieved = $totalAkreditasiSyarat === $totalAkreditasiSyaratAchieved;
                $model->apakah_syarat_terakreditasi_terpenuhi = $isAkreditasiSyaratAchieved;

                if (!$model->apakah_syarat_terakreditasi_terpenuhi) {
                    $model->butir_tidak_terpenuhi = '<ul>' . implode('', array_map(
                        fn($item) => '<li>' . $item['pertanyaan_penilaian'] . '</li>',
                        array_filter(
                            $AkreditasiSyarat,
                            fn($item) => $item['apakah_terpenuhi'] === false
                        )
                    )) . '</ul>';
                } else {
                    $model->butir_tidak_terpenuhi = null;
                }
            } else {
                $tempPersentaseNilaiAkhir = $model->persentase_nilai_akhir;
                $model->persentase_nilai_akhir = $model->nilai_akhir;
                $model->nilai_iku = $tempPersentaseNilaiAkhir . '%';
                $model->apakah_syarat_terakreditasi_terpenuhi = $model->apakah_status_terakreditasi;

                $akreditasiStatus = AkreditasiStatus::find($model->id_status_peringkat);
                $model->nama_akreditasi_status = $akreditasiStatus?->nama_status ?? '-';
            }
        } else {
            $model->apakah_syarat_terakreditasi_terpenuhi = null;
            $model->butir_tidak_terpenuhi = null;
        }

        $model->anggota_auditor = $suratTugasAuditorAnggota->count() > 0 ? $suratTugasAuditorAnggota->map(function ($item) {
            return trim(
                ($item->biodata->gelar_depan ?? '') . ' ' .
                    $item->biodata->nama . ' ' .
                    ($item->biodata->gelar_belakang ?? '')
            );
        })->implode('<br>') : '-';

        return $model;
    }

    public function showByPeriodeAudit($idPeriodeAudit, $idUnit, $idJadwalAudit = null)
    {
        $query = PenilaianAudit::where('id_audit_periode', $idPeriodeAudit)
            ->where('id_unit', $idUnit)
            ->where('apakah_penilaian_mandiri', false);

        if (!empty($idJadwalAudit)) {
            $query->where('id_jadwal_audit', $idJadwalAudit);
        }

        $penilaianAudit = $query->first();

        if (empty($penilaianAudit)) {
            return new Error('Data tidak ditemukan', 404);
        }

        $hasilAkhirAudit = HasilAkhirAudit::where('id_unit', $idUnit)
            ->where('id_penilaian_audit', $penilaianAudit->id)
            ->first();

        if (empty($hasilAkhirAudit)) {
            return new Error('Data tidak ditemukan', 404);
        }

        return $this->show($hasilAkhirAudit->id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return HasilAkhirAudit
     */
    public function store(array $data): HasilAkhirAudit
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return HasilAkhirAudit
     */
    public function update(array $data, int $id): HasilAkhirAudit
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

    public function generateReport($id)
    {
        $data['self'] = $this->show($id);
        $data['university'] = UnitKerja::where('jenis_unit', UnitKerja::UNIVERSITY)->first('nama_unit');
        $data['members'] = PenilaianAuditorManagementService::getAuditorMember($data['self']->id_penilaian_audit);
        $rawScore = $this->showRawScoreChart($id);

        $data['is_has_butir_spme'] = PenilaianMatriks::where(
            'id_penilaian_panduan',
            $data['self']->id_penilaian_panduan,
        )
            ->where('kategori_penilaian', PenilaianMatriks::CATEGORY_INDICATOR)
            ->where('butir_indikator_spme', true)
            ->exists();

        $data['chart_ami'] = $rawScore['chart']['ami'];
        $data['criteria_data'] = $data['chart_ami']['category'];

        // Ambil Temuan Audit
        $auditFindings = AuditTemuanManagementService::showAllMatricesByPenilaianPanduan(
            $data['self']['id_penilaian_panduan'],
            $data['self']['id_unit'],
            $data['self']['id_audit_periode'],
            $data['self']['id_penilaian_audit'],
            true,
            $data['self']['id_jadwal_audit'] ?? null
        );

        // Total Matriks Penilaian
        $data['totals']['total_matrix'] = MappingPenilaianMatriks::join('spmi.penilaian_matriks', 'spmi.penilaian_matriks.id', '=', 'spmi.mapping_penilaian_matriks.id_penilaian_matriks')
            ->where('spmi.penilaian_matriks.id_penilaian_panduan', $data['self']->id_penilaian_panduan)
            ->where('spmi.mapping_penilaian_matriks.id_audit_periode', $data['self']->id_audit_periode)
            ->where('spmi.mapping_penilaian_matriks.id_unit', $data['self']->id_unit)
            ->where('spmi.penilaian_matriks.waktu_dihapus', null)
            ->where('spmi.penilaian_matriks.kategori_penilaian', PenilaianMatriks::CATEGORY_INDICATOR)
            ->count();

        // Persentase Temuan Audit
        $totalAchievedFindings = $data['totals']['total_matrix'] - count($auditFindings);
        $percentageAchievedFinding = (($totalAchievedFindings / $data['totals']['total_matrix']) * 100);
        $percentageAchievedFinding = number_format($percentageAchievedFinding, 2, '.', ',');
        $data['totals']['achieved_finding'] = $totalAchievedFindings;
        $data['totals']['achieved_finding_percent'] = $percentageAchievedFinding . '%';
        $data['totals']['finding'] = count($auditFindings);
        $percentageFinding = (($data['totals']['finding'] / $data['totals']['total_matrix']) * 100);
        $percentageFinding = number_format($percentageFinding, 2, '.', ',');
        $data['totals']['finding_percent'] = $percentageFinding . '%';

        // Ambil Temuan Audit KTS Mayor
        $auditFindingFilled = AuditTemuan::where('id_penilaian_audit', $data['self']['id_penilaian_audit'])->get();
        $data['totals']['kts_major_finding'] = $auditFindingFilled
            ->where('jenis_temuan', AuditTemuan::TYPE_KTS_MAJOR)
            ->count();
        $ktsMajorPercent = ($data['totals']['kts_major_finding'] / $data['totals']['total_matrix']) * 100;
        $data['totals']['kts_major_percent'] = number_format($ktsMajorPercent, 2, '.', ',') . '%';

        // Ambil Temuan Audit KTS Minor
        $data['totals']['kts_minor_finding'] = $auditFindingFilled
            ->where('jenis_temuan', AuditTemuan::TYPE_KTS_MINOR)
            ->count();
        $ktsMinorPercent = ($data['totals']['kts_minor_finding'] / $data['totals']['total_matrix']) * 100;
        $data['totals']['kts_minor_percent'] = number_format($ktsMinorPercent, 2, '.', ',') . '%';

        // Ambil Temuan Observasi
        $data['totals']['kts_observation_finding'] = $auditFindingFilled
            ->where('jenis_temuan', AuditTemuan::TYPE_OBSERVATION)
            ->count();
        $ktsOBSPercent = ($data['totals']['kts_observation_finding'] / $data['totals']['total_matrix']) * 100;
        $data['totals']['kts_observation_percent'] = number_format($ktsOBSPercent, 2, '.', ',') . '%';

        $data['totals']['undifined_finding'] = count($auditFindings) - $data['totals']['kts_major_finding'] - $data['totals']['kts_minor_finding'] - $data['totals']['kts_observation_finding'];
        $data['totals']['undifined_finding_percent'] = number_format(($data['totals']['undifined_finding'] / $data['totals']['total_matrix']) * 100, 2, '.', ',') . '%';

        // Chart Temuan
        $data['chart_finding']['category'] = ['Positif', 'OBS', 'KTS Mayor', 'KTS Minor'];
        $data['chart_finding']['data'] = [
            $percentageAchievedFinding,
            number_format($ktsOBSPercent, 2, '.', ','),
            number_format($ktsMajorPercent, 2, '.', ','),
            number_format($ktsMinorPercent, 2, '.', ',')
        ];

        return $data;
    }

    protected function showIndexByRole($sql, $bindings = [])
    {
        $userRole = auth()->user()->kode_role;

        if ($userRole !== Role::ROLE_AUDITOR && $userRole !== Role::ROLE_KAPRODI && $userRole !== Role::ROLE_AUDITEE) {
            return [$sql, $bindings];
        }

        $filterPosisi = ($userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE) ? "AND stp.posisi IN (?,?)" : "AND stp.posisi NOT IN (?,?)";

        $checkValidAuditor =
            " AND (
                SELECT
                    stp.id
                FROM spmi.surat_tugas_auditor_pegawai stp
                JOIN core.biodata p ON p.id = stp.id_personil AND p.waktu_dihapus is null
                WHERE stp.id_unit = o.id
                    and p.ref_key_pegawai = ?
                    {$filterPosisi}
                    AND stp.id_surat_tugas_auditor = sa.id
                LIMIT 1
            ) IS NOT NULL";

        $bindings[] = session('token.idpegawai');
        $bindings[] = SuratTugasAuditorPegawai::POSITION_AUDITEE;
        $bindings[] = SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE;
        $sql .= $checkValidAuditor;

        return [$sql, $bindings];
    }

    protected function showIndexByUnitKerja($sql, $bindings = [])
    {
        $userRole = auth()->user()->kode_role;
        $userUnit = session()->get('user.unit_kerja');

        if (empty($userUnit)) {
            return [$sql, $bindings];
        }

        if ($userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITOR || $userRole === Role::ROLE_AUDITEE) {
            return [$sql, $bindings];
        }

        $unitKerjaServices = new UnitKerjaManagementService();
        $ids = array_keys($unitKerjaServices->getUnitAncestors($userUnit->id));
        if (!empty($ids)) {
            $sql .= " AND o.id IN (" . implode(',', $ids) . ")";
        } else {
            $sql .= " AND o.id = 0"; // pastikan tidak ada data yang ditampilkan
        }

        return [$sql, $bindings];
    }
}
