<?php

namespace Modules\PMB\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\PMB\Models\Cache\JenisSyaratCache;
use Modules\PMB\Models\SyaratJenis;

class SyaratJenisManagementService
{
    /**
     * @var SyaratJenis
     */
    protected $model = SyaratJenis::class;

    /**
     * @var JenisSyaratCache
     */
    protected $modelCache = JenisSyaratCache::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new SyaratJenis;
        $this->modelCache = new JenisSyaratCache;
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
     * Mengambil semua data dari cache db.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->modelCache->get();
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return SyaratJenis
     */
    public function show(int $id): SyaratJenis
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return SyaratJenis
     */
    public function store(array $data): SyaratJenis
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return SyaratJenis
     */
    public function update(array $data, int $id): SyaratJenis
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
