<?php

namespace Modules\HR\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\HR\Models\FunctionalPosition;

class FunctionalPositionManagementService
{
    /**
     * @var FunctionalPosition
     */
    protected $model = FunctionalPosition::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new FunctionalPosition;
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
        $sql = "select fp.id, fp.code, fp.name, pl.name as position_level, fp.credit_number, fp.retirement_age
                from " . $this->model->getTable() . " fp
                left join hr.position_levels pl on pl.id = fp.position_level_id";

        $defaultFilter = "fp.waktu_dihapus is null";

        $fieldMap = [
            'id' => 'fp.id',
            'code' => 'fp.code',
            'name' => 'fp.name',
            'position_level' => 'pl.name',
            'credit_number' => 'fp.credit_number',
            'retirement_age' => 'fp.retirement_age',
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
     * @return FunctionalPosition
     */
    public function show(int $id): FunctionalPosition
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return FunctionalPosition
     */
    public function store(array $data): FunctionalPosition
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return FunctionalPosition
     */
    public function update(array $data, int $id): FunctionalPosition
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
