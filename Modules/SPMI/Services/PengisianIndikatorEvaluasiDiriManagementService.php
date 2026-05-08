<?php

namespace Modules\SPMI\Services;

use Carbon\Carbon;
use DOMDocument;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Helpers\SessionManager;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\Gate\Models\Modul;
use Modules\Gate\Models\Role;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\PengisianIndikator;
use Modules\SPMI\Models\DataPengisianLED;
use Modules\SPMI\Models\DokumenPendukungPengisianLed;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\MappingLED;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;

class PengisianIndikatorEvaluasiDiriManagementService
{
    /**
     * @var PengisianIndikator
     */
    protected $model = PengisianIndikator::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengisianIndikator;
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
        $year = array_filter($filter, function ($item, $key) use (&$filter) {
            if (isset($item['field']) && ($item['field'] === 'id_audit_periode')) {
                unset($filter[$key]);

                return true;
            }
        }, ARRAY_FILTER_USE_BOTH);
        $year = array_values($year)[0]['value'] ?? null;

        $sql = "SELECT
                    fg.id AS id_pengisian_panduan,
                    ap.tahun_audit AS periode_audit,
                    asch.nama_jadwal_audit,
                    COALESCE(fi.id, o.id) AS id,
                    asch.id AS id_jadwal_audit,
                    CONCAT(d.kode_jenjang, ' - ', o.nama_unit) AS nama_unit,
                    aa.nama_singkat_lembaga AS nama_lembaga_akreditasi,
                    fg.nama_pengisian_panduan AS nama_pengisian_panduan,
                    CASE WHEN fi.id IS NOT NULL THEN true ELSE false END AS is_created,
                    COALESCE(r.total_indikator_terisi, 0) AS total_indikator_terisi,
                    COALESCE(r2.total_indikator, 0) AS total_indikator,
                    r2.id_indikator_evaluasi_diri
                FROM
                    core.unit_kerja o
                JOIN spmi.audit_periode ap ON ap.id = ? AND ap.waktu_dihapus IS NULL
                JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
                JOIN spmi.jadwal_audit asch ON asch.id_audit_periode = ap.id AND asch.waktu_dihapus IS NULL
                JOIN spmi.jadwal_audit_unit aso ON aso.id_jadwal_audit = asch.id AND aso.id_unit = o.id
                JOIN spmi.pengisian_panduan fg2 ON fg2.id = aso.id_pengisian_panduan AND fg2.apakah_aktif = true AND fg2.tipe_edisi = ?
                JOIN spmi.pengisian_panduan fg ON fg.id = fg2.id_pengisian_panduan
                    AND fg.apakah_aktif = true
                    AND fg.tipe_edisi = ?
                JOIN core.lembaga_akreditasi aa ON aa.id = fg.id_lembaga_akreditasi
                JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = asch.id_audit_periode AND sa.waktu_dihapus IS NULL
                LEFT JOIN spmi.pengisian_indikator fi ON fi.id_unit = o.id
                    AND fi.id_pengisian_panduan = fg.id
                    AND fi.id_audit_periode = ap.id
                    AND fi.id_jadwal_audit = asch.id
                    AND fi.jenis_indikator = ?
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
                    WHERE fid.waktu_dihapus IS NULL
                    GROUP BY
                        fid.id_pengisian_indikator, ml.id_audit_periode, ml.id_unit
                ) r ON r.id_pengisian_indikator = fi.id AND r.id_unit = o.id AND r.id_audit_periode = ap.id";

        $fieldMap = [
            'nama_unit' => 'o.nama_unit',
            'nama_pengisian_panduan' => 'fg.nama_pengisian_panduan',
            'nama_lembaga_akreditasi' => 'aa.nama_singkat_lembaga',
            'id_jenjang_pendidikan' => 'd.id',
            'id_pengisian_panduan' => 'fg.id',
            'id_unit_kerja' => 'o.id',
            'status_pengisian_indikator' => 'total_indikator_terisi',
        ];

        // minimal 1 butir
        $defaultFilter = "o.waktu_dihapus is null AND o.jenis_unit IN ('" . UnitKerja::STUDY_PROGRAM . "', '" . UnitKerja::UNIT_NON_PRODI . "')
            AND EXISTS (
                SELECT 1
                FROM spmi.mapping_led ml
                JOIN spmi.indikator_evaluasi_diri ipr
                ON ipr.id = ml.id_indikator_evaluasi_diri
                AND ipr.id_pengisian_panduan = fg.id
                AND ipr.apakah_parent = false
                WHERE ml.id_unit = o.id
                AND ml.id_audit_periode = ap.id
            )";

        $bindings = [(int) $year, AkreditasiBuku::PERFORMANCE_REPORT, AkreditasiBuku::SELF_EVALUATION, AkreditasiBuku::SELF_EVALUATION];
        $userRole = auth()->user()->kode_role;

        $isValidatedRole = $userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE || $userRole === Role::ROLE_AUDITOR;
        $checkPosition = ($userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE)
            ? 'AND stp.posisi IN (?, ?)'
            : 'AND stp.posisi NOT IN (?, ?)';

        $checkValidAuditee =
            "AND (
                SELECT
                    stp.id
                FROM spmi.surat_tugas_auditor_pegawai stp
                JOIN core.biodata p ON p.ref_key_pegawai = ?
                    AND p.waktu_dihapus is null
                WHERE stp.id_unit = o.id
                    AND stp.id_personil = p.id
                    {$checkPosition}
                    AND stp.id_surat_tugas_auditor = sa.id
                LIMIT 1
            ) IS NOT NULL";

        if ($isValidatedRole) {
            $defaultFilter .= " $checkValidAuditee";

            $bindings[] = session('token.idpegawai');
            $bindings[] = SuratTugasAuditorPegawai::POSITION_AUDITEE;
            $bindings[] = SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE;
        }

        // Filter tambahan untuk cek hak akses unit
        $userUnit = session()->get('user.unit_kerja');
        $isInternalRole = SessionManager::isInternalRole();
        if (!empty($userUnit) && ($userRole !== Role::ROLE_KAPRODI && $userRole !== Role::ROLE_AUDITOR && $userRole !== Role::ROLE_AUDITEE) && !$isInternalRole) {
            $unitKerjaServices = new UnitKerjaManagementService();
            $ids = array_keys($unitKerjaServices->getUnitAncestors($userUnit->id));
            if (!empty($ids)) {
                $sql .= " AND o.id IN (" . implode(',', $ids) . ")";
            } else {
                $sql .= " AND o.id = 0"; // pastikan tidak ada data yang ditampilkan
            }
        }

        [$sql, $bindings] = Pagination::buildQuery(
            bindings: $bindings,
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
            groupBy: 'o.id, ap.id, aa.id, fg.id, asch.id, fi.id, r.total_indikator_terisi, r2.total_indikator, d.kode_jenjang, r2.id_indikator_evaluasi_diri',
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return PengisianIndikator
     */
    public function show(int $id): PengisianIndikator
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return PengisianIndikator|Error
     */
    public function store(array $data): PengisianIndikator|Error
    {
        $act = null;
        if (!empty($data['act'])) {
            $act = $data['act'];
        }

        DB::beginTransaction();
        $fillingIndicator = PengisianIndikator::where('id_unit', $data['self']['id_unit'])
            ->where('id_pengisian_panduan', $data['self']['id_pengisian_panduan'])
            ->where('jenis_indikator', AkreditasiBuku::SELF_EVALUATION)
            ->when(!empty($data['self']['id_jadwal_audit']), fn ($q) => $q->where('id_jadwal_audit', $data['self']['id_jadwal_audit']))
            ->first();

        if ($fillingIndicator && ($fillingIndicator->id_audit_periode == $data['self']['id_audit_periode'])) {
            DB::rollBack();
            return new Error('Data sudah dibuat sebelumnya', $fillingIndicator->id, 409);
        }

        $data['self']['jenis_indikator'] = AkreditasiBuku::SELF_EVALUATION; // self evaluation

        if (isset($data['uraian'])) {
            $data['uraian'] = $this->cleanUraian($data['uraian']);
        }

        $model = $this->model->create($data['self']);

        if ($act === 'upload-dokumen-pendukung') {
            $saveDokumen = $this->saveDokumenPendukung($model->id, $data);

            if (Error::isError($saveDokumen)) {
                DB::rollBack();
                return $saveDokumen;
            }
        } else {
            $keyPoints = [];
            if (!empty($data['key_points'])) {
                // unset frist $data
                unset($data['key_points'][0]);
                foreach ($data['key_points'] as $key => $value) {
                    $keyPoints[] = $value;
                }
            }

            try {
                // Jika berhasil dibuat, maka buat indicator data
                if (!empty($data['uraian'])) {
                    DataPengisianLED::create([
                        'id_pengisian_indikator' => $model->id,
                        'id_indikator_evaluasi_diri' => $data['self']['id_indikator_evaluasi_diri'],
                        'data_pengisian_led' => $data['uraian'] ?? null,
                        'komentar' => $data['komentar'] ?? null,
                        'key_points' => json_encode($keyPoints) ?? null
                    ]);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return new Error(exception: $e);
            };
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
     * @return PengisianIndikator|Error
     */
    public function update(array $data, int $id): PengisianIndikator|Error
    {
        $model = $this->model->findOrFail($id);

        if (isset($data['uraian'])) {
            $data['uraian'] = $this->cleanUraian($data['uraian']);
        }

        $saveIndikator = $this->saveIndicatorData($id, $data);

        if (Error::isError($saveIndikator)) {
            return $saveIndikator;
        }

        return $model;
    }

    public function cleanUraian(?string $html): ?string
    {
        if (empty($html)) {
            return null;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        foreach (['table', 'tr', 'td', 'th'] as $tag) {
            foreach ($dom->getElementsByTagName($tag) as $el) {
                if ($el->hasAttribute('style')) {
                    $el->removeAttribute('style');
                }
            }
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        $cleanHtml = '';
        foreach ($body->childNodes as $child) {
            $cleanHtml .= $dom->saveHTML($child);
        }

        $textOnly = strip_tags($cleanHtml);
        $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $textOnly = preg_replace([
            '/\xC2\xA0/u',
            '/\x{FEFF}/u',
            '/[\x{200B}-\x{200D}\x{2060}]/u',
            '/\s+/u',
        ], ['', '', '', ''], $textOnly);

        $textOnly = trim($textOnly);

        if ($textOnly === '') {
            return null;
        }

        return $cleanHtml;
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

    public function getDokumenPendukung($pengisianIndikatorId, $indikatorLaporanKinerjaId)
    {
        $sql =
            "SELECT
                dp.id,
                dp.pengisian_indikator_id,
                dp.id_indikator_evaluasi_diri,
                d.nama_dokumen,
                d.extension_versi_terbaru,
                d.slug,
                dp.waktu_diubah,
                d.ukuran
                FROM spmi.dokumen_pendukung_pengisian_led dp
                JOIN dms.dokumen d ON d.id = dp.id_dokumen
                    AND d.waktu_dihapus IS NULL
                WHERE dp.pengisian_indikator_id = ?
                    AND dp.id_indikator_evaluasi_diri = ?
                    AND dp.waktu_dihapus IS NULL";

        $dokumenPendukungPengisian = DB::select($sql, [$pengisianIndikatorId, $indikatorLaporanKinerjaId]);

        return $dokumenPendukungPengisian;
    }

    /**
     * Save Filling Indicator Data
     *
     * @param array $data
     *
     * @return DataPengisianLED|Error
     */
    public function saveIndicatorData($idPengisianIndikator, $data)
    {
        $act = null;
        if (!empty($data['act'])) {
            $act = $data['act'];
        }

        if ($act === 'upload-dokumen-pendukung') {
            return $this->saveDokumenPendukung($idPengisianIndikator, $data);
        } else {
            if (isset($data['uraian']) && !empty($data['uraian'])) {
                $data['uraian'] = $this->cleanUraian($data['uraian']);

                // checking if filling indicator data is not exist
                if (empty($data['self']['filling_indicator_data_id'])) {
                    // create new data
                    $fillingIndicatorData = new DataPengisianLED;
                    $isNew = true;
                    $datas = [];
                } else {
                    // get data filling indicator data
                    $fillingIndicatorData = DataPengisianLED::findOrFail($data['self']['filling_indicator_data_id']);
                    $datas = json_decode($fillingIndicatorData->data, true);
                    $isNew = false;
                }

                $keyPoints = [];
                if (!empty($data['key_points'])) {
                    // unset frist $data
                    unset($data['key_points'][0]);
                    foreach ($data['key_points'] as $key => $value) {
                        $keyPoints[] = $value;
                    }
                }

                try {
                    if ($isNew) {
                        // create new data
                        $fillingIndicatorData->create([
                            'id_pengisian_indikator' => $idPengisianIndikator,
                            'id_indikator_evaluasi_diri' => $data['self']['id_indikator_evaluasi_diri'],
                            'data_pengisian_led' => $data['uraian'],
                            'komentar' => $data['komentar'] ?? null,
                            'key_points' => json_encode($keyPoints) ?? null
                        ]);
                    } else {
                        // update data
                        $fillingIndicatorData->update([
                            'data_pengisian_led' => $data['uraian'] ?? null,
                            'komentar' => $data['komentar'] ?? null,
                            'key_points' => json_encode($keyPoints)
                        ]);
                    }
                } catch (\Exception $e) {
                    return new Error(exception: $e);
                }
            } else {
                DataPengisianLED::where('id_pengisian_indikator', $idPengisianIndikator)
                    ->where('id_indikator_evaluasi_diri', $data['self']['id_indikator_evaluasi_diri'])
                    ->delete();

                $fillingIndicatorData = null;
            }
        }

        return $fillingIndicatorData;
    }

    public function  deleteDokumenPendukung($idPengisianIndikator, $idDokumenPendukung)
    {
        $dokumenPendukung = DokumenPendukungPengisianLed::where('pengisian_indikator_id', $idPengisianIndikator)
            ->where('id', $idDokumenPendukung)
            ->first();

        $return = $dokumenPendukung;

        if (empty($dokumenPendukung)) {
            return new Error('Dokumen pendukung tidak ditemukan', 404);
        }

        try {
            $dokumenPendukung->delete();
        } catch (\Exception $e) {
            return new Error('Gagal menghapus dokumen pendukung', 500);
        }

        return $return;
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
     * Show Information
     *
     * @return mixed
     */
    public function showInformation(int $id)
    {
        $sql =
            "SELECT
                fi.id,
                ap.tahun_audit periode_audit,
                concat(d.kode_jenjang, ' - ', o.nama_unit) as nama_unit,
                aa.nama_singkat_lembaga nama_lembaga_akreditasi,
                fg.nama_pengisian_panduan,
                ap.id id_audit_periode,
                o.id id_unit,
                aa.id id_lembaga_akreditasi,
                fg.id id_pengisian_panduan,
                fi.id_jadwal_audit,
                ja.nama_jadwal_audit,
                ja.tanggal_awal_pengisian,
                ja.tanggal_akhir_pengisian
            FROM spmi.pengisian_indikator fi
            JOIN core.unit_kerja o ON o.id = fi.id_unit
                AND o.waktu_dihapus is null
            JOIN spmi.audit_periode ap ON ap.id = fi.id_audit_periode
                AND ap.waktu_dihapus is null
            JOIN spmi.jadwal_audit_unit aso ON aso.id_unit = o.id
            JOIN spmi.jadwal_audit ja ON ja.id = aso.id_jadwal_audit
                AND ja.id_audit_periode = ap.id
                AND (fi.id_jadwal_audit IS NULL OR ja.id = fi.id_jadwal_audit)
                AND ja.waktu_dihapus is null
            JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = ja.id_audit_periode
                AND sa.waktu_dihapus is null
            JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
                AND d.waktu_dihapus is null
            JOIN spmi.pengisian_panduan fg2 ON fg2.id = aso.id_pengisian_panduan AND fg2.apakah_aktif = true
            JOIN spmi.pengisian_panduan fg ON fg.id = fg2.id_pengisian_panduan
                AND fg.apakah_aktif = true
                AND fg.id = fi.id_pengisian_panduan
            JOIN core.lembaga_akreditasi aa ON aa.id = fg.id_lembaga_akreditasi
            WHERE fi.id = :id
                AND fi.waktu_dihapus is null";

        $userRole = auth()->user()->kode_role;
        $isValidatedRole = $userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE || $userRole === Role::ROLE_AUDITOR;
        $checkPosition = ($userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE)
            ? 'AND stp.posisi IN (:posisi, :posisi2)'
            : 'AND stp.posisi NOT IN (:posisi, :posisi2)';

        $checkValidAuditee =
            "AND (
                SELECT
                    stp.id
                FROM spmi.surat_tugas_auditor_pegawai stp
                JOIN core.biodata p ON p.ref_key_pegawai = :id_biodata
                    AND p.waktu_dihapus is null
                WHERE stp.id_unit = o.id
                    AND stp.id_personil = p.id
                    {$checkPosition}
                    AND stp.id_surat_tugas_auditor = sa.id
                LIMIT 1
            ) IS NOT NULL";

        $bindings = [
            'id' => $id
        ];

        if ($isValidatedRole) {
            $sql .= " $checkValidAuditee";

            $bindings['id_biodata'] = session('token.idpegawai');
            $bindings['posisi'] = SuratTugasAuditorPegawai::POSITION_AUDITEE;
            $bindings['posisi2'] = SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE;
        }

        // Filter tambahan untuk cek hak akses unit
        $userUnit = session()->get('user.unit_kerja');
        $isInternalRole = SessionManager::isInternalRole();
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

        $tahunPeriode = $data['periode_audit'];
        $information = [
            'nama_jadwal_audit' => $data['nama_jadwal_audit'],
            'periode_audit' => $tahunPeriode,
            'periode_akademik' => ($tahunPeriode - 1) . '/' . $tahunPeriode,
            'nama_unit' => $data['nama_unit'],
            'nama_lembaga_akreditasi' => $data['nama_lembaga_akreditasi'],
            'panduan_pengisian' => $data['nama_pengisian_panduan'],
            'filling_date' => Carbon::parse($data['tanggal_awal_pengisian'])->translatedFormat('d F Y')
                . ' s.d. ' . Carbon::parse($data['tanggal_akhir_pengisian'])->translatedFormat('d F Y'),
        ];

        $raw = [
            'id_audit_periode' => $data['id_audit_periode'],
            'id_unit' => $data['id_unit'],
            'id_lembaga_akreditasi' => $data['id_lembaga_akreditasi'],
            'id_pengisian_panduan' => $data['id_pengisian_panduan'],
            'id_jadwal_audit' => $data['id_jadwal_audit'],
        ];

        $apakahTanggalPengisianValid = Carbon::now()->between(
            $data['tanggal_awal_pengisian'],
            Carbon::parse($data['tanggal_akhir_pengisian'])->addDay(),
            true
        );

        $apakahPengisianBelumDimulai = Carbon::now()->lt(
            Carbon::parse($data['tanggal_awal_pengisian']),
        );

        return [
            $information,
            $raw,
            $apakahTanggalPengisianValid,
            $apakahPengisianBelumDimulai
        ];
    }

    /**
     * Get Information
     *
     * @return mixed
     */
    public function showInformationByStudyProgram(int $idUnit, $yearData, $idPengisianPanduan, $idJadwalAudit = null)
    {
        if (!isset($yearData)) {
            return new Error('Tahun audit tidak ditemukan', 404);
        }

        $sql =
            "SELECT
                concat(d.kode_jenjang, ' - ', o.nama_unit) as nama_unit,
                aa.nama_singkat_lembaga nama_lembaga_akreditasi,
                fg.nama_pengisian_panduan as panduan_pengisian,
                o.id id_unit,
                aa.id id_lembaga_akreditasi,
                fg.id id_pengisian_panduan,
                ja.id id_jadwal_audit,
                ja.nama_jadwal_audit,
                ja.tanggal_awal_pengisian,
                ja.tanggal_akhir_pengisian,
                d.kode_jenjang,
                d.id id_jenjang_pendidikan
            FROM core.unit_kerja o
            JOIN spmi.audit_periode ap ON ap.id = :periode_audit
                AND ap.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit_unit aso ON aso.id_unit = o.id
            JOIN spmi.jadwal_audit ja ON ja.id = aso.id_jadwal_audit
                AND ja.id_audit_periode = ap.id
                AND ja.waktu_dihapus is null
            JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = ja.id_audit_periode
                AND sa.waktu_dihapus is null
            JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
                AND d.waktu_dihapus IS NULL
            JOIN spmi.pengisian_panduan fg2 ON fg2.id = aso.id_pengisian_panduan AND fg2.apakah_aktif = true
            JOIN spmi.pengisian_panduan fg ON fg.id = fg2.id_pengisian_panduan
                AND fg.apakah_aktif = true
                AND fg.id = :id_pengisian_panduan
            JOIN core.lembaga_akreditasi aa ON aa.id = fg.id_lembaga_akreditasi
                AND aa.waktu_dihapus IS NULL
            WHERE o.id = :id_unit
                AND o.waktu_dihapus IS NULL";

        $userRole = auth()->user()->kode_role;
        $isValidatedRole = $userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE || $userRole === Role::ROLE_AUDITOR;
        $checkPosition = ($userRole === Role::ROLE_KAPRODI || $userRole === Role::ROLE_AUDITEE)
            ? 'AND stp.posisi IN (:posisi, :posisi2)'
            : 'AND stp.posisi NOT IN (:posisi, :posisi2)';

        $checkValidAuditee =
            "AND (
                SELECT
                    stp.id
                FROM spmi.surat_tugas_auditor_pegawai stp
                JOIN core.biodata p ON p.ref_key_pegawai = :id_biodata
                    AND p.waktu_dihapus is null
                WHERE stp.id_unit = o.id
                    AND stp.id_personil = p.id
                    {$checkPosition}
                    AND stp.id_surat_tugas_auditor = sa.id
                LIMIT 1
            ) IS NOT NULL";

        if (!empty($idJadwalAudit)) {
            $sql .= " AND ja.id = :id_jadwal_audit";
        }

        $bindings = [
            'periode_audit' => $yearData['id'],
            'id_unit' => $idUnit,
            'id_pengisian_panduan' => $idPengisianPanduan
        ];

        if (!empty($idJadwalAudit)) {
            $bindings['id_jadwal_audit'] = (int) $idJadwalAudit;
        }

        if ($isValidatedRole) {
            $sql .= " $checkValidAuditee";

            $bindings['id_biodata'] = session('token.idpegawai');
            $bindings['posisi'] = SuratTugasAuditorPegawai::POSITION_AUDITEE;
            $bindings['posisi2'] = SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE;
        }

        // Filter tambahan untuk cek hak akses unit
        $userUnit = session()->get('user.unit_kerja');
        $isInternalRole = SessionManager::isInternalRole();
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

        // Jika data dan tahun tidak ditemukan, maka kembalikan error
        if (empty($data) || empty($yearData)) {
            return new Error('Data tidak ditemukan', 404);
        }

        $data = (array) $data[0];

        // Panduan Penilaian harus ada, jika tidak ada maka kembalikan error
        if (empty($data['panduan_pengisian'])) {
            return new Error('Panduan Pengisian belum tersedia', 404);
        }

        $tahunPeriode = $yearData['tahun_audit'];
        // // get information
        $information = [
            'nama_jadwal_audit' => $data['nama_jadwal_audit'],
            'periode_audit' => $tahunPeriode,
            'periode_akademik' => ($tahunPeriode - 1) . '/' . $tahunPeriode,
            'nama_unit' => $data['nama_unit'],
            'nama_lembaga_akreditasi' => $data['nama_lembaga_akreditasi'],
            'panduan_pengisian' => $data['panduan_pengisian'],
            'filling_date' => Carbon::parse($data['tanggal_awal_pengisian'])->translatedFormat('d F Y')
                . ' s.d. ' . Carbon::parse($data['tanggal_akhir_pengisian'])->translatedFormat('d F Y'),
        ];

        $raw = [
            'id_audit_periode' => $yearData['id'],
            'id_unit' => $data['id_unit'],
            'id_lembaga_akreditasi' => $data['id_lembaga_akreditasi'],
            'id_pengisian_panduan' => $data['id_pengisian_panduan'],
            'id_jadwal_audit' => $data['id_jadwal_audit'],
        ];

        $apakahTanggalPengisianValid = Carbon::now()->between(
            $data['tanggal_awal_pengisian'],
            Carbon::parse($data['tanggal_akhir_pengisian'])->addDay(),
            true
        );

        $apakahPengisianBelumDimulai = Carbon::now()->lt(
            Carbon::parse($data['tanggal_awal_pengisian']),
        );

        return [
            $information,
            $raw,
            $apakahTanggalPengisianValid,
            $apakahPengisianBelumDimulai
        ];
    }

    private function saveDokumenPendukung($idPengisianIndikator, $data)
    {
        $file = $data['dokumen_pendukung'] ?? null;

        if (!empty($file)) {
            $fileName = $file->getClientOriginalName();
            $fileName = pathinfo($fileName, PATHINFO_FILENAME);

            if (($file->getSize() / 1000) > 10048) {
                return new Error('Ukuran file terlalu besar, maksimal 10MB.', 400);
            }

            // Validasi file harus pdf, doc, docx, jpg, png, xls, xlsx, jpeg
            $allowedExtension = ['pdf', 'doc', 'docx', 'jpg', 'png', 'xls', 'xlsx', 'jpeg'];
            $extension = $file->getClientOriginalExtension();

            if (!in_array($extension, $allowedExtension)) {
                return new Error('Ekstensi file tidak diizinkan', 400);
            }

            DB::beginTransaction();

            $upload = new UploadDokumen();
            $upload = $upload->upload(
                file: $file,
                name: $fileName,
                folderCode: UploadDokumen::SPMI_DOKUMEN_PENGISIAN,
                moduleCode: Modul::CODE_SPMI,
                note: null,
                withTransaction: false
            );

            if (Error::isError($upload->getError())) {
                DB::rollBack();
                return $upload->getError();
            }

            $dokumenId = $upload->get()?->id;

            try {
                DokumenPendukungPengisianLed::create([
                    'pengisian_indikator_id' => $idPengisianIndikator,
                    'id_indikator_evaluasi_diri' => $data['self']['id_indikator_evaluasi_diri'],
                    'id_dokumen' => $dokumenId
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return new Error('Gagal menyimpan dokumen pendukung', 500);
            }

            // Execute upload
            $upload->executeUpload();

            if (Error::isError($upload->getError())) {
                DB::rollBack();
                return $upload->getError();
            }

            DB::commit();
        }
    }

    // FIXME: Belum fix core report
    /**
     * Show Report
     */
    public function showReport($idPengisianPanduan, $idPeriode, $idUnit)
    {
        $indicator = IndikatorEvaluasiDiri::select('spmi.indikator_evaluasi_diri.*')
            ->join('spmi.mapping_led', function ($join) use ($idPeriode, $idUnit) {
                $join->on('spmi.mapping_led.id_indikator_evaluasi_diri', '=', 'spmi.indikator_evaluasi_diri.id')
                    ->where('spmi.mapping_led.id_audit_periode', $idPeriode)
                    ->where('spmi.mapping_led.id_unit', $idUnit);
            })
            ->where('spmi.indikator_evaluasi_diri.id_pengisian_panduan', $idPengisianPanduan)
            ->orderBy('spmi.indikator_evaluasi_diri.info_left', 'asc')
            ->distinct()
            ->get();

        if (empty($indicator)) {
            return new Error('Data tidak ditemukan', 404);
        }

        return $indicator;
    }

    public function getPreviousSelectOptions($idAuditPeriode, $idUnit, $idPengisianPanduan)
    {
        $data = DB::select(
            "WITH IndikatorAcuan AS (
                SELECT
                    ml.id_indikator_evaluasi_diri
                FROM
                    spmi.mapping_led ml
                WHERE
                    ml.id_audit_periode = :id_audit_periode
                    AND ml.id_unit = :id_unit
                    AND ml.waktu_dihapus IS NULL
            )
            select DISTINCT
                ap.id,
                ap.tahun_audit,
                ja.id as id_jadwal_audit,
                ja.nama_jadwal_audit
            FROM
                spmi.data_pengisian_led dpl
            JOIN IndikatorAcuan ia ON dpl.id_indikator_evaluasi_diri = ia.id_indikator_evaluasi_diri
            JOIN spmi.pengisian_indikator pi ON dpl.id_pengisian_indikator = pi.id
                AND pi.waktu_dihapus IS NULL
                AND pi.jenis_indikator = '".AkreditasiBuku::SELF_EVALUATION."'
                AND pi.id_unit = :id_unit
                AND pi.id_pengisian_panduan = :id_pengisian_panduan
            JOIN spmi.jadwal_audit ja ON pi.id_audit_periode = ja.id_audit_periode
                AND (pi.id_jadwal_audit IS NULL OR ja.id = pi.id_jadwal_audit)
                AND ja.waktu_dihapus IS null
            JOIN spmi.jadwal_audit_unit jau ON ja.id = jau.id_jadwal_audit AND jau.id_unit = :id_unit
            JOIN spmi.pengisian_panduan pp ON pp.id = :id_pengisian_panduan AND pp.waktu_dihapus IS NULL
            JOIN spmi.audit_periode ap ON pi.id_audit_periode = ap.id
            WHERE
                ap.id != :id_audit_periode
                AND dpl.waktu_dihapus IS NULL
            ORDER BY
                ap.tahun_audit DESC;",
            [
                'id_unit' => $idUnit,
                'id_pengisian_panduan' => $idPengisianPanduan,
                'id_audit_periode' => $idAuditPeriode
            ]
        );

        if (empty($data)) {
            return [false, null];
        }

        return [true, $data];
    }

    public function copyBulkAnswerPeriode($data)
    {
        $panduan_pengisian = PengisianPanduan::find($data['id_pengisian_panduan']);
        $unit = UnitKerja::find($data['id_unit']);
        $audit_periode = AuditPeriode::find($data['id_audit_periode']);
        $prev_audit_periode = AuditPeriode::find($data['previous_year_audit']);

        if (empty($panduan_pengisian) || empty($unit) || empty($audit_periode) || empty($prev_audit_periode)) {
            return [false, "Data panduan, unit kerja, atau periode audit tidak ditemukan."];
        }

        $indicator_ids_to_copy = [];

        if ($data['scope'] == '1') {
            if (empty($data['id_indikator_evaluasi_diri'])) {
                return [false, "Untuk scope yang dipilih, ID indikator wajib diisi."];
            }

            $indicatorExists = DB::table('spmi.mapping_led')
                ->where('id_audit_periode', $data['previous_year_audit'])
                ->where('id_unit', $data['id_unit'])
                ->where('id_indikator_evaluasi_diri', $data['id_indikator_evaluasi_diri'])
                ->whereNull('waktu_dihapus')
                ->exists();

            if (!$indicatorExists) {
                return [false, "Indikator yang dipilih tidak terpetakan pada periode audit dan unit kerja sebelumnya."];
            }

            $indicator_ids_to_copy = is_array($data['id_indikator_evaluasi_diri'])
                ? $data['id_indikator_evaluasi_diri']
                : [$data['id_indikator_evaluasi_diri']];

        } else {
            $mapped_indicators = DB::table('spmi.mapping_led as ml')
                ->join('spmi.indikator_evaluasi_diri as ied', 'ml.id_indikator_evaluasi_diri', '=', 'ied.id')
                ->where('ml.id_audit_periode', $data['id_audit_periode'])
                ->where('ml.id_unit', $data['id_unit'])
                ->where('ied.id_pengisian_panduan', $data['id_pengisian_panduan'])
                ->pluck('ml.id_indikator_evaluasi_diri');

            if ($mapped_indicators->isEmpty()) {
                return [false, "Tidak ada indikator yang terpetakan pada periode audit dan unit kerja yang dipilih."];
            }
            $indicator_ids_to_copy = $mapped_indicators->toArray();
        }

        $prev_answers = DB::table('spmi.data_pengisian_led as dpl')
            ->join('spmi.pengisian_indikator as pi', 'dpl.id_pengisian_indikator', '=', 'pi.id')
            ->where('pi.id_unit', $data['id_unit'])
            ->where('pi.id_audit_periode', $data['previous_year_audit'])
            ->when(!empty($data['previous_jadwal_audit']), function ($query) use ($data) {
                $query->where('pi.id_jadwal_audit', $data['previous_jadwal_audit']);
            })
            ->whereIn('dpl.id_indikator_evaluasi_diri', $indicator_ids_to_copy)
            ->whereNull('dpl.waktu_dihapus')
            ->select('dpl.*')
            ->get();

        if ($prev_answers->isEmpty()) {
            return [false, "Data pengisian pada periode audit sebelumnya tidak ditemukan untuk indikator yang dipilih."];
        }

        $pengisian_indikator = PengisianIndikator::firstOrCreate(
            [
                'id_unit' => $data['id_unit'],
                'id_audit_periode' => $data['id_audit_periode'],
                'id_pengisian_panduan' => $data['id_pengisian_panduan'],
                'id_jadwal_audit' => $data['id_jadwal_audit'] ?? null,
                'jenis_indikator' => 'se',
            ],
            ['id_lembaga_akreditasi' => $panduan_pengisian->id_lembaga_akreditasi]
        );

        try {
            DB::transaction(function () use ($pengisian_indikator, $prev_answers, $data) {
                if ($data['scope'] == '1') {
                    DataPengisianLED::where('id_pengisian_indikator', $pengisian_indikator->id)
                        ->where('id_indikator_evaluasi_diri', $data['id_indikator_evaluasi_diri'])
                        ->delete();
                } else {
                    DataPengisianLED::where('id_pengisian_indikator', $pengisian_indikator->id)->delete();
                }

                $data_to_insert = [];
                foreach ($prev_answers as $prev) {
                    $data_to_insert[] = [
                        'id_pengisian_indikator' => $pengisian_indikator->id,
                        'id_indikator_evaluasi_diri' => $prev->id_indikator_evaluasi_diri,
                        'data_pengisian_led' => $prev->data_pengisian_led,
                        'komentar' => $prev->komentar,
                        'key_points' => $prev->key_points,
                    ];
                }
                DataPengisianLED::insert($data_to_insert);
            });
            return [true, (object)["data" => $pengisian_indikator->id, "message" => "Data LED berhasil disalin"]];
        } catch (\Exception $e) {
            Log::error('Gagal menyalin jawaban: ' . $e->getMessage());
            return [false, "Gagal menyalin data karena terjadi kesalahan pada server."];
        }
    }
}
