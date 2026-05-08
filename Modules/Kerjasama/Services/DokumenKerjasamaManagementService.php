<?php

namespace Modules\Kerjasama\Services;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Helpers\Error;
use Illuminate\Support\Facades\DB;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\DMS\Services\DokumenManagementService;
use Modules\Gate\Models\Modul;
use Modules\Kerjasama\Models\DokumenKerjasama;
use Modules\Kerjasama\Models\Kerjasama;

// use Modules\Kerjasama\Models\PengisianPanduan;

class DokumenKerjasamaManagementService
{
    /**
     * @var Kerjasama
     */
    protected $model = DokumenKerjasama::class;

    protected DokumenManagementService $dokumenCoreService;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new DokumenKerjasama;
        $this->dokumenCoreService = new DokumenManagementService();
    }

    public function getByModelId($idModel): Collection {
        return $this->model
            ->with(['dokumen'])
            ->where('id_kerjasama', $idModel)
            ->get();
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Kerjasama
     */
    public function store(array $data): DokumenKerjasama|Error
    {
        DB::beginTransaction();

        $upload = $this->processUploadDocument($data, 'id_dokumen', UploadDokumen::KERJASAMA_DATA);

        if (!empty($upload) && Error::isError($upload->getError())) {
            return $upload->getError();
        }

        if (!empty($upload)) {
            $data['id_dokumen'] = $upload->get()?->id;
        }

        try {
            $model = $this->model->create($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error(exception: $e);
        }

        // Execute upload
        if (!empty($upload)) {
            $upload->executeUpload();
        }

        if (!empty($upload) && Error::isError($upload->getError())) {
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
     * @return Kerjasama
     */
    public function update(array $data, int $id): DokumenKerjasama|Error
    {
        DB::beginTransaction();
        $model = $this->model->findOrFail($id);
        $upload = $this->processUploadDocument($data, 'id_dokumen', UploadDokumen::KERJASAMA_DATA, $model);

        if (!empty($upload)) {
            $data['id_dokumen'] = $upload->get()?->id;
        } else {
            unset($data['id_dokumen']);
        }

        $model->update($data);

        if (!empty($upload) && Error::isError($upload->getError())) {
            DB::rollBack();
            return $upload->getError();
        }

        // Execute upload
        $upload?->executeUpload();

        DB::commit();
        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Error|bool
     */
    public function destroy(int $id): Error|bool
    {
        try {
            $model = $this->model->findOrFail($id);

            $this->dokumenCoreService->destroy($model->id_dokumen);

            $model->destroy($model->id);
        } catch (\Exception) {
            return new Error('Data gaga dihapus.');
        }

        return true;
    }

    /**
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return Error|null
     */
    public function destroySome($ids)
    {
        try {
            DB::transaction(function () use ($ids) {
                // get id_dokumen dari dokumen_kegiatan
                $idsDokumen = $this->model->query()
                    ->whereIn('id', $ids)
                    ->get(['id_dokumen'])
                    ->pluck('id_dokumen')
                    ->toArray();

                // hapus dokumen pada DMS
                $this->dokumenCoreService->destroySome($idsDokumen);

                // hapus dokumen pada dokumen_kerjasama
                $this->model->destroy($ids);
            });
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }

    /**
     * Proses upload document ke DMS.
     *
     * @param array $data
     * @param string $field
     * @param string $folderCode
     * @param Kerjasama|null $model
     * @return UploadDokumen|null|Error
     */
    private function processUploadDocument(array $data, string $field, string $folderCode, Kerjasama $model = null): UploadDokumen|null|Error
    {
        $document = $data[$field] ?? null;
        if (empty($document)) {
            return null;
        }
        $docName = $document->getClientOriginalName();
        $docName = pathinfo($docName, PATHINFO_FILENAME);

        $upload = new UploadDokumen();
        if (empty($model->{$field})) {
            $document = $upload->upload(
                file: $document,
                name: $docName,
                folderCode: $folderCode,
                note: null,
                moduleCode: Modul::CODE_KERJASAMA,
                withTransaction: false,
            );
        } else {
            $document = $upload->update(
                data: [
                    'file' => $document,
                    'name' => $docName,
                    'note' => null,
                ],
                id: $model->{$field},
                withTransaction: false,
                isReplace: true
            );
        }

        if ($document instanceof Error) {
            return new Error($document->message);
        }

        return $document;
    }
}
