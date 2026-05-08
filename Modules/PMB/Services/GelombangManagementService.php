<?php

namespace Modules\PMB\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\PMB\Models\Gelombang;

class GelombangManagementService
{
    /**
     * @var Gelombang
     */
    protected $model = Gelombang::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Gelombang;
    }

    /**
     * Menampilkan list data
     *
     * @param int $limit
     * @param int $page
     * @param array $sort
     * @param array $filter
     *
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $sql = "select b.id, b.nama_gelombang, b.nama_gelombang as text
                from pmb.gelombang b";
        $defaultFilter = "b.waktu_dihapus is null";

        $fieldMap = ['nama_gelombang' => 'b.nama_gelombang'];
        [$sql, $bindings] = Pagination::buildQuery(
            $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Gelombang
     */
    public function show(int $id): Gelombang
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Gelombang
     */
    public function store(array $data): Gelombang
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Gelombang
     */
    public function update(array $data, int $id): Gelombang
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
        try {
            DB::transaction(function () use ($ids) {
                Gelombang::destroy($ids);
            });
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }
}
