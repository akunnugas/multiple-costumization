<?php

namespace Modules\Core\Helpers;

class PaginationRequest
{
    public $order;
    public $filter = [];

    public $header = [];
    public $filterMain = [];
    public $filterAdvanced = [];

    /**
     * Konstruktor (menggunakan promotion).
     */
    public function __construct(
        $header,
        public $page = null, // halaman sekarang
        public $perPage = null, // jumlah item per halaman
    ) {
        $this->assignHeader($header);
    }

    /**
     * Set filter main.
     */
    public function assignFilter(array $filterMain, array $filter, string $type = 'filterMain')
    {
        $nav = Navigation::getInstance();

        foreach ($filterMain as $name => $item) {
            // selected
            $filter[$name] ??= $item['selected'] ?? null;
            $item['selected'] = $filter[$name];

            // placeholder
            $item['label'] ??= __($nav->module . '::' . $name . '.main');

            // dynamic component
            if (!empty($item['component'])) {
                $item['component'] = Field::getDynamicOptions($item['component'], $nav->module, $name);
            }

            // set definisi filter
            $this->{$type}[$name] = $item;

            // cek skip
            if (!empty($item['skip'])) {
                continue;
            }

            // tambah filter
            $options = $item['options'] ?? [];
            $isEmpty = $item['isEmpty'] ?? true;

            $filterValue = $filter[$name] ?? null;
            if (empty($isEmpty) && empty($filterValue) && !empty($options)) {
                $filterValue = key($options);
            }

            if (!empty($filterValue)) {
                // Jika options array maka dianggap tree
                if (!empty($options) && is_array(current($options)) && array_key_exists('info_level', current($options))) {
                    $treeFilter = $this->processTree($filterValue, $options, $name);
                } else {
                    $this->filter[] = ['field' => $name, 'value' => $filterValue];
                }

                if (!empty($treeFilter) && !empty($treeFilter['value'])) {
                    $this->filter[] = $treeFilter;
                }
            }
        }
    }

    protected function processTree($value, $options, $name)
    {
        if (!is_array($value)) {
            $values = [$value];
        } else {
            $values = $value;
        }

        $values = array_filter($values, function ($item) use ($options) {
            $searchIndex = array_search($item, array_column($options, 'id'));
            return !empty($options[$searchIndex]) && $options[$searchIndex]['info_level'] > 0;
        });

        sort($values);

        $recentLevel = null;
        $isNext = false;
        $selectedValues = [];

        foreach ($options as $option) {
            if ($recentLevel !== null && $option['info_level'] > $recentLevel && $isNext) {
                $selectedValues[] = $option['id'];
            }

            if ($recentLevel !== null && $option['info_level'] <= $recentLevel) {
                $isNext = false;
            }

            if (in_array($option['id'], $values)) {
                $recentLevel = $option['info_level'];
                $isNext = true;

                $selectedValues[] = $option['id'];
            }
        }

        $selectedValues = array_unique($selectedValues);

        return ['field' => $name, 'operator' => 'in', 'value' => $selectedValues];
    }

    /**
     * Tambah filter.
     */
    public function addFilter(string $field, mixed $value)
    {
        $this->filter[] = ['field' => $field, 'value' => $value];
    }

    /**
     * Set filter search.
     */
    public function assignSearch(string $search = null)
    {
        if (empty($search)) {
            return;
        }

        $filter = [];
        foreach ($this->header as $item) {
            if (empty($item['searchable'])) {
                continue;
            }

            $fields = [$item['field']];

            if (is_array($item['searchable'])) {
                $fields = $item['searchable'];
            }

            foreach ($fields as $field) {
                if (!empty($item['casting'])) {
                    $field .= '::text';
                }

                $filter[] = ['field' => $field, 'operator' => 'ilike', 'value' => $search];
            }
        }

        $this->filter[] = ['or' => $filter];
    }

    /**
     * Set order.
     */
    public function assignSort(int $sort = null, int $sortDesc = null)
    {
        if (empty($sort)) {
            return;
        }

        foreach ($this->header as $i => $item) {
            if ($item['sort_index'] === $sort) {
                $this->header[$i]['sort_desc'] = !empty($sortDesc);

                $order = ['field' => $item['field']];
                if (!empty($sortDesc)) {
                    $order['direction'] = 'desc';
                }

                $this->order = $order;
                return;
            }
        }
    }

    /**
     * Set header pagination.
     */
    protected function assignHeader(array $header)
    {
        // lengkapi item pagination
        $sortIndex = 0;
        foreach ($header as $i => $item) {
            // default bisa di-search
            if (!isset($item['searchable'])) {
                $item['searchable'] = true;
            }

            // default bisa di-sort
            if (!isset($item['sortable'])) {
                $item['sortable'] = true;
            }

            // beri index sort
            $item['sort_index'] = $item['sortable'] ? ++$sortIndex : null;

            $this->header[$i] = $item;
        }
    }
}
