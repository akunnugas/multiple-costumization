<?php

namespace Modules\HR\Services;

use Modules\Core\Helpers\ManagementService;
use Modules\HR\Models\RecognitionType;

class RecognitionTypeManagementService
{
    /**
     * @var RecognitionType
     */
    protected $model = RecognitionType::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new RecognitionType;
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
     * @return RecognitionType
     */
    public function show(int $id): RecognitionType
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return RecognitionType
     */
    public function store(array $data): RecognitionType
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return RecognitionType
     */
    public function update(array $data, int $id): RecognitionType
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
