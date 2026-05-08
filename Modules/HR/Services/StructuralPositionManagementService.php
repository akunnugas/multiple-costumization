<?php

namespace Modules\HR\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\HR\Models\StructuralPosition;

class StructuralPositionManagementService
{
    /**
     * @var StructuralPosition
     */
    protected $model = StructuralPosition::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new StructuralPosition;
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

        $sql = "SELECT
                    sp.id,
                    sp.code,
                    sp.name,
                    pr.name  parent
                FROM $table sp
                LEFT JOIN $table pr ON pr.id = sp.parent_id";

        // Defaultnya order berdasarkan info left
        if (!empty($order) && $order['field'] == 'id') {
            $order = ['field' => 'sp.info_left', 'direction' => 'asc', 'desc' => false];
        }

        $defaultFilter = "sp.waktu_dihapus is null";
        $fieldMap = [
            'id' => 'sp.id',
            'name' => 'sp.name',
            'code' => 'sp.code',
            'parent' => 'pr.name',
        ];

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
     * @return StructuralPosition
     */
    public function show(int $id): StructuralPosition
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return StructuralPosition
     */
    public function store(array $data): StructuralPosition
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return StructuralPosition
     */
    public function update(array $data, int $id): StructuralPosition
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
