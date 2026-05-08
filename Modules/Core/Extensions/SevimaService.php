<?php

namespace Modules\Core\Extensions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Helpers\ServiceReturn;

class SevimaService
{
    /**
     * Create pagination.
     */
    protected function createPagination(
        $sql,
        $request,
        $defaultOrder = null,
        $defaultFilter = null,
        $defaultFilterBindings = [],
        $filterMap = null,
        $fieldMap = null,
        $bindingUsingName = false,
        $groupBy = null,
    ) {
        // jika ada $defaultFilterBindings dan isi key pertama adalah string, maka set default $bindingUsingName nya true
        if (!empty($defaultFilterBindings) && is_string(array_key_first($defaultFilterBindings))) {
            $bindingUsingName = true;
        }

        $param = [
            'query' => $sql,
            'bindings' => $defaultFilterBindings,
            'order' => $request->order,
            'filter' => $request->filter,
            'defaultOrder' => $defaultOrder,
            'defaultFilter' => $defaultFilter,
            'filterMap' => $filterMap,
            'fieldMap' => $fieldMap,
            'groupBy' => $groupBy,
            'bindingUsingName' => $bindingUsingName,
        ];

        [$sql, $bindings] = Pagination::buildQuery(...$param);

        return Pagination::create($sql, $bindings, $request->page, $request->perPage, bindingUsingName: $bindingUsingName);
    }

    /**
     * Save model.
     */
    protected function saveModel($model)
    {
        return static::tryCall(function () use ($model) {
            return DB::transaction(function () use ($model) {
                $this->saveByModel($model);

                return $model;
            });
        });
    }

    /**
     * Update model.
     */
    protected function updateModel($model, $data)
    {
        return static::tryCall(function () use ($model, $data) {
            return DB::transaction(function () use ($model, $data) {
                $this->throwModelError($model);

                $model->fill($this->prepareData($data));
                $this->saveByModel($model);

                return $model;
            });
        });
    }

    /**
     * Delete model.
     */
    protected function deleteCallback($ids, $findModelCallback, $forceDelete = true)
    {
        return $this->tryCall(function () use ($ids, $findModelCallback, $forceDelete) {
            DB::transaction(function () use ($ids, $findModelCallback, $forceDelete) {
                foreach ($ids as $id) {
                    $model = $findModelCallback($id);

                    $this->throwModelError($model);
                    $this->deleteByModel($model, $forceDelete);
                }
            });
        });
    }

    /**
     * Save model.
     */
    protected function saveByModel($model)
    {
        return $model->save();
    }

    /**
     * Delete (one) model.
     */
    protected function deleteByModel($model, $forceDelete = true)
    {
        if ($forceDelete) {
            $model->forceDelete();
        } else {
            $model->delete();
        }
    }

    /**
     * Check model.
     */
    protected function showModel($model)
    {
        $error = $this->checkModel($model);
        if (!empty($error)) {
            return $error;
        }

        return ServiceReturn::value($model);
    }

    /**
     * Try catch.
     * @return array<string, mixed>
     */
    protected function tryCall($callable): array
    {
        try {
            return ServiceReturn::value($callable());
        } catch (Exception $e) {
            return ServiceReturn::error(exception: $e);
        }
    }

    /**
     * Throw error exception for model
     */
    protected function throwModelError($model)
    {
        $error = $this->checkModel($model);
        if (empty($error)) {
            return;
        }

        $exception = ServiceReturn::getError($error, 'exception');
        if (!empty($exception)) {
            throw $exception;
        }

        ServiceReturn::throwError($error);
    }

    /**
     * Check model value.
     * @return mixed
     */
    protected function checkModel($model)
    {
        if (empty($model)) {
            return ServiceReturn::error(exception: new ModelNotFoundException);
        }
        if (ServiceReturn::isError($model)) {
            return $model;
        }

        return null;
    }

    /**
     * Try catch.
     * @return array<string, mixed>
     */
    protected function prepareData($data): array
    {
        foreach ($data as &$value) {
            if (!is_string($value)) {
                continue;
            }

            $value = trim($value);
            if ($value === '') {
                $value = null;
            }
        }

        return $data;
    }
}
