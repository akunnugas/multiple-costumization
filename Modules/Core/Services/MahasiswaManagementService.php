<?php

namespace Modules\Core\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\Mahasiswa;
use Modules\Core\Models\UnitKerja;
use Modules\Gate\Models\User;
use Modules\Gate\Services\UserManagementService;

class MahasiswaManagementService
{
    /**
     * @var Mahasiswa
     */
    protected $model = Mahasiswa::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Mahasiswa;
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
     * @return Mahasiswa
     */
    public function show(int $id): Mahasiswa
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Mahasiswa
     */
    public function store(array $data): Mahasiswa
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Mahasiswa
     */
    public function update(array $data, int $id): Mahasiswa
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
