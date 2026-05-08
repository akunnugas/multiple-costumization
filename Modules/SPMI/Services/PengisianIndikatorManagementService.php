<?php

namespace Modules\SPMI\Services;

use Carbon\Carbon;
use DOMDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
use Modules\SPMI\Models\DataPengisianLK;
use Modules\SPMI\Models\DokumenPendukungPengisianLk;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Models\JadwalAuditUnit;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;

class PengisianIndikatorManagementService
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
                    r2.id_indikator_laporan_kinerja
                FROM
                    core.unit_kerja o
                JOIN spmi.audit_periode ap ON ap.id = ? AND ap.waktu_dihapus IS NULL
                JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
                JOIN spmi.jadwal_audit asch ON asch.id_audit_periode = ap.id AND asch.waktu_dihapus IS NULL
                JOIN spmi.jadwal_audit_unit aso ON aso.id_jadwal_audit = asch.id AND aso.id_unit = o.id
                JOIN spmi.pengisian_panduan fg ON fg.id = aso.id_pengisian_panduan AND fg.apakah_aktif = true AND fg.tipe_edisi = ?
                JOIN core.lembaga_akreditasi aa ON aa.id = fg.id_lembaga_akreditasi
                JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = asch.id_audit_periode AND sa.waktu_dihapus IS NULL
                LEFT JOIN spmi.pengisian_indikator fi ON fi.id_unit = o.id
                    AND fi.id_pengisian_panduan = aso.id_pengisian_panduan
                    AND fi.id_audit_periode = ap.id
                    AND fi.id_jadwal_audit = asch.id
                    AND fi.jenis_indikator = ?
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
                FROM spmi.mapping_lk ml
                JOIN spmi.indikator_laporan_kinerja ipr
                ON ipr.id = ml.id_indikator_laporan_kinerja
                AND ipr.id_pengisian_panduan = fg.id
                AND ipr.apakah_parent = false
                WHERE ml.id_unit = o.id
                AND ml.id_audit_periode = ap.id
            )";

        $bindings = [(int) $year, AkreditasiBuku::PERFORMANCE_REPORT, AkreditasiBuku::PERFORMANCE_REPORT];
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
            groupBy: 'o.id, ap.id, aa.id, fg.id, asch.id, fi.id, r.total_indikator_terisi, r2.total_indikator, d.kode_jenjang, r2.id_indikator_laporan_kinerja',
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
        DB::beginTransaction();
        $fillingIndicator = PengisianIndikator::where('id_unit', $data['self']['id_unit'])
            ->where('id_pengisian_panduan', $data['self']['id_pengisian_panduan'])
            ->where('jenis_indikator', AkreditasiBuku::PERFORMANCE_REPORT)
            ->when(!empty($data['self']['id_jadwal_audit']), fn ($q) => $q->where('id_jadwal_audit', $data['self']['id_jadwal_audit']))
            ->first();

        if ($fillingIndicator && ($fillingIndicator->id_audit_periode == $data['self']['id_audit_periode'])) {
            DB::rollBack();
            return new Error('Data sudah dibuat sebelumnya', $fillingIndicator->id, 409);
        }
        $data['self']['jenis_indikator'] = AkreditasiBuku::PERFORMANCE_REPORT;

        $model = $this->model->create($data['self']);

        $saveIndikator = $this->saveIndicatorData($model, $data);

        if (Error::isError($saveIndikator)) {
            DB::rollBack();
            return $saveIndikator;
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

        DB::beginTransaction();

        $saveIndikator = $this->saveIndicatorData($model, $data);

        if (Error::isError($saveIndikator)) {
            DB::rollBack();
            return $saveIndikator;
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
    public function destroy(int $id): void
    {
        $model = $this->model->findOrFail($id);

        $model->destroy($model->id);
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
     * Save Filling Indicator Data New
     *
     * @param array $data
     *
     * @return DataPengisianLK|Error
     */
    public function saveIndicatorData($model, $data)
    {
        $act = null;
        if (!empty($data['act'])) {
            $act = $data['act'];
        }

        $idPengisianIndikator = $model->id;

        if ($act === 'upload-dokumen-pendukung') {
            return $this->saveDokumenPendukung($idPengisianIndikator, $data);
        }

        $fillingIndicatorData = DataPengisianLK::where('id_pengisian_indikator', $idPengisianIndikator)->where('id_indikator_laporan_kinerja', $data['self']['id_indikator_laporan_kinerja'])->first();

        // checking if filling indicator data is not exist
        if (empty($fillingIndicatorData)) {
            // create new data
            $fillingIndicatorData = new DataPengisianLK;
            $isNew = true;
            $payload = [];
            $jenis_payload = [];
        } else {
            // get data filling indicator data
            $isNew = false;
            $payload = json_decode($fillingIndicatorData->data_pengisian_lk, true);
            $jenis_payload = json_decode($fillingIndicatorData->jenis_data_pengisian_lk, true);
        }

        // check apakah indikator laporan kinerja adalah data default
        $apakahDataDefault = IndikatorLaporanKinerja::where('id', $data['self']['id_indikator_laporan_kinerja'])->value('apakah_data_default');
        $apakahIkuKualitatif = PengisianPanduan::where('id', $data['self']['id_pengisian_panduan'])->value('apakah_iku_kualitatif');

        // save if indikator laporan kinerja bukan data default
        if (!$apakahDataDefault || $apakahIkuKualitatif) {
            if ($isNew) {
                $fillingIndicatorData->create([
                    'id_pengisian_indikator' => $idPengisianIndikator,
                    'id_indikator_laporan_kinerja' => $data['self']['id_indikator_laporan_kinerja'],
                    'data_pengisian_lk' => json_encode($payload),
                    'jenis_data_pengisian_lk' => json_encode($jenis_payload),
                    'teks_pengisian' => !empty($data['teks_pengisian']) ? $this->cleanUraian($data['teks_pengisian']) : null,
                ]);
            } else {
                // update data
                $fillingIndicatorData->update([
                    'teks_pengisian' => !empty($data['teks_pengisian']) ? $this->cleanUraian($data['teks_pengisian']) : null,
                ]);
            }
            return $fillingIndicatorData;
        }

        if (isset($data['iddelete'])) {
            $act = 'd';
        } else {
            $act = $data['act'];
        }

        if ($act == 'fillinggetdata') {
            if ($data['form_type'] == IndikatorLaporanKinerja::FORM_ROW) {
                if ($data['row_type'] == '0') {
                    foreach ($jenis_payload as $key => $jenis) {
                        if ($jenis == 'get') {
                            unset($payload[$key]);
                            unset($jenis_payload[$key]);
                        }
                    }

                    $payload = array_values($payload);
                    $jenis_payload = array_values($jenis_payload);

                    foreach ($data['data'] as $index => $obj) {
                        $payload[] = $obj;
                        $jenis_payload[] = "get";
                    }
                } else {
                    foreach ($jenis_payload as $key => $values) {
                        foreach ($values as $index => $jenis) {
                            if ($jenis == 'get') {
                                unset($payload[$key][$index]);
                                unset($jenis_payload[$key][$index]);
                            }
                        }
                    }

                    $payload = array_map(function ($item) {
                        return array_values($item);
                    }, $payload);
                    $jenis_payload = array_map(function ($item) {
                        return array_values($item);
                    }, $jenis_payload);

                    foreach ($data['data'] as $index => $values) {
                        foreach ($values as $obj) {
                            $payload[$index][] = $obj;
                            $jenis_payload[$index][] = "get";
                        }
                    }
                }
            } else {
                $payload = $data['data'];
                $jenis_payload = null;
            }

            // save to database
            if ($isNew) {
                $fillingIndicatorData->create([
                    'id_pengisian_indikator' => $idPengisianIndikator,
                    'id_indikator_laporan_kinerja' => $data['self']['id_indikator_laporan_kinerja'],
                    'data_pengisian_lk' => json_encode($payload),
                    'jenis_data_pengisian_lk' => json_encode($jenis_payload)
                ]);
            } else {
                // update data
                $fillingIndicatorData->update([
                    'data_pengisian_lk' => json_encode($payload),
                    'jenis_data_pengisian_lk' => json_encode($jenis_payload)
                ]);
            }
        } else {
            if ($act != 'd') {
                $data['column_length'] = (int) $data['column_length'] + 1;

                if ($data['form_type'] == IndikatorLaporanKinerja::FORM_ROW) {
                    // loop data get input
                    $indicatorPost = array_filter($data, function ($val, $key) use ($act) {
                        return strpos($key, ($act . '_')) !== false && $val !== null;
                    }, ARRAY_FILTER_USE_BOTH);

                    if (empty($indicatorPost)) {
                        return new Error('Data tidak boleh kosong', 400);
                    }

                    $updatedRow = [];
                    $v_key = null;
                    foreach ($indicatorPost as $key => $value) {
                        $indicator = explode('_', $key)[1];
                        $indicator = explode('-', $indicator);

                        if ($act == 'i') {
                            if ($data['key'] != $indicator[0]) {
                                continue;
                            }
                        } else if ($act == 'u') {
                            if ($data['key'] != $indicator[0] . '-' . $indicator[1]) {
                                continue;
                            }
                        }
                        $v_key = $indicator[0];

                        if (is_numeric($value)) {
                            $value = (float) $value;
                        }

                        $updatedRow[$indicator[0]][0][$indicator[2]] = $value;
                    }

                    if ($data['row_type'] == '0') {
                        $updatedRow = $updatedRow[0];
                    }

                    // update structure
                    for ($i = 2; $i <= $data['column_length']; $i++) {
                        if ($data['row_type'] == '0') {
                            if (!isset($updatedRow[0][$i])) {
                                $updatedRow[0][$i] = null;
                            }
                            // sort by key
                            ksort($updatedRow[0]);
                        } else {
                            if (!isset($updatedRow[$v_key][0][$i])) {
                                $updatedRow[$v_key][0][$i] = null;
                            }
                            // sort by key
                            ksort($updatedRow[$v_key][0]);
                        }
                    }

                    if ($act == 'i') {
                        if ($data['row_type'] == '0') {
                            $payload[] = $updatedRow[0];
                            $jenis_payload[] = "manual";
                        } else {
                            $payload[$v_key][] = $updatedRow[$v_key][0];
                            $jenis_payload[$v_key][] = "manual";
                        }
                    } else if ($act == 'u') {
                        $dIndex = explode('-', $data['key'])[1] - 1;
                        if ($data['row_type'] == '0') {
                            $payload[$dIndex] = $updatedRow[0];
                        } else {
                            $payload[$v_key][$dIndex] = $updatedRow[$v_key][0];
                        }
                    }
                } else {
                    $payload = [];

                    // loop data get input
                    $indicatorPost = array_filter($data, function ($val, $key) use ($act) {
                        return strpos($key, ($act . '_')) !== false && strpos($key, ($act . '-')) !== false;
                    }, ARRAY_FILTER_USE_BOTH);

                    foreach ($indicatorPost as $key => $value) {
                        $arrKey = explode('-', explode('_', $key)[1]);

                        $index = $arrKey[0];

                        $indexVal = $arrKey[1];

                        if (is_numeric($value)) {
                            $value = (float) $value;
                        }

                        $payload[$index][0][$indexVal] = $value;
                    }
                }

                if ($isNew) {
                    // save to database
                    $fillingIndicatorData->create([
                        'id_pengisian_indikator' => $idPengisianIndikator,
                        'id_indikator_laporan_kinerja' => $data['self']['id_indikator_laporan_kinerja'],
                        'data_pengisian_lk' => json_encode($payload),
                        'jenis_data_pengisian_lk' => json_encode($jenis_payload)
                    ]);
                } else {
                    // update data
                    $fillingIndicatorData->update([
                        'data_pengisian_lk' => json_encode($payload),
                        'jenis_data_pengisian_lk' => json_encode($jenis_payload)
                    ]);
                }
            } else {
                $d_key = explode('-', $data['iddelete']);
                if ($d_key[0] == '0') {
                    unset($payload[$d_key[1] - 1]);
                    unset($jenis_payload[$d_key[1] - 1]);
                } else {
                    unset($payload[$d_key[0]][$d_key[1] - 1]);
                    unset($jenis_payload[$d_key[0]][$d_key[1] - 1]);
                }

                // check if all payload is empty then remove data
                $isRemoveAll = true;
                foreach ($payload as $innerArray) {
                    if (!empty($innerArray)) {
                        $isRemoveAll = false;
                        break;
                    }
                }

                // action remove
                if ($isRemoveAll) {
                    $fillingIndicatorData->delete();
                } else {
                    // reindex $payload
                    if ($d_key[0] == '0') {
                        $payload = array_values($payload);
                        $jenis_payload = array_values($jenis_payload);
                    } else {
                        $payload[$d_key[0]] = array_values($payload[$d_key[0]]);
                        $jenis_payload[$d_key[0]] = array_values($jenis_payload[$d_key[0]]);
                    }

                    // update data
                    $fillingIndicatorData->update([
                        'data_pengisian_lk' => json_encode($payload),
                        'jenis_data_pengisian_lk' => json_encode($jenis_payload)
                    ]);
                }
            }
        }

        return $fillingIndicatorData;
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
                o.nama_unit,
                aa.nama_singkat_lembaga nama_lembaga_akreditasi,
                fg.nama_pengisian_panduan,
                ap.id id_audit_periode,
                o.id id_unit,
                aa.id id_lembaga_akreditasi,
                fg.id id_pengisian_panduan,
                fi.id_jadwal_audit,
                ja.nama_jadwal_audit,
                ja.tanggal_awal_pengisian,
                ja.tanggal_akhir_pengisian,
                d.kode_jenjang,
                d.nama_jenjang,
                d.id id_jenjang_pendidikan
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
            JOIN spmi.pengisian_panduan fg ON fg.apakah_aktif = true AND fg.id = aso.id_pengisian_panduan
                AND fg.waktu_dihapus is null
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

        $tahunPeriode = (int) $data['periode_audit'];
        $information = [
            'nama_jadwal_audit' => $data['nama_jadwal_audit'],
            'periode_audit' => $tahunPeriode,
            'periode_akademik' => ($tahunPeriode - 1) . '/' . $tahunPeriode,
            'nama_unit' => $data['kode_jenjang'] . ' - ' . $data['nama_unit'],
            'nama_lembaga_akreditasi' => $data['nama_lembaga_akreditasi'],
            'panduan_pengisian' => $data['nama_pengisian_panduan'],
            'filling_date' => Carbon::parse($data['tanggal_awal_pengisian'])->translatedFormat('d F Y')
                . ' s.d. ' . Carbon::parse($data['tanggal_akhir_pengisian'])->translatedFormat('d F Y'),
        ];

        $raw = [
            'nama_jenjang' => $data['nama_jenjang'],
            'id_audit_periode' => $data['id_audit_periode'],
            'id_unit' => $data['id_unit'],
            'id_lembaga_akreditasi' => $data['id_lembaga_akreditasi'],
            'id_pengisian_panduan' => $data['id_pengisian_panduan'],
            'id_jenjang_pendidikan' => $data['id_jenjang_pendidikan'],
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
                d.id id_jenjang_pendidikan,
                d.nama_jenjang
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
            JOIN spmi.pengisian_panduan fg on fg.apakah_aktif = true and fg.id = aso.id_pengisian_panduan
                AND fg.waktu_dihapus IS NULL
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

        $tahunPeriode = (int) $yearData['tahun_audit'];
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
            'id_jenjang_pendidikan' => $data['id_jenjang_pendidikan'],
            'nama_jenjang' => $data['nama_jenjang'],
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

    // FIXME: Belum fix core report
    /**
     * Show Report
     */
    public function showReport($idPengisianPanduan, $idPeriode, $idUnit)
    {
        $indicator = IndikatorLaporanKinerja::select('spmi.indikator_laporan_kinerja.*')
            ->join('spmi.mapping_lk', function ($join) use ($idPeriode, $idUnit) {
                $join->on('spmi.mapping_lk.id_indikator_laporan_kinerja', '=', 'spmi.indikator_laporan_kinerja.id')
                    ->where('spmi.mapping_lk.id_audit_periode', $idPeriode)
                    ->where('spmi.mapping_lk.id_unit', $idUnit);
            })
            ->where('spmi.indikator_laporan_kinerja.id_pengisian_panduan', $idPengisianPanduan)
            ->orderBy('spmi.indikator_laporan_kinerja.info_left', 'asc')
            ->distinct()
            ->get();

        if (empty($indicator)) {
            return new Error('Data tidak ditemukan', 404);
        }

        return $indicator;
    }

    public function getDokumenPendukung($pengisianIndikatorId, $indikatorLaporanKinerjaId)
    {
        $sql =
            "SELECT
                dp.id,
                dp.pengisian_indikator_id,
                dp.id_indikator_laporan_kinerja,
                d.nama_dokumen,
                d.extension_versi_terbaru,
                d.slug,
                dp.waktu_diubah,
                d.ukuran
            FROM spmi.dokumen_pendukung_pengisian_lk dp
            JOIN dms.dokumen d ON d.id = dp.id_dokumen
                AND d.waktu_dihapus IS NULL
            WHERE dp.pengisian_indikator_id = ?
                AND dp.id_indikator_laporan_kinerja = ?
                AND dp.waktu_dihapus IS NULL";

        $dokumenPendukungPengisian = DB::select($sql, [$pengisianIndikatorId, $indikatorLaporanKinerjaId]);

        return $dokumenPendukungPengisian;
    }

    public function  deleteDokumenPendukung($idPengisianIndikator, $idDokumenPendukung)
    {
        $dokumenPendukung = DokumenPendukungPengisianLk::where('pengisian_indikator_id', $idPengisianIndikator)
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
                DokumenPendukungPengisianLk::create([
                    'pengisian_indikator_id' => $idPengisianIndikator,
                    'id_indikator_laporan_kinerja' => $data['self']['id_indikator_laporan_kinerja'],
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

    public function convertTable($table, $isHasNumber, $isHasAction)
    {
        $table_column = [];
        $table_input = [];
        $table_disabled = [];

        $t_index = 0;

        $this->processTable($table, $table_column, $table_input, $table_disabled, $t_index);

        // append number
        if ($isHasNumber) {
            array_unshift($table_column, '_no_');
            array_unshift($table_input, '_no_');
        }

        // append action
        if ($isHasAction) {
            array_push($table_column, '_action_');
            array_push($table_input, '_action_');
        }

        // re-structure table input
        $temp = [];
        foreach (array_keys($table_input) as $index => $key) {
            if (is_int($key)) {
                $temp[$index] = $table_input[$key];
            } else {
                $temp[$index] = [$key, $table_input[$key]];
            }
        }
        $table_input = $temp;

        return [$table_column, $table_input, $table_disabled];
    }

    public function processTable($table, &$table_column, &$table_input, &$table_disabled, &$t_index)
    {
        foreach ($table as $d_key => $tb) {
            $t_index++;

            $label = $tb['label'];

            if (isset($tb['input'])) {
                $table_column[] = $label;
                if ($tb['input'] == 'readonly') {
                    $table_input[] = 'disabled';
                } else {
                    if ($tb['input'] == 'select') {
                        // Check if this is a lazy-load select
                        if (isset($tb['lazy_load']) && $tb['lazy_load'] && isset($tb['api_url'])) {
                            $table_input['select_lazy:' . rand(10000, 99999)] = $tb['api_url'];
                        } else {
                            $table_input['select:' . rand(10000, 99999)] = $tb['options'] ?? [];
                        }
                    } else {
                        $table_input[] = $tb['input'];
                    }
                }

                if (isset($tb['disabled'])) {
                    $table_disabled[$t_index - 1] = $tb['disabled'];
                }
            } else if (isset($tb['function'])) {
                $table_column[] = $label;
                $data = [
                    $tb['function'],
                    $tb['indexes'],
                    $tb['divides'] ?? null,
                ];

                if (!empty($tb['type'])) {
                    $data[] = $tb['type'];
                }

                $table_input['_function_:' . rand(10000, 99999)] = $data;
            } else if (isset($tb['childs'])) {
                $this->processTable($tb['childs'], $table_column[$label], $table_input, $table_disabled, $t_index);
            } else {
                throw new \Exception('Invalid table column');
            }
        }
    }

    public function getPreviousSelectOptions($idAuditPeriode, $idUnit, $idPengisianPanduan)
    {
        $data = DB::select(
            "WITH IndikatorAcuan AS (
                SELECT
                    ml.id_indikator_laporan_kinerja
                FROM
                    spmi.mapping_lk ml
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
                spmi.data_pengisian_lk dpl
            JOIN IndikatorAcuan ia ON dpl.id_indikator_laporan_kinerja = ia.id_indikator_laporan_kinerja
            JOIN spmi.pengisian_indikator pi ON dpl.id_pengisian_indikator = pi.id
                AND pi.waktu_dihapus IS NULL
                AND pi.jenis_indikator = '".AkreditasiBuku::PERFORMANCE_REPORT."'
                AND pi.id_unit = :id_unit
                AND pi.id_pengisian_panduan = :id_pengisian_panduan
            JOIN spmi.jadwal_audit ja ON pi.id_audit_periode = ja.id_audit_periode
                AND (pi.id_jadwal_audit IS NULL OR ja.id = pi.id_jadwal_audit)
                AND ja.waktu_dihapus IS null
            JOIN spmi.jadwal_audit_unit jau ON ja.id = jau.id_jadwal_audit AND jau.id_unit = :id_unit
                AND jau.id_pengisian_panduan = :id_pengisian_panduan
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

        $jadwalExists = DB::table('spmi.jadwal_audit_unit as jau')
            ->join('spmi.jadwal_audit as ja', 'jau.id_jadwal_audit', '=', 'ja.id')
            ->where('ja.id_audit_periode', $data['previous_year_audit'])
            ->where('jau.id_unit', $data['id_unit'])
            ->where('jau.id_pengisian_panduan', $data['id_pengisian_panduan'])
            ->when(!empty($data['previous_jadwal_audit']), function ($query) use ($data) {
                $query->where('ja.id', $data['previous_jadwal_audit']);
            })
            ->whereNull('ja.waktu_dihapus')
            ->exists();

        if (!$jadwalExists) {
            return [false, "Tidak ada jadwal audit untuk unit kerja, periode audit, dan panduan pengisian yang dipilih."];
        }

        $indicator_ids_to_copy = [];

        if ($data['scope'] == '1') {
            if (empty($data['id_indikator_laporan_kinerja'])) {
                return [false, "Untuk scope yang dipilih, ID indikator wajib diisi."];
            }

            $indicatorExists = DB::table('spmi.mapping_lk')
                ->where('id_audit_periode', $data['previous_year_audit'])
                ->where('id_unit', $data['id_unit'])
                ->where('id_indikator_laporan_kinerja', $data['id_indikator_laporan_kinerja'])
                ->whereNull('waktu_dihapus')
                ->exists();

            if (!$indicatorExists) {
                return [false, "Indikator yang dipilih tidak terpetakan pada periode audit dan unit kerja sebelumnya."];
            }

            $indicator_ids_to_copy = is_array($data['id_indikator_laporan_kinerja'])
                ? $data['id_indikator_laporan_kinerja']
                : [$data['id_indikator_laporan_kinerja']];
        } else {
            // TODO: inner join mapping periode sekarang dan sebelumnya
            $mapped_indicators = DB::table('spmi.mapping_lk as ml')
                ->join('spmi.indikator_laporan_kinerja as il', 'ml.id_indikator_laporan_kinerja', '=', 'il.id')
                ->where('ml.id_audit_periode', $data['id_audit_periode'])
                ->where('ml.id_unit', $data['id_unit'])
                ->where('il.id_pengisian_panduan', $data['id_pengisian_panduan'])
                ->pluck('ml.id_indikator_laporan_kinerja');

            if ($mapped_indicators->isEmpty()) {
                return [false, "Tidak ada indikator yang terpetakan pada periode audit dan unit kerja yang dipilih."];
            }
            $indicator_ids_to_copy = $mapped_indicators->toArray();
        }

        $prev_answers = DB::table('spmi.data_pengisian_lk as dpl')
            ->join('spmi.pengisian_indikator as pi', 'dpl.id_pengisian_indikator', '=', 'pi.id')
            ->where('pi.id_unit', $data['id_unit'])
            ->where('pi.id_audit_periode', $data['previous_year_audit'])
            ->when(!empty($data['previous_jadwal_audit']), function ($query) use ($data) {
                $query->where('pi.id_jadwal_audit', $data['previous_jadwal_audit']);
            })
            ->whereIn('dpl.id_indikator_laporan_kinerja', $indicator_ids_to_copy)
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
                'jenis_indikator' => AkreditasiBuku::PERFORMANCE_REPORT,
            ],
            ['id_lembaga_akreditasi' => $panduan_pengisian->id_lembaga_akreditasi]
        );

        try {
            DB::transaction(function () use ($pengisian_indikator, $prev_answers, $data) {
                if ($data['scope'] == '1') {
                    DataPengisianLK::where('id_pengisian_indikator', $pengisian_indikator->id)
                        ->where('id_indikator_laporan_kinerja', $data['id_indikator_laporan_kinerja'])
                        ->delete();
                } else {
                    DataPengisianLK::where('id_pengisian_indikator', $pengisian_indikator->id)->delete();
                }

                $data_to_insert = [];
                foreach ($prev_answers as $prev) {
                    $data_to_insert[] = [
                        'id_pengisian_indikator' => $pengisian_indikator->id,
                        'id_indikator_laporan_kinerja' => $prev->id_indikator_laporan_kinerja,
                        'data_pengisian_lk' => $prev->data_pengisian_lk,
                        'jenis_data_pengisian_lk' => $prev->jenis_data_pengisian_lk,
                        'data_pengisian_lk_awal' => $prev->data_pengisian_lk_awal,
                        'teks_pengisian' => $prev->teks_pengisian,
                    ];
                }
                DataPengisianLK::insert($data_to_insert);
            });
            return [true, (object)["data" => $pengisian_indikator->id, "message" => "Berhasil menyalin data."]];
        } catch (\Exception $e) {
            Log::error('Gagal menyalin jawaban: ' . $e->getMessage());
            return [false, "Gagal menyalin data karena terjadi kesalahan pada server."];
        }
    }
}
