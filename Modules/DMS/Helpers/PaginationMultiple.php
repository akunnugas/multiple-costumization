<?php

namespace Modules\DMS\Helpers;

use Illuminate\Support\Facades\DB;
use Livewire\Wireable;
use Modules\Core\Helpers\Pagination;

class PaginationMultiple implements Wireable
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
     * @param array[] $queriesAndBindings
     * @param int $page
     * @param int $perPage
     * @param string $connection
     * @return static
     */
    public static function create($queriesAndBindings, $page = null, $perPage = null, $connection = null)
    {
        $totalPerQuery = [];
        $total = 0;

        foreach ($queriesAndBindings as $queryAndBindings) {
            $query = $queryAndBindings[0];
            $bindings = $queryAndBindings[1] ?? [];

            // cek keseluruhan data
            $sql = "select count(*) as total from ($query) a";
            $totalLocal = DB::connection($connection)->select($sql, $bindings)[0]->total ?? false;

            if ($totalLocal === false) {
                return new Error();
            }

            $totalPerQuery[] = $totalLocal;
            $total += $totalLocal;
        }

        // default data
        $page ??= 1;
        $perPage ??= 10;

        $perPages = Pagination::showPerPage();
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
            $items = [];
            $offset = ($page - 1) * $perPage;
            $localOffset = $offset;

            foreach ($queriesAndBindings as $index => $queryAndBindings) {
                if ($totalPerQuery[$index] == 0) continue;
                if ($localOffset > $totalPerQuery[$index]) {
                    $localOffset -= $totalPerQuery[$index];
                    continue;
                }

                $query = $queryAndBindings[0];
                $bindings = $queryAndBindings[1] ?? [];

                $perPageLocal = $perPage - count($items);

                if ($perPageLocal <= 0) {
                    break;
                }

                $param = $bindings;
                $param[] = $perPageLocal;
                $param[] = $localOffset;

                $sql = $query." limit ? offset ?";
                $itemsLocal = DB::connection($connection)->select($sql, $param);
                $itemsLocal = json_decode(json_encode($itemsLocal), true);
                $items = array_merge($items, $itemsLocal);

                // mulai dari offset 0 untuk query selanjutnya
                if (count($items) < $perPage) {
                    $localOffset = 0;
                }
            }
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
     * @param string[] $query tidak ada where dan order by
     * @param array[] $bindingss
     * @param mixed[] $order
     * @param mixed[] $filter
     * @param mixed[] $defaultOrder
     * @param mixed[] $defaultFilter
     * @param array[] $fieldMap
     * @param array[] $filterMap
     * @param string[] $groupBy
     * @return array[]
     */
    public static function buildQuery($queries, $bindingss = [], $orders = [], $filters = [], $defaultOrders = [], $defaultFilters = [], $fieldMaps = [], $filterMaps = [], $groupBys = []) {
        $all = array_map(function ($query, $bindings, $order, $filter, $defaultOrder, $defaultFilter, $fieldMap, $filterMap, $groupBy) {
            return Pagination::buildQuery($query, $bindings ?? [], $order, $filter, $defaultOrder, $defaultFilter, $fieldMap, $filterMap, $groupBy);
        }, $queries, $bindingss, $orders, $filters, $defaultOrders, $defaultFilters, $fieldMaps, $filterMaps, $groupBys);

        return $all;
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
