<?php

namespace Modules\SPMI\Services;

use Modules\Core\Helpers\ManagementService;
use Modules\SPMI\Models\PenilaianKlaster;

class PenilaianKlasterManagementService
{
    /**
     * @var PenilaianKlaster
     */
    protected $model = PenilaianKlaster::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PenilaianKlaster;
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
     * @return PenilaianKlaster
     */
    public function show(int $id): PenilaianKlaster
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return PenilaianKlaster
     */
    public function store(array $data): PenilaianKlaster
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return PenilaianKlaster
     */
    public function update(array $data, int $id): PenilaianKlaster
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
