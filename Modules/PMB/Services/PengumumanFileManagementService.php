<?php

namespace Modules\PMB\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\PMB\Models\PengumumanFile;

class PengumumanFileManagementService
{
    /**
     * @var PengumumanFile
     */
    protected $model = PengumumanFile::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengumumanFile;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return PengumumanFile
     */
    public function store(array $data): PengumumanFile
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return PengumumanFile
     */
    public function update(array $data, int $id): PengumumanFile
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
