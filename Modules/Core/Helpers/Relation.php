<?php

namespace Modules\Core\Helpers;

use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

class Relation
{
    /**
     * Cek apakah model memiliki relasi yang memiliki data.
     *
     * @param Model $model
     * @param array|null $relations spesifik relasi yang ingin dicek.
     * @return array|null
     */
    public static function hasRelationsData(Model $model, array $relations = null): ?array
    {
        $relations ??= self::getModelRelations($model);

        foreach ($relations as $relation) {
            if ($model->{$relation}()->exists()) {
                return [
                    'status' => true,
                    'relation' => $relation,
                ];
            }
        }

        return null;
    }

    /**
     * Mendapatkan daftar relasi dari model.
     *
     * @param Model $model
     * @return array
     */
    private static function getModelRelations(Model $model): array
    {
        $relations = [];
        $reflection = new ReflectionClass($model);

        foreach ($reflection->getMethods() as $method) {
            if ($method->class == get_class($model) && $method->getNumberOfParameters() == 0) {
                $returnType = $method->getReturnType();
                if ($returnType && is_subclass_of($returnType->getName(), \Illuminate\Database\Eloquent\Relations\Relation::class)) {
                    $relations[] = $method->getName();
                }
            }
        }

        return $relations;
    }
}
