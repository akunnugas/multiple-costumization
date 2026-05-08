<?php

namespace Modules\Core\Extensions;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class SevimaBlueprint extends Blueprint
{
    /**
     * Create log columns on the table.
     *
     * @param bool $isSoftDelete
     */
    public function logs($isSoftDelete = true)
    {
        $this->timestampTz('waktu_dibuat')->nullable();
        $this->unsignedBigInteger('dibuat_oleh')->nullable();
        $this->timestampTz('waktu_diubah')->nullable();
        $this->unsignedBigInteger('diubah_oleh')->nullable();

        if ($isSoftDelete) {
            $this->timestampTz('waktu_dihapus')->nullable();
            $this->unsignedBigInteger('dihapus_oleh')->nullable();
        }
    }

    /**
     * Create foreign key column on the table.
     *
     * @param string|null $model
     * @param string|null $column
     * @param bool|null $nullable
     * @param string|null $foreignName
     * @param string|null $indexName
     */
    public function foreignIdTo($model = null, $column = null, $nullable = false, $foreignName = null, $indexName = null, $table = null)
    {
        $table ??= (new $model)->getTable();
        $tableParts = explode('.', $table);
        $column ??= 'id_' . ($tableParts[1] ?? $tableParts[0]);
        $this->unsignedBigInteger($column)->nullable($nullable);
        $this->foreign($column, $foreignName)->references('id')->on($table);
        $this->index($column, $indexName);
    }

    /**
     * Create unique index for table with soft delete.
     *
     * @param  mixed  $columns
     * @param  bool   $isSoftDelete
     */
    public function uniqueIndex($columns, $isSoftDelete = true, $uniqueIndexName = null)
    {
        $sql = 'CREATE UNIQUE INDEX '. $uniqueIndexName .' ON ' . $this->table . ' (' . implode(', ', (array)$columns) . ')';
        if ($isSoftDelete) {
            $sql .= ' WHERE waktu_dihapus IS NULL';
        }

        DB::statement($sql);
    }
}
