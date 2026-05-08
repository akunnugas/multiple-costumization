<?php

namespace Modules\Core\Helpers;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use ReflectionProperty;

class ManagementService
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * Init helper.
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Buat instance baru.
     *
     * @param Model $model
     */
    public static function create($model)
    {
        return new static($model);
    }

    /**
     * Menampilkan list data
     *
     * @param int $page
     * @param int $perPage
     * @param array $order
     * @param array $filter
     * @param string $connection
     *
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = [], string $connection = null, $fieldMap = [], $alias = null)
    {
        $table = $this->model->getTable();
        $alias = $alias ?? substr(explode('.', $table)[1], 0, 1);
        // Pengecekan apakah model menggunakan trait softdelete
        $isSoftDelete = in_array(SoftDeletes::class, class_uses($this->model::class), true);

        $sql = "select " . $alias . ".* from " . $table . " " . $alias;
        $defaultFilter = $alias . ".waktu_dihapus is null";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $isSoftDelete ? $defaultFilter : null,
            fieldMap: $fieldMap
        );

        return Pagination::create($sql, $bindings, $page, $perPage, $connection);
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
                $this->model->destroy($ids);
            });
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }

    /**
     * Ambil model dari service
     *
     * @param $service
     *
     * @return mixed
     */
    public static function getModel(mixed $service)
    {
        $service = new ($service);

        try {
            $classReflect = new ReflectionProperty($service, 'model');
        } catch (\ReflectionException $e) {
            return null;
        }

        $classReflect->setAccessible(true);
        $model = $classReflect->getValue($service);
        return $model;
    }
}
