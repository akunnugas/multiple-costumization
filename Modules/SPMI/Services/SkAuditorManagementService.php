<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\Pegawai;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\Gate\Models\Modul;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\SkAuditor;
use Modules\SPMI\Models\SkAuditorPegawai;
use Modules\SPMI\Models\SuratTugasAuditor;

class SkAuditorManagementService
{
    /**
     * @var SkAuditor
     */
    protected $model = SkAuditor::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new SkAuditor;
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
                    sk.id,
                    ap.tahun_audit AS periode_audit,
                    sk.nomor_sk,
                    sk.tanggal_diterbitkan,
                    sk.tanggal_awal_berlaku,
                    sk.tanggal_akhir_berlaku,
                    d.nama_dokumen as nama_dokumen_sk,
                    d.extension_versi_terbaru as sk_document_extension
                FROM $table sk
                JOIN spmi.audit_periode ap ON ap.id = sk.id_audit_periode
                    AND ap.waktu_dihapus IS NULL
                LEFT JOIN dms.dokumen d ON d.id = sk.id_dokumen
                    AND d.waktu_dihapus IS NULL";

        $fieldMap = [
            'periode_audit' => 'ap.tahun_audit::text',
            'tanggal_diterbitkan' => 'sk.tanggal_diterbitkan::text',
            'tanggal_diterbitkan_range' => "CONCAT(sk.tanggal_awal_berlaku::text, ' - ', sk.tanggal_akhir_berlaku::text)",
            'nama_dokumen_sk' => "CONCAT(d.nama_dokumen, '.', d.extension_versi_terbaru)",
        ];

        $defaultFilter = "sk.waktu_dihapus is null";

