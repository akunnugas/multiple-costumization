<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Facades\DB;
use Livewire\Wireable;

class Pagination implements Wireable
{
    /**
     * Konstruktor (menggunakan promotion).
     */
    public function __construct(
        public $count, // jumlah item pada halaman sekarang
        public $currentPage, // halaman sekarang
        public $firstItem, // nomor pada item pertama
        public $hasPages, // apakah ada lebih dari satu halaman
        public $hasMorePages, // apakah masih ada halaman selanjutnya
        public $items, // item pada halaman sekarang
        public $lastItem, // nomor pada item terakhir
        public $lastPage, // halaman terakhir
        public $onFirstPage, // apakah pada halaman pertama
        public $perPage, // jumlah item per halaman
        public $total // jumlah semua item
    ) {
    }

    /**
     * Select dari database.
     *
     * @param string $query
     * @param array $bindings
     * @param int $page
     * @param int $perPage
     * @param string $connection
     * @return static
     */
    public static function create($query, $bindings = [], $page = null, $perPage = null, $connection = null, $bindingUsingName = false)
    {
        // cek keseluruhan data
        $sql = "select count(*) as total from ($query) a";
        $total = DB::connection($connection)->select($sql, $bindings)[0]->total ?? false;

        if ($total === false) {
            return new Error();
        }

        // default data
        $page ??= 1;
        $perPage ??= 10;

        $perPages = static::showPerPage();
        if (!in_array($perPage, $perPages)) {
            $perPage = $perPages[0];
        }

        $lastPage = empty($total) ? 1 : (int)ceil($total / $perPage);

        // cek batas halaman
        if (!empty($total) && $page < 1) {
            $page = 1;
        }
        if (!empty($total) && $page > $lastPage) {
            $page = $lastPage;
        }

        if (!empty($total)) {
            $offset = ($page - 1) * $perPage;

            $param = $bindings;
            if ($bindingUsingName) {
                $param['__limit'] = $perPage;
                $param['__offset'] = $offset;

                $sql = $query . " limit :__limit offset :__offset";
            } else {
                $param[] = $perPage;
                $param[] = $offset;

                $sql = $query . " limit ? offset ?";
            }

            $items = DB::connection($connection)->select($sql, $param);
            $items = json_decode(json_encode($items), true);
        }

        if (empty($items)) {
            $items = [];
            $count = 0;
            $firstItem = 0;
            $lastItem = 0;
        } else {
            $count = count($items);
            $firstItem = $offset + 1;
            $lastItem = $offset + $count;
        }

        return new static(
            count: $count,
            currentPage: $page,
            firstItem: $firstItem,
            hasPages: $lastPage > 1,
            hasMorePages: $lastPage > $page,
            items: $items,
            lastItem: $lastItem,
            lastPage: $lastPage,
            onFirstPage: $page == 1,
            perPage: $perPage,
            total: $total
        );
    }

    /**
     * Membuat query dengan tambahan order dan filter.
     *
     * @param string $query tidak ada where dan order by
     * @param array $bindings
     * @param mixed $order
     * @param mixed $filter
     * @param mixed $defaultOrder
     * @param mixed $defaultFilter
     * @param array $fieldMap
     * @param array $filterMap
     * @param string $groupBy
     * @return array
     */
    public static function buildQuery(
        $query,
        $bindings = [],
        $order = null,
        $filter = null,
        $defaultOrder = null,
        $defaultFilter = null,
        $fieldMap = [],
        $filterMap = [],
        $groupBy = null,
        $bindingUsingName = false
    ) : array
    {
        [$query, $bindings] = static::buildQueryFilter($query, $bindings, $filter, $defaultFilter, $fieldMap, $filterMap, $bindingUsingName);

        if (!empty($groupBy)) {
            $query .= ' group by ' . $groupBy;
        }

        $query = static::buildQueryOrder($query, $order, $defaultOrder, $fieldMap);

        return [$query, $bindings];
    }

    /**
     * Mendapatkan pilihan perPage.
     *
     * @return array
     */
    public static function showPerPage()
    {
        return [10, 20, 40, 100];
    }

