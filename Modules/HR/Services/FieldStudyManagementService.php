<?php

namespace Modules\HR\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\HR\Models\FieldStudy;

class FieldStudyManagementService
{
    /**
     * @var FieldStudy
     */
    protected $model = FieldStudy::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new FieldStudy;
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
    public function index(int|null $page = null, int|null $perPage = null, array|null $order = [], array|null $filter = []): mixed
    {
        $table = $this->model->getTable();
        $sql = "SELECT
                    fs.id,
                    fs.code,
                    fs.name,
                    p.name as parent_field_study
                FROM $table fs
                LEFT JOIN $table p ON p.id = fs.parent_id";

        // Defaultnya order berdasarkan info left
        if (!empty($order) && $order['field'] == 'id') {
            $order = ['field' => 'fs.info_left', 'direction' => 'asc', 'desc' => false];
        }

        $fieldMap = [
            'id' => 'fs.id',
            'code' => 'fs.code',
            'name' => 'fs.name',
            'parent_field_study' => 'p.name',
        ];

        $defaultFilter = "fs.waktu_dihapus is null";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
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
     * @return FieldStudy
     */
    public function show(int $id): FieldStudy
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return FieldStudy
     */
    public function store(array $data): FieldStudy
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return FieldStudy
     */
    public function update(array $data, int $id): FieldStudy
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
