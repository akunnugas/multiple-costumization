<?php

namespace Modules\SPMI\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\SPMI\Models\DataPengisianLK;

class DataPengisianLKManagementService
{
    /**
     * @var DataPengisianLK
     */
    protected $model = DataPengisianLK::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new DataPengisianLK;
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
     * @return DataPengisianLK
     */
    public function show(int $id): DataPengisianLK
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return DataPengisianLK
     */
    public function store(array $data): DataPengisianLK
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return DataPengisianLK
     */
    public function update(array $data, int $id): DataPengisianLK
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

    /**
     * Destroy spesific data by filling indicator id.
     *
     * @param array $datas (data from request)
     * @return array
     */
    public function destroyByID(array $datas, $recordKey): array
    {
        // delete data by record key
        unset($recordKey - 1);

        return array_values($datas);
    }
}
