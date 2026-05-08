<?php

namespace Modules\Litabmas\Services;

use Exception;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Litabmas\Models\TemaKegiatan;

class TemaKegiatanService
{
    /**
     * @var TemaKegiatan
     */
    protected $model = TemaKegiatan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new TemaKegiatan;
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
        $table = $this->model->getTable();

        $sql = "select rt.id, rt.nama_tema from ".$table." rt";

        $defaultFilter = "rt.waktu_dihapus IS NULL";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return TemaKegiatan
     */
    public function show(int $id): TemaKegiatan|Error
    {
        try {
            return $this->model->findOrFail($id);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return TemaKegiatan
     */
    public function store(array $data): TemaKegiatan|Error
    {
        try {
            return $this->model->create($data);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return TemaKegiatan
     */
    public function update(array $data, int $id): TemaKegiatan|Error
    {
        try {
            $model = $this->model->findOrFail($id);

            $model->update($data);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Error|null
     */
    public function destroy(int $id): Error|null
    {
        try {
            $model = $this->model->findOrFail($id);

            $model->destroy($model->id);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }

    /**
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return Error|null
     */
    public function destroySome($ids)
    {
        try {
            return ManagementService::create($this->model)->destroySome($ids);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }
}
