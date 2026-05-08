<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Helpers\SessionManager;
use Modules\Core\Services\Service;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\Gate\Models\Modul;
use Modules\Gate\Models\Role;
use Modules\SPMI\Models\DokumenHasilAudit;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;

class DokumenHasilAuditManagementService extends Service
{
    /**
     * @var DokumenHasilAudit
     */
    protected $model = DokumenHasilAudit::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new DokumenHasilAudit;
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
                COALESCE(en.id, o.id) id,
                o.id id_unit,
                ap.id id_audit_periode,
                ja.id id_jadwal_audit,
                ap.tahun_audit periode_audit,
                ja.nama_jadwal_audit,
                concat(jp.kode_jenjang, ' - ', o.nama_unit) nama_unit,
                d.nama_dokumen nama_dokumen,
                d.extension_versi_terbaru,
                d.alamat_versi_terbaru,
                fr.id id_hasil_akhir_audit,
                pp.nama_singkat AS nama_penilaian_panduan,
                CASE WHEN en.id is not null THEN true ELSE false END is_created
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
            JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = ja.id_audit_periode
                AND sa.waktu_dihapus IS NULL
            JOIN spmi.surat_tugas_auditor_pegawai stap ON stap.id_surat_tugas_auditor = sa.id
                AND stap.id_unit = o.id
                AND stap.posisi = ?
                AND stap.waktu_dihapus IS NULL
            JOIN core.biodata lp ON lp.id = stap.id_personil
                AND lp.waktu_dihapus IS NULL
            LEFT JOIN spmi.dokumen_hasil_audit en ON en.id_unit = o.id
                AND en.id_audit_periode = ap.id
                AND en.id_jadwal_audit = ja.id
                AND en.waktu_dihapus is null
            LEFT JOIN dms.dokumen d on d.id = en.id_dokumen
                AND d.waktu_dihapus is null";

        $defaultFilter = "o.waktu_dihapus is null";

        $fieldMap = [
            'periode_audit' => 'ap.tahun_audit::text',
            'nama_unit' => 'o.nama_unit',
            'nama_dokumen' => "CONCAT(d.nama_dokumen, '.', d.extension_versi_terbaru)",
            'id_unit_kerja' => 'o.id',
        ];

        $bindings = [$auditPeriodId, SuratTugasAuditorPegawai::POSITION_LEAD];

        $isInternalRole = SessionManager::isInternalRole();
        if (!$isInternalRole) {
            [$defaultFilter, $bindings] = $this->showIndexByRole($defaultFilter, $bindings);
            [$defaultFilter, $bindings] = $this->showIndexByUnitKerja($defaultFilter, $bindings);
        }

        [$sql, $bindings] = Pagination::buildQuery(
            bindings: $bindings,
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return DokumenHasilAudit
     */
    public function show(int $id): DokumenHasilAudit
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return DokumenHasilAudit|Error
     */
    public function store(array $data): DokumenHasilAudit|Error
    {
        $idData = request()->get('id_data') ?? null;
        if ($idData) {
            return $this->update($data, $idData);
        }

        $file = $data['id_dokumen'] ?? null;

        if (!isset($file)) {
            return new Error(message: 'Gagal mengupload berkas. Berkas wajib diisi.');
        }

        DB::beginTransaction();

        $fileName = $file->getClientOriginalName();
        $fileName = pathinfo($fileName, PATHINFO_FILENAME);

        $upload = new UploadDokumen();
        $upload = $upload->upload(
            file: $file,
            name: $fileName ?? 'Berita Acara',
            folderCode: UploadDokumen::SPMI_BERITA_ACARA,
            moduleCode: Modul::CODE_SPMI,
            note: null,
            withTransaction: false
        );

        if ($upload instanceof Error) {
            return $upload;
        }

        $data['id_dokumen'] = $upload->get()->id;

        try {
            $model = $this->model->create($data);
            $this->authorize('create', $model);
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        // Execute upload
        $upload->executeUpload();

        if (Error::isError($upload->getError())) {
            DB::rollBack();
            return $upload->getError();
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
     * @return DokumenHasilAudit|Error
     */
    public function update(array $data, int $id): DokumenHasilAudit|Error
    {
        $model = $this->model->findOrFail($id);
        $this->authorize('update', $model);

        $file = $data['id_dokumen'] ?? null;

        if (!isset($file)) {
            return new Error(message: 'Gagal mengupload berkas. Berkas wajib diisi.');
        }

        DB::beginTransaction();

        $fileName = $file->getClientOriginalName();
        $fileName = pathinfo($fileName, PATHINFO_FILENAME);

        $upload = new UploadDokumen();
        $upload = $upload->update(
            data: [
                'file' => $file,
                'name' => $fileName ?? 'Berita Acara',
                'note' => null
            ],
            id: $model->id_dokumen,
            withTransaction: false,
            isReplace: true
        );

        if (Error::isError($upload->getError())) {
            DB::rollBack();
            return $upload->getError();
        }

        $data['id_dokumen'] = $upload->get()->id;

        try {
            $model->update($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        // Execute upload
        $upload->executeUpload();

        if (Error::isError($upload->getError())) {
            DB::rollBack();
            return $upload->getError();
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
