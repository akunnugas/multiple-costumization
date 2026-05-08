<?php

namespace Modules\PMB\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\PMB\Models\SeleksiKomponen;

class SeleksiKomponenManagementService
{
    /**
     * @var SeleksiKomponen
     */
    protected $model = SeleksiKomponen::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new SeleksiKomponen;
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
     * @return SeleksiKomponen
     */
    public function show(int $id): SeleksiKomponen
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return SeleksiKomponen
     */
    public function store(array $data): SeleksiKomponen
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return SeleksiKomponen
     */
    public function update(array $data, int $id): SeleksiKomponen
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
