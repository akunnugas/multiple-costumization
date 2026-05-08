<?php

namespace Modules\Core\Extensions;

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SevimaSchema extends Schema
{
    /**
     * Get a schema builder instance for a connection.
     *
     * @param  string|null  $name
     * @return Builder
     */
    public static function connection($name = null)
    {
        $builder = parent::connection($name);

        $builder->blueprintResolver(static function ($table, $callback) {
            return new SevimaBlueprint($table, $callback);
        });

        return $builder;
    }

    /**
     * Create unique index for table with soft delete.
     *
     * @param  mixed  $column
     * @param  bool   $isSoftDelete
     */
    public static function createUniqueIndex($column, $isSoftDelete = true)
    {
        $sql = 'CREATE UNIQUE INDEX ON ' . $column;
        if ($isSoftDelete) {
            $sql .= ' WHERE waktu_dihapus IS NULL';
        }

        DB::statement($sql);
    }
}
