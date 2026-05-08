<?php

namespace Modules\Gate\Services;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\UnitKerja;
use Modules\Gate\Models\Role;
use Modules\Gate\Models\User;
use Modules\Gate\Models\UserRole;

class RoleManagementService
{
    public $user;

    /**
     * Konstruktor.
     */
    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Display a listing of the resource.
     * @param int $page
     * @param int $perPage
     * @param array $order
     * @param array $filter
     * @return \Modules\Core\Helpers\Pagination
     */
    public function index($page = null, $perPage = null, $order = null, $filter = null)
    {
        $sql = "select id, kode_role, nama_role, apakah_statis, apakah_statis is true as _readonly
                from gate.role";
        $defaultFilter = "waktu_dihapus is null";

        [$sql, $bindings] = Pagination::buildQuery(
            $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Store a newly created resource in storage.
     * @param array $data
     * @return mixed
     */
    public function store($data)
    {
        try {
            $model = new Role($data);
            $model->is_static = false;

            return $model->save();
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update($data, $id)
    {
        try {
            $model = $this->findEditable($id);
            if (Error::isError($model)) {
                return $model;
            }

            $model->is_static = false;
            $model->update($data);

            return $model;
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return mixed
     */
    public function destroy($id)
    {
        try {
            $model = $this->findEditable($id);
            if (Error::isError($model)) {
                return $model;
            }

            $model->delete();
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }

    /**
     * Remove some resources from storage.
     * @param array $ids
     * @return mixed
     */
    public function destroySome($ids)
    {
        try {
            DB::transaction(function () use ($ids) {
                foreach ($ids as $id) {
                    $model = $this->findEditable($id);
                    if (Error::isError($model)) {
                        throw new Exception;
                    }

                    $model->delete();
                }
            });
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }

    /**
     * Update the specified resource in storage.
     * @param int $id
     * @return mixed
     */
    private function findEditable($id)
    {
        $model = Role::findOrFail($id);
        if ($model->is_static) {
            return new Error;
        }

        return $model;
    }
}