    /**
     * Membuat query dengan tambahan filter.
     *
     * @param string $query tidak ada where dan order by
     * @param array $bindings
     * @param null $filter
     * @param null $defaultFilter
     * @param array $fieldMap
     * @param array $filterMap
     * @param bool $bindingUsingName
     * @return array
     */
    private static function buildQueryFilter(
        $query,
        $bindings = [],
        $filter = null,
        $defaultFilter = null,
        $fieldMap = [],
        $filterMap = [],
        $bindingUsingName = false
    ) : array
    {
        $filter = self::normalizeQueryFilter($filter);
        $defaultFilter = self::normalizeQueryFilter($defaultFilter);

        if (!empty($defaultFilter)) {
            $filter = array_merge($defaultFilter, $filter);
        }

        if (empty($filter)) {
            return [$query, $bindings];
        }

        $filter = array_filter($filter, function ($item) {
            $items = $item['or'] ?? [$item];
            foreach ($items as $subItem) {
                if (isset($subItem['value']) && $subItem['value'] === 'null_filter') {
                    return false;
                }
            }
            return true;
        });

        $filterRaw = [];
        foreach ($filter as $items) {
            $items = $items['or'] ?? [$items];

            $filterOr = [];
            foreach ($items as $item) {
                $value = $item['value'] ?? null;
                $operator = $item['operator'] ?? null;
                $subBindings = $item['bindings'] ?? null;

                if (!empty($filterMap[$item['field']])) {
                    $operator = null;
                    [$filterItem, $subBindings] = $filterMap[$item['field']]($item);
                } else {
                    $filterItem = ($fieldMap[$item['field']] ?? $item['field']);
                }

                if (empty($operator) && !empty($value)) {
                    $operator = '=';
                }

                if (!empty($operator) && !$bindingUsingName) { // jika menggunakan binding biasa (?) tanpa custom nama
                    $filterItem .= " $operator ?";
                    $subBindings = empty($subBindings)
                        ? [$value]
                        : array_slice($subBindings, 0, 1);
                } elseif (!empty($operator) && $bindingUsingName) { // jika menggunakan custom binding name
                    // cek jika field ada . nya (artinya prefix table) ubah jadi __
                    $field = str_replace('.', '__', $item['field']);
                    $placeholder = !empty($value) ? (" :__$field") : '';
                    $filterItem .= " $operator $placeholder"; // ex: field = :__field

                    $subBindings = empty($subBindings)
                        ? ['__' . $field => $value]
                        : array_slice($subBindings, 0, 1);
                }

                // jika operator ada kata like, tambahkan % di depan dan belakang
                if (!empty($operator) && !strcasecmp(substr($operator, -4), 'like')) {
                    if (!$bindingUsingName) {
                        $subBindings[0] = '%' . $subBindings[0] . '%';
                    } else {
                        $field ??= $item['field'];
                        $subBindings['__' . $field] = '%' . $value . '%';
                    }
                }

                $filterOr[] = '(' . $filterItem . ')';
                if (!empty($subBindings)) { // jika ada subBindings, gabungkan
                    $bindings = array_merge($bindings, $subBindings);
                }
            }

            $filterRaw[] = '(' . implode(' or ', $filterOr) . ')';
        }

        $query .= ' where ' . implode(' and ', $filterRaw);

        return [$query, $bindings];
    }

    /**
     * Normalize query filter dari method buildQueryFilter
     *
     * @param array|string|null $filter
     * @return array|array[]
     */
    private static function normalizeQueryFilter($filter = null)
    {
        if (empty($filter)) {
            return [];
        }

        if (!is_array($filter)) {
            $filter = ['field' => $filter];
        }

        if (!is_array(current($filter))) {
            $filter = [$filter];
        }

        return $filter;
    }

    /**
     * Membuat query dengan tambahan order.
     *
     * @param string $query tidak ada order by
     * @param mixed $order
     * @param mixed $defaultOrder
     * @param array $fieldMap
     * @return string
     */
    private static function buildQueryOrder($query, $order = null, $defaultOrder = null, $fieldMap = [])
    {
        $order = !empty($order) ? $order : $defaultOrder;
        if (empty($order)) {
            return $query;
        }

        if (!is_array($order)) {
            $order = ['field' => $order];
        }
        if (!is_array(current($order))) {
            $order = [$order];
        }

        $orderRaw = [];
        foreach ($order as $item) {
            $orderRaw[] = ($fieldMap[$item['field']] ?? $item['field']) . (empty($item['direction']) ? '' : ' ' . $item['direction']);
        }

        $query .= ' order by ' . implode(', ', $orderRaw);

        return $query;
    }

    /**
     * Mengubah ke format data Livewire.
     *
     * @return array
     */
    public function toLivewire()
    {
        return (array)$this;
    }

    /**
     * Membuat object dari format data Livewire.
     *
     * @param array $value
     * @return static
     */
    public static function fromLivewire($value)
    {
        return new static(...$value);
    }
}