        [$sql, $bindings] = Pagination::buildQuery(
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
     * @return SkAuditor
     */
    public function show(int $id): SkAuditor
    {
        $data = $this->model->findOrFail($id);

        // get pegawai
        $data->data_pegawai = implode('::::', SkAuditorPegawai::where('id_sk_auditor', $id)->get()->map(function ($item) {
            $biodata = Biodata::find($item->id_personil);
            $pegawai = Pegawai::where('id_biodata', $biodata->id)->first();
            $nama = ($biodata->gelar_depan ? $biodata->gelar_depan : '') . $biodata->nama . ($biodata->gelar_belakang ? ', ' . $biodata->gelar_belakang : '');
            $nip = empty($pegawai->nip) ? "" : ' (' . $pegawai?->nip . ')';
            return $nama . $nip;
        })->toArray());

        return $data;
    }

    public function showEmployees(int $id)
    {
        $sql = "SELECT p.id, coalesce(p.gelar_depan, '') || ' ' || p.nama || ', ' || coalesce(p.gelar_belakang, '') nama, e.nip
                FROM spmi.sk_auditor_pegawai skp
                JOIN core.biodata p ON p.id = skp.id_personil
                    AND p.waktu_dihapus IS NULL
                JOIN core.pegawai e ON e.ref_key_siakad = p.ref_key_pegawai
                    AND e.waktu_dihapus IS NULL
                JOIN hr.employee_statuses s ON s.id = e.id_status_pegawai
                    AND s.waktu_dihapus IS NULL
                WHERE skp.id_sk_auditor = ?
                    AND s.is_active = true";

        $data = DB::select($sql, [$id]);

        return array_map(function ($item) {
            return (array) $item;
        }, $data ?? []);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return SkAuditor
     */
    public function store(array $data): SkAuditor|Error
    {
        // Cek apakah ada surat keputusan di periode yang sama
        $isExist = SkAuditor::where('id_audit_periode', $data['id_audit_periode'])
            ->where('waktu_dihapus', null)
            ->exists();

        if ($isExist) {
            $selectedPeriod = AuditPeriode::find($data['id_audit_periode']);
            return new Error('Surat Keputusan Auditor untuk periode audit ' . $selectedPeriod->tahun_audit . ' sudah ada.', 400);
        }

        DB::beginTransaction();
        $file = $data['id_dokumen'] ?? null;
        $fileName = explode('.', $file?->getClientOriginalName())[0];

        $upload = new UploadDokumen();
        $upload = $upload->upload(
            file: $file,
            name: $fileName,
            folderCode: UploadDokumen::SPMI_SURAT_KEPUTUSAN,
            moduleCode: Modul::CODE_SPMI,
            note: null,
            withTransaction: false
        );

        if (Error::isError($upload->getError())) {
            return $upload->getError();
        }

        $data['id_dokumen'] = $upload->get()?->id;

        try {
            $model = $this->model->create($data);
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

        $saveEmployee = $this->saveEmployee($data['sk_auditor_pegawai'] ?? [], $model->id);
        if (Error::isError($saveEmployee)) {
            DB::rollBack();
            return $saveEmployee;
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
     * @return SkAuditor|Error
     */
    public function update(array $data, int $id): SkAuditor|Error
    {
        $isExist = SkAuditor::where('id_audit_periode', $data['id_audit_periode'])
            ->where('waktu_dihapus', null)
            ->whereNot('id', $id)
            ->exists();

        if ($isExist) {
            $selectedPeriod = AuditPeriode::find($data['id_audit_periode']);
            return new Error('Surat Keputusan Auditor untuk periode audit ' . $selectedPeriod->tahun_audit . ' sudah ada.', 400);
        }

        DB::beginTransaction();

        $model = $this->model->findOrFail($id);

        $file = $data['id_dokumen'] ?? null;
        $fileName = explode('.', $file?->getClientOriginalName())[0];

        $upload = new UploadDokumen();
        if (isset($model->id_dokumen)) {
            $upload = $upload->update(
                data: [
                    'file' => $file,
                    'name' => $fileName,
                    'note' => null,
                ],
                id: $model->id_dokumen,
                withTransaction: false,
                isReplace: true
            );
        } else {
            $upload = $upload->upload(
                file: $file,
                name: $fileName,
                folderCode: UploadDokumen::SPMI_SURAT_KEPUTUSAN,
                moduleCode: Modul::CODE_SPMI,
                note: null,
                withTransaction: false,
            );
        }

        if (Error::isError($upload->getError())) {
            DB::rollBack();
            return $upload->getError();
        }

        $data['id_dokumen'] = $upload->get()?->id ?? $model->id_dokumen;

        $model->update($data);

        // Execute upload
        $upload->executeUpload();

        if (Error::isError($upload->getError())) {
            DB::rollBack();
            return $upload->getError();
        }

        $saveEmployee = $this->saveEmployee($data['sk_auditor_pegawai'] ?? [], $id);
        if (Error::isError($saveEmployee)) {
            DB::rollBack();
            return $saveEmployee;
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
        $model = $this->model->findOrFail($id);

        if (SuratTugasAuditor::where('id_audit_periode', $model->id_audit_periode)->exists()) {
            return new Error('Penghapusan data SK Auditor gagal, data masih dijadikan referensi');
        }

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
        foreach ($ids as $id) {
            $model = $this->model->findOrFail($id);

            if (SuratTugasAuditor::where('id_audit_periode', $model->id_audit_periode)->exists()) {
                return new Error('Penghapusan data SK Auditor gagal, data masih dijadikan referensi');
            }
        }

        return ManagementService::create($this->model)->destroySome($ids);
    }

    private function saveEmployee($data, $id): int|Error
    {
        $totalSaved = 0;

        $currentData = SkAuditorPegawai::where('id_sk_auditor', $id)
            ->whereIn('id_personil', $data)
            ->get()->pluck('id_personil')->toArray();

        $toStoreData = array_diff($data, $currentData);

        if (!empty($toStoreData)) {
            try {
                foreach ($toStoreData as $personId) {
                    $personId = (int) $personId;
                    SkAuditorPegawai::create([
                        'id_sk_auditor' => $id,
                        'id_personil' => $personId,
                    ]);
                    $totalSaved++;
                }
            } catch (\Exception $e) {
                return new Error(exception: $e);
            }
        }

        try {
            SkAuditorPegawai::where('id_sk_auditor', $id)->whereNotIn('id_personil', $data)->delete();
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }

        return $totalSaved;
    }
}
