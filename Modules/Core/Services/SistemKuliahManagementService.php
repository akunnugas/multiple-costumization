<?php

namespace Modules\Core\Services;

use Modules\Core\Helpers\ManagementService;
use Modules\Core\Models\SistemKuliah;

class SistemKuliahManagementService
{
    /**
     * @var SistemKuliah
     */
    protected $model = SistemKuliah::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new SistemKuliah;
    }

    /**
     * Menampilkan list data
     *
     * @param int $limit
     * @param int $page
     * @param array $sort
     * @param array $filter
     *
     * @return mixed
     */
    public function index(int | null $page = null, int | null $perPage = null, array $order = [], array $filter = []): mixed
    {
        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return SistemKuliah
     */
    public function show(int $id): SistemKuliah
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return SistemKuliah
     */
    public function store(array $data): SistemKuliah
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return SistemKuliah
     */
    public function update(array $data, int $id): SistemKuliah
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
    public function destroySome($ids)
    {
        return ManagementService::create($this->model)->destroySome($ids);
    }
}
