<?php

namespace Modules\PMB\Services;

use Modules\Core\Helpers\ManagementService;
use Modules\PMB\Models\PilihanProdi;

class PilihanProdiManagementService
{
    /**
     * @var PilihanProdi
     */
    protected $model = PilihanProdi::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PilihanProdi;
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
     * @return PilihanProdi
     */
    public function show(int $id): PilihanProdi
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return PilihanProdi
     */
    public function store(array $data): PilihanProdi
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return PilihanProdi
     */
    public function update(array $data, int $id): PilihanProdi
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
}
