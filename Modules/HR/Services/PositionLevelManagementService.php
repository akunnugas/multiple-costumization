<?php

namespace Modules\HR\Services;

use Illuminate\Support\Facades\Storage;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Models\UnitKerja;
use Modules\DMS\Helpers\FolderStructure;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\DMS\Models\Dokumen;
use Modules\DMS\Models\DocumentPermission;
use Modules\Gate\Models\Modul;
use Modules\HR\Models\PositionLevel;

class PositionLevelManagementService
{
    /**
     * @var PositionLevel
     */
    protected $model = PositionLevel::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PositionLevel;
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
        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return PositionLevel
     */
    public function show(int $id): PositionLevel
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return PositionLevel|Error
     */
    public function store(array $data): PositionLevel|Error
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return PositionLevel
     */
    public function update(array $data, int $id): PositionLevel
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
}
