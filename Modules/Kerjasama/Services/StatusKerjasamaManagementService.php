<?php

namespace Modules\Kerjasama\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Kerjasama\Models\StatusKerjasama;

class StatusKerjasamaManagementService
{
    /**
     * @var StatusKerjasama
     */
    protected $model = StatusKerjasama::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new StatusKerjasama;
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
     * @return StatusKerjasama
     */
    public function show(int $id): StatusKerjasama
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return StatusKerjasama
     */
    public function store(array $data): StatusKerjasama
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return StatusKerjasama
     */
    public function update(array $data, int $id): StatusKerjasama
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
     * @return Error|bool
     */
    public function destroy(int $id): Error|bool
    {
        // if ($this->checkReference($id)) {
        //     return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
        // }

        $model = $this->model->findOrFail($id);

        try {
            $model->destroy($model->id);
        } catch (\Exception) {
            return new Error('Data gaga dihapus.');
        }

        return true;
    }

    /**
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return Error|null
     */
    public function destroySome($ids)
    {
        // if ($this->checkReferenceMultiple($ids)) {
        //     return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
        // }

        return ManagementService::create($this->model)->destroySome($ids);
    }

    public function getStatus($key): string | null
    {
        return $this->model
            ->query()
            ->where('status_kerjasama', $key)
            ->first(['id'])->id ?? null;
    }

}
