<?php

namespace Modules\Litabmas\Services;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Helpers\SyncSiakad;
use Modules\Litabmas\Models\JenisOutputPenelitian;
use Modules\Litabmas\Models\JenisPublikasi;

class JenisOutputPenelitianService
{
    /**
     * @var JenisOutputPenelitian
     */
    protected $model = JenisOutputPenelitian::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new JenisOutputPenelitian;
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

        $sql = "select otp.id, otp.nama_output from " . $table . " otp";

        $defaultFilter = "otp.waktu_dihapus is null";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Get list data output dari database (cache).
     *
     * @return Collection
     */
    public function getListCache()
    {
        return $this->model->getListCache();
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     * @return JenisOutputPenelitian|Error
     */
    public function show(int $id): JenisOutputPenelitian|Error
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
     * @return JenisOutputPenelitian|Error
     */
    public function store(array $data): JenisOutputPenelitian|Error
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
     * @return JenisOutputPenelitian|Error
     */
    public function update(array $data, int $id): JenisOutputPenelitian|Error
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
     * @return null|Error
     */
    public function destroy(int $id): null|Error
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
     * @return null|Error
     */
    public function destroySome($ids)
    {
        try {
            DB::transaction(function () use ($ids) {
                JenisOutputPenelitian::destroy($ids);
            });
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }
}
