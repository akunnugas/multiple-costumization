<?php

namespace Modules\PMB\Services;

use Modules\Core\Helpers\ManagementService;
use Modules\PMB\Models\SeleksiNilai;

class SeleksiNilaiManagementService
{
    /**
     * @var SeleksiNilai
     */
    protected $model = SeleksiNilai::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new SeleksiNilai;
    }

    /**
     * Menampilkan list data
     *
     * @param int $page
     * @param int $perPage
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
     * @return SeleksiNilai
     */
    public function show(int $id): SeleksiNilai
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return SeleksiNilai
     */
    public function store(array $data): SeleksiNilai
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return SeleksiNilai
     */
    public function update(array $data, int $id): SeleksiNilai
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
