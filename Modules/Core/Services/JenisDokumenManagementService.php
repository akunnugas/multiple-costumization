<?php

namespace Modules\Core\Services;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Models\JenisDokumen;

class JenisDokumenManagementService
{
    /**
     * @var JenisDokumen
     */
    protected $model = JenisDokumen::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new JenisDokumen;
    }

    /**
     * Menampilkan list data
     *
     * @param int|null $page
     * @param int|null $perPage
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
     * @return JenisDokumen
     */
    public function show(int $id): JenisDokumen
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return JenisDokumen
     */
    public function store(array $data): JenisDokumen
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return JenisDokumen|Error
     */
    public function update(array $data, int $id): JenisDokumen|Error
    {
        $model = $this->model->findOrFail($id);

        // is_static tidak dapat diubah
        if ($model->is_static) {
            return new Error('Tidak dapat mengubah data statis');
        }

        $model->update($data);

        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return null|Error
     */
    public function destroy(int $id): null|Error
    {
        $model = $this->model->findOrFail($id);

        // is_static tidak dapat dihapus
        if ($model->is_static) {
            return new Error('Tidak dapat menghapus data statis');
        }

        $model->destroy($model->id);

        // karena return void nggk bisa return sendiri, jadi diubah jadi return null agar Error bisa di return
        return null;
    }
}
