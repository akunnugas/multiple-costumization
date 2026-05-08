<?php

namespace Modules\PMB\Services;

use Modules\Core\Helpers\ManagementService;
use Modules\PMB\Models\SeleksiJenis;

class SeleksiJenisManagementService
{
    /**
     * @var SeleksiJenis
     */
    protected $model = SeleksiJenis::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new SeleksiJenis;
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
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return SeleksiJenis
     */
    public function show(int $id): SeleksiJenis
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return SeleksiJenis
     */
    public function store(array $data): SeleksiJenis
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return SeleksiJenis
     */
    public function update(array $data, int $id): SeleksiJenis
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
