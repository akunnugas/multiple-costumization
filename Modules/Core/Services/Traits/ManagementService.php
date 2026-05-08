<?php

namespace Modules\Core\Services\Traits;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Helpers\Pagination;

trait ManagementService
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var string
     */
    protected $sqlIndex;

    /**
     * Menampilkan list data
     *
     * @param int $limit
     * @param int $page
     * @param array $filters
     *
     * @return mixed
     */
    public function index(int $limit = 10, int $page = 1, array $filters = []): mixed
    {
        $table = $this->model->getTable();
        $isSoftDelete = SevimaSchema::hasColumn($table, 'waktu_dihapus');
        $alias = substr(explode('.', $table)[1], 0, 1);
        $hideSoftDelete = $isSoftDelete ? $alias . ".waktu_dihapus is null" : "";

        // Jika ada filter
        $sqlFilter = '';
        $bindings = [];
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                $key = $filter['key'];
                $value = $filter['val'];
                $operator = $filter['operator'] ?? '=';
                if (is_array($key)) {
                    foreach ($key as $i => $k) {
                        if ($i == 0) {
                            $sqlFilter .= ' and ' . "($alias.$k ilike ?";
                        } else {
                            $sqlFilter .= ' or ' . "$alias.$k ilike ?";
                        }
                        $bindings[] = $value;
                    }
                    $sqlFilter .= ')';
                } else {
                    $sqlFilter .= ' and ' . "$alias.$key $operator ?";
                    $bindings[] = $value;
                }
            }
        }

        $sql = $this->sqlIndex ?? "select " . $alias . ".* from " . $table . " " . $alias . " where " . $hideSoftDelete . $sqlFilter;

        return Pagination::create(query: $sql, bindings: $bindings, page: $page, perPage: $limit);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Model
     */
    public function show(int $id): Model
    {
        $model = $this->model->findOrFail($id);

        return $model;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return Model
     */
    public function store(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return Model
     */
    public function update(array $data, int $id): Model
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
