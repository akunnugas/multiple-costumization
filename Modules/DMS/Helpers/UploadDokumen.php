<?php

namespace Modules\DMS\Helpers;

use Illuminate\Http\UploadedFile;
use Modules\Core\Helpers\Error;
use Modules\DMS\Models\Dokumen;
use Modules\DMS\Services\DokumenManagementService;

class UploadDokumen extends FolderStructure
{
    /**
     * @var Dokumen
     */
    protected $dokumen;

    /**
     * @var Error
     */
    protected $error;

    /**
     * @var DokumenManagementService
     */
    protected $service;

    /**
     * Upload file.
     *
     * @param UploadedFile $file
     * @param string $name
     * @param string $folderCode
     * @param string|null $note
     * @param string $moduleCode
     *
     * @return UploadDokumen
     */
    public function upload(
        UploadedFile|null $file,
        string $name,
        string $folderCode,
        string|null $note = null,
        string $moduleCode,
        bool $withTransaction = true
    ): UploadDokumen {
        if (isset($this->dokumen)) {
            $this->error = new Error('Dokumen has been uploaded.', 409);

            return $this;
        }

        if (!isset($file)) {
            return $this;
        }

        $payload = [
            'file' => $file,
            'nama_dokumen' => $name,
            'kode_folder' => $folderCode,
            'catatan' => $note,

            // Default visibility diset ke public
            'visibilitas' => Dokumen::VISIBILITY_PUBLIC,
            'kode_modul' => $moduleCode
        ];

        $this->service = new DokumenManagementService();
        $this->dokumen = $this->service->store($payload, $withTransaction, false);

        if (Error::isError($this->dokumen)) {
            $this->error = $this->dokumen;
        }

        return $this;
    }

    /**
     * Update file.
     * 
     * @param array $data
     * @param int|null $id
     * @param bool $withTransaction
     * @param bool $isReplace
     * 
     * @return UploadDokumen|Error
     */
    public function update(array $data, int|null $id, bool $withTransaction = true, bool $isReplace = true): UploadDokumen|Error
    {
        if (isset($this->dokumen)) {
            $this->error = new Error('Dokumen has been uploaded.', 409);

            return $this;
        }

        if (!isset($data['file'])) {
            return $this;
        }

        $this->service = new DokumenManagementService;

        $this->dokumen = $this->service->update(
            data: [
                'file' => $data['file'],
                'nama_dokumen' => $data['name'],
                'catatan' => $data['note']
            ],
            id: $id,
            autoExecuteUpload: false,
            withTransaction: $withTransaction,
            isReplace: $isReplace,
        );

        if (Error::isError($this->dokumen)) {
            $this->error = $this->dokumen;

            return $this;
        }

        return $this;
    }

    public function updateMultiple(array $data, bool $withTransaction = true, bool $isReplace = true)
    {
        if (isset($this->dokumen)) {
            $this->error = new Error('Dokumen has been uploaded.', 409);

            return $this;
        }

        $data = array_map(function ($item) {
            if (!isset($item['file'])) {
                return null;
            }

            return [
                'id' => $item['id'],
                'file' => $item['file'],
                'nama_dokumen' => $item['name'],
                'catatan' => $item['note']
            ];
        }, $data);

        $data = array_values(array_filter($data));

        $this->service = new DokumenManagementService;

        $this->dokumen = $this->service->updateMultiple(
            data: $data,
            autoExecuteUpload: false,
            withTransaction: $withTransaction,
            isReplace: $isReplace,
        );

        if (Error::isError($this->dokumen)) {
            $this->error = $this->dokumen;

            return $this;
        }

        return $this;
    }

    /**
     * Upload multiple files.
     *
     * @param array $data
     * @param bool $withTransaction
     *
     * @return UploadDokumen
     */
    public function uploadMultiple(array $data, bool $withTransaction = true)
    {
        if (isset($this->dokumen)) {
            $this->error = new Error('Dokumen has been uploaded.', 409);

            return $this;
        }

        $data = array_map(function ($item) {
            $item['visibilitas'] = Dokumen::VISIBILITY_PUBLIC;
            $item['kode_folder'] = $item['folderCode'];
            $item['kode_modul'] = $item['moduleCode'];

            unset($item['folderCode']);
            unset($item['moduleCode']);

            return $item;
        }, $data);

        $this->service = new DokumenManagementService();
        $this->dokumen = $this->service->storeMultiple($data, $withTransaction, false);

        if (Error::isError($this->dokumen)) {
            $this->error = $this->dokumen;

            return $this;
        }

        return $this;
    }

    /**
     * Set private permission.
     *
     * @param string $type
     * @param array $permission
     *
     * @return UploadDokumen
     */
    public function setPrivate(string $type, array $permission): UploadDokumen
    {
        if (!isset($this->dokumen)) {
            $this->error = new Error('Please upload Dokumen first.', 400);

            return $this;
        }

        if (is_array($this->dokumen)) {
            // Jika Dokumen berupa array, maka setiap item akan diberikan permission
            $this->dokumen = array_map(function ($item) use ($type, $permission) {
                return $this->service->setPermission(
                    Dokumen: $item,
                    type: $type,
                    data: $permission
                );
            }, $this->dokumen);
        } else {
            $this->dokumen = $this->service->setPermission(
                Dokumen: $this->dokumen,
                type: $type,
                data: $permission
            );
        }

        return $this;
    }

    /**
     * Get Dokumen.
     *
     * @return Dokumen|array|Error|null
     */
    public function get(): Dokumen|array|Error|null
    {
        if (isset($this->error)) {
            return $this->error;
        }

        return $this->dokumen;
    }

    /**
     * Execute upload.
     */
    public function executeUpload(): int|Error
    {
        if (isset($this->error)) {
            return $this->error;
        }

        $upload = $this->service?->executeUpload($this->dokumen);

        if ($upload instanceof Error) {
            $this->error = $upload;

            return $this->error;
        }

        return 0;
    }

    public function getError(): Error|null
    {
        return $this->error ?? null;
    }
}
