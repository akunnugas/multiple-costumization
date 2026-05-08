<?php

namespace Modules\HR\Services;

use Modules\Core\Helpers\ManagementService;
use Modules\HR\Models\LecturerDedicationType;

class LecturerDedicationTypeManagementService
{
    /**
     * @var LecturerDedicationType
     */
    protected $model = LecturerDedicationType::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new LecturerDedicationType;
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
     * @return LecturerDedicationType
     */
    public function show(int $id): LecturerDedicationType
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return LecturerDedicationType
     */
    public function store(array $data): LecturerDedicationType
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return LecturerDedicationType
     */
    public function update(array $data, int $id): LecturerDedicationType
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
