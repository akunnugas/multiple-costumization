<?php

namespace Modules\Core\Services;

use Modules\Core\Helpers\ManagementService;
use Modules\Core\Models\JenisPerguruanTinggi;

class JenisPerguruanTinggiManagementService
{
    /**
     * @var JenisPerguruanTinggi
     */
    protected $model = JenisPerguruanTinggi::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new JenisPerguruanTinggi;
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
     * @return JenisPerguruanTinggi
     */
    public function show(int $id): JenisPerguruanTinggi
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return JenisPerguruanTinggi
     */
    public function store(array $data): JenisPerguruanTinggi
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return JenisPerguruanTinggi
     */
    public function update(array $data, int $id): JenisPerguruanTinggi
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
