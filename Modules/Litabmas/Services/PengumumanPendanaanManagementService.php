<?php

namespace Modules\Litabmas\Services;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Format;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\UnitKerja;
use Modules\DMS\Helpers\FolderStructure;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\DMS\Models\Dokumen;
use Modules\Gate\Models\Modul;
use Modules\Gate\Models\Role;
use Modules\Litabmas\Models\AgendaKegiatan;
use Modules\Litabmas\Models\PengumumanPendanaan;

class PengumumanPendanaanManagementService
{
    /**
     * @var PengumumanPendanaan
     */
    protected $model = PengumumanPendanaan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengumumanPendanaan;
    }

    /**
     * Menampilkan list data
     *
     * @param int|null $page
     * @param int|null $perPage
     * @param array $order
     * @param array $filter
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $table = $this->model->getTable();

        $sql = "select pengpp.id, pengpp.judul, pengpp.informasi, pengpp.id_dokumen_lampiran,
                pengpp.status_pengumuman, pengpp.waktu_dipublikasi, p.tahun as nama_periode_pendanaan
            from $table pengpp
            join litabmas.periode_pendanaan p on pengpp.id_periode_pendanaan = p.id";

        // default filter & bindings
        $defaultFilter = "pengpp.waktu_dihapus is null";

        $fieldMap = [
            'nama_periode_pendanaan' => 'p.tahun',
        ];

        // cek scope user
        $userRole = auth()->user()?->kode_role;

        $bindings = [];
        $isDosen = in_array($userRole, [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL]);
        if ($isDosen) { // cek hanya yg sudah publish
            $defaultFilter .= " and pengpp.status_pengumuman = :status_pengumuman";
            $bindings['status_pengumuman'] = PengumumanPendanaan::STATUS_TERPUBLIKASI;
        }

        // default order
        if (empty($order)) {
            $defaultOrder = [
                'field' => 'pengpp.status_pengumuman',
            ];
        }

        [$sql, $params] = Pagination::buildQuery(
            query: $sql,
            bindings: $bindings,
            order: $order,
            filter: $filter,
            fieldMap: $fieldMap,
            defaultOrder: $defaultOrder ?? [],
            defaultFilter: $defaultFilter,
            bindingUsingName: true
        );

        return Pagination::create($sql, $params, $page, $perPage, bindingUsingName: true);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     * @return Collection|ModelNotFoundException
     */
    public function show(int $id): Collection|ModelNotFoundException
    {
        $table = $this->model->getTable();

        $sql = "select pengpp.id, pengpp.judul, pengpp.informasi, pengpp.id_dokumen_lampiran,
                pengpp.status_pengumuman, pengpp.waktu_dipublikasi, pengpp.id_periode_pendanaan
            from $table pengpp";

        // default filter & bindings
        $defaultFilter = " where pengpp.waktu_dihapus is null
            and pengpp.id = :id";
        $bindings = [
            'id' => $id
        ];

        // cek scope user
        $userRole = auth()->user()?->kode_role;

        $isDosen = in_array($userRole, [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL]);
        if ($isDosen) { // cek hanya yg sudah publish
            $defaultFilter .= " and pengpp.status_pengumuman = :status_pengumuman";
            $bindings['status_pengumuman'] = PengumumanPendanaan::STATUS_TERPUBLIKASI;
        }

        // final sql query
        $sql .= $defaultFilter;
        $select = DB::select($sql, $bindings);

        if (!isset($select[0])) {
            throw new ModelNotFoundException();
        }

        $data = Collection::make($select[0]);

        if (!empty($data['waktu_dipublikasi'])) {
            $data['tanggal_dipublikasi'] = Carbon::parse($data['waktu_dipublikasi'])->translatedFormat('d F Y');
        }

        return $data;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     * @return PengumumanPendanaan|Error
     */
    public function store(array $data): PengumumanPendanaan|Error
    {
        DB::beginTransaction();

        try {
            // upload document
            $documents = $this->prosesUploadDokumenKeDB([
                [$data['id_dokumen_lampiran'] ?? null, 'id_dokumen_lampiran', 'poster'],
            ]);
            $dokumenLampiran = $documents['id_dokumen_lampiran'] ?? null;

            // get id dokumen dari dms nya
            $data['id_dokumen_lampiran'] = $dokumenLampiran?->get()->id ?? null;

            // get status
            $data['status_pengumuman'] ??= PengumumanPendanaan::STATUS_DRAFT;

            if ($data['status_pengumuman'] == PengumumanPendanaan::STATUS_TERPUBLIKASI) {
                $data['waktu_dipublikasi'] = now();
            }

            $model = $this->model->create($data);

            // execute upload ke s3
            $this->prosessUploadDokumenKeS3([$dokumenLampiran]);
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof ValidationException) {
                throw $e;
            }

            return new Error(exception: $e);
        }

        DB::commit();

        return $model;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     * @return PengumumanPendanaan|Error
     */
    public function update(array $data, int $id): PengumumanPendanaan|Error
    {
        DB::beginTransaction();

        try {
            // upload document
            $documents = $this->prosesUploadDokumenKeDB([
                [$data['id_dokumen_lampiran'] ?? null, 'id_dokumen_lampiran', 'poster'],
            ]);
            $dokuemnLampiran = $documents['id_dokumen_lampiran'] ?? null;

            // get id dokumen dari dms nya
            unset($data['id_dokumen_lampiran']);
            if (!empty($dokuemnLampiran)) {
                $data['id_dokumen_lampiran'] = $dokuemnLampiran?->get()->id ?? null;
            }

            // update
            $data['status_pengumuman'] ??= PengumumanPendanaan::STATUS_DRAFT;

            if ($data['status_pengumuman'] == PengumumanPendanaan::STATUS_TERPUBLIKASI) {
                $data['waktu_dipublikasi'] = now();
            }

            $model = $this->model->findOrFail($id);
            $model->update($data);

            // execute upload ke s3
            $this->prosessUploadDokumenKeS3([$dokuemnLampiran]);
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof ValidationException) {
                throw $e;
            }

            return new Error(exception: $e);
        }

        DB::commit();

        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     * @return null|Error
     */
    public function destroy(int $id): null|Error
    {
        try {
            $model = $this->model->findOrFail($id);

            // cek jika sudah di publish maka tidak bisa dihapus
            if ($model->status_pengumuman === PengumumanPendanaan::STATUS_TERPUBLIKASI) {
                return new Error(message: 'Pengumuman yang sudah terpublikasi tidak bisa dihapus.');
            }

            $model->destroy($model->id);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }

    /*** --- [START] PRIVATE METHOD--- ***/
    /**
     * Proses upload dokumen ke database.
     *
     * @param array $data (isinya document, field, note)
     * @param PengumumanPendanaan|null $model
     * @return array
     * @throws Exception
     */
    private function prosesUploadDokumenKeDB(array $data, PengumumanPendanaan $model = null)
    {
        $documents = [];
        foreach ($data as $item) {
            $document = $item[0] ?? null;
            $field = $item[1] ?? null;
            $note = $item[2] ?? null;

            // ketika tidak ada document dianggap tidak ada update/perubahan jadi skip
            if (empty($document)) {
                continue;
            }

            // field tdk boleh kosong
            if (empty($field)) {
                throw new Exception('Field dokumen tidak boleh kosong');
            }

            $docName = explode('.', $document?->getClientOriginalName())[0];
            $docName = pathinfo($docName, PATHINFO_FILENAME);

            $upload = new UploadDokumen();
            if (empty($model->{$field})) {
                $document = $upload->upload(
                    file: $document,
                    name: $docName,
                    folderCode: FolderStructure::LITABMAS_PENGUMUMAN_PENDANAAN,
                    note: $note,
                    moduleCode: Modul::CODE_LITABMAS,
                    withTransaction: false,
                );
            } else {
                $document = $upload->update(
                    data: [
                        'file' => $document,
                        'name' => $docName,
                        'note' => $note,
                    ],
                    id: $model->{$field},
                    withTransaction: false,
                    isReplace: true
                );
            }

            if ($document instanceof Error) {
                // throw exception aja soalnya sekali gagal upload maka gagal semua, dan biar masuk catch
                throw new Exception($document->message);
            }

            $documents[$field] = $document;
        }

        return $documents;
    }

    /**
     * Proses upload dokumen pengajuan pendanaan ke S3.
     *
     * @param array $documents
     * @return void
     * @throws Exception
     */
    private function prosessUploadDokumenKeS3(array $documents)
    {
        foreach ($documents as $document) {
            if (!empty($document)) {
                $document->executeUpload();
                if (Error::isError($document->getError())) { // jika ada error saat proses dms
                    throw new Exception($document->getError());
                }
            }
        }
    }
    /*** --- [END] PRIVATE METHOD--- ***/
}
