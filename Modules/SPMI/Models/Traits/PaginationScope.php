<?php

namespace Modules\SPMI\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait PaginationScope
{
    /**
     * Scope a query to filter.
     *
     * @param Builder $query
     * @param array $filter
     * @param array $map
     */
    public function scopeFilterPage(Builder $query, array $filter, array $map = [])
    {
        if (empty($filter)) {
            return;
        }

        foreach ($filter as $v) {
            $this->queryFilter($query, $v['key'], $v['value'], $v['operator'] ?? null, $map);
        }
    }

    /**
     * Scope a query to join table.
     *
     * @param Builder $query
     * @param string $table
     * @param string|null $withTable
     */
    public function scopeJoinTable(Builder $query, string $table, string $withTable = null): void
    {
        $this->queryJoin($query, $table, $withTable);
    }

    /**
     * Scope a query to left join table.
     *
     * @param Builder $query
     * @param string $table
     * @param string|null $withTable
     */
    public function scopeLeftJoinTable(Builder $query, string $table, string $withTable = null): void
    {
        $this->queryJoin($query, $table, $withTable, 'leftJoin');
    }

    /**
     * Scope a query to select with map.
     *
     * @param Builder $query
     * @param array $fields
     * @param array $map
     */
    public function scopeSelectMap(Builder $query, array $fields, array $map)
    {
        $select = [];
        foreach ($fields as $field) {
            $select[] = $this->getTable() . '.' . $field;
        }

        foreach ($map as $alias => $field) {
            $select[] = $field . ' as ' . $alias;
        }

        $query->selectRaw(implode(', ', $select));
    }

    /**
     * Scope a query to sort.
     *
     * @param Builder $query
     * @param array $sort
     * @param array $map
     */
    public function scopeSortPage(Builder $query, array $sort, array $map = [])
    {
        if (empty($sort)) {
            if (defined('static::SORT_DEFAULT')) {
                $query->orderByRaw(static::SORT_DEFAULT);
            }

            return;
        }

        foreach ($sort as $v) {
            $this->querySort($query, $v['key'], $v['direction'] ?? null, $map);
        }
    }

    /**
     * Build query for filter.
     *
     * @param Builder $query
     * @param mixed $column
     * @param mixed $value
     * @param mixed $operator
     * @param array $map
     */
    protected function queryFilter(
        Builder $query,
        mixed $column,
        mixed $value,
        mixed $operator = null,
        array $map = []
    ) {
        if (empty($operator)) {
            $operator = 'ilike';
            $value = '%' . $value . '%';
        }

        if (is_array($column)) {
            $this->queryFilterArray($query, $column, $value, $operator, $map);
        } else {
            $query->where(function ($query) use ($column, $value, $operator, $map) {
                $this->queryFilterWhere($query, $column, $value, $operator, $map);
            });
        }
    }

    /**
     * Build query for multiple filter.
     *
     * @param Builder $query
     * @param array $column
     * @param mixed $value
     * @param mixed $operator
     * @param array $map
     */
    protected function queryFilterArray(
        Builder $query,
        array $column,
        mixed $value,
        mixed $operator = null,
        array $map = []
    ) {
        $query->where(function ($query) use ($column, $value, $operator, $map) {
            $query->where(function ($query) use ($column, $value, $operator, $map) {
                $this->queryFilterWhere(
                    $query,
                    $column[0],
                    is_array($value) ? $value[0] : $value,
                    is_array($operator) ? $operator[0] : $operator,
                    $map
                );
            });

            for ($i = 1; $i < count($column); $i++) {
                $query->orWhere(function ($query) use ($column, $value, $operator, $map, $i) {
                    $this->queryFilterWhere(
                        $query,
                        $column[$i],
                        is_array($value) ? $value[$i] : $value,
                        is_array($operator) ? $operator[$i] : $operator,
                        $map
                    );
                });
            }
        });
    }

    /**
     * Build query for filter condition.
     *
     * @param Builder $query
     * @param string $column
     * @param mixed $value
     * @param mixed $operator
     * @param array $map
     * @param bool $or
     */
    protected function queryFilterWhere(
        Builder $query,
        string $column,
        mixed $value,
        mixed $operator = null,
        array $map = []
    ) {
        // bisa di-extend untuk custom
        $query->where($this->defineColumn($column, $map), $operator, $value);
    }

    /**
     * Build query for join table.
     *
     * @param Builder $query
     * @param string $table
     * @param string|null $withTable
     * @param string $command
     */
    protected function queryJoin(Builder $query, string $table, string $withTable = null, string $command = 'join')
    {
        $tableParts = explode(' as ', $table);
        $asTable = $tableParts[1] ?? $tableParts[0];
        $withTable ??= $this->getTable();

        $query->$command(
            $table,
            $asTable . '.id',
            '=',
            $withTable . '.' . Str::singular($asTable) . '_id'
        );
    }

    /**
     * Build query for sort.
     *
     * @param mixed $query
     * @param string $column
     * @param string|null $direction
     * @param array $map
     */
    protected function querySort(Builder $query, string $column, string $direction = null, array $map = [])
    {
        $query->orderByRaw($this->defineColumn($column, $map) . (empty($direction) ? '' : ' ' . $direction));
    }

    /**
     * Get column definition.
     *
     * @param string $column
     * @param array $map
     *
     * @return string
     */
    protected function defineColumn(string $column, array $map = []): string
    {
        // cek map
        if (!empty($map[$column])) {
            return $map[$column];
        }

        return $this->getTable() . '.' . $column;
    }
}
