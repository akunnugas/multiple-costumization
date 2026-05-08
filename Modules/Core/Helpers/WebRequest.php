<?php

namespace Modules\Core\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class WebRequest
{
    /**
     * Get data for index.
     * @param mixed $service
     * @param string|null $model
     * @param array $header
     * @param array $filter
     * @param int|null $requestPage
     * @param int|null $requestPerPage
     * @param int|null $requestSort
     * @param int|null $requestSortDesc
     * @param array|null $requestFilter
     * @param string|null $requestSearch
     * @param string|null $customMethod
     * @param bool $isUsingDefaultOrder
     * @return array
     */
    public static function indexData(
        $service,
        string $model = null,
        array $header = [],
        array $filter = [],
        int $requestPage = null,
        int $requestPerPage = null,
        int $requestSort = null,
        int $requestSortDesc = null,
        array $requestFilter = null,
        string $requestSearch = null,
        string $customMethod = null,
        bool $isUsingDefaultOrder = true,
        array $customMethodParams = []
    ) {
        $header = static::buildFields($model, $header, setDefaultIndex: true);

        $indexHeader = static::buildIndexHeader($header);
        $selectFilter = static::buildIndexSelectFilter($filter, $requestFilter);
        $sessionFilter = static::getFilterSession($requestFilter, $selectFilter);

        $order = static::buildIndexOrder($indexHeader, $requestSort, $requestSortDesc, $isUsingDefaultOrder);
        $filter = static::buildIndexFilter($indexHeader, $sessionFilter, $requestSearch, $requestFilter);

        $data = $service->{$customMethod}($requestPage, $requestPerPage, $order ?? [], $filter, ...$customMethodParams);

        return [
            'data' => $data,
            'header' => $indexHeader['items'],
            'filter' => $sessionFilter,
            'order' => $order,
        ];
    }

    /**
     * Get data filter from session
     */
    public static function getFilterSession($requestFilter, $filter)
    {
        $routeName = Route::currentRouteName();

        if (!empty($routeName)) {
            [$module, $resource] = explode('.', $routeName, 3);
            $newFilter = [];

            foreach ($filter as $key => $item) {
                $sessionkey = "filter.{$module}.{$resource}.{$key}";
                $selectedFilter = session()->get($sessionkey);
                if (isset($requestFilter[$key]) && !empty($selectedFilter)) {
                    $newFilter[$key] = $item;
                    $newFilter[$key]['selected'] = empty($item['selected']) || $item['selected'] === '-' ? $selectedFilter : $item['selected'];

                    continue;
                }

                if (!empty($item['selected'])) {
                    session()->put($sessionkey, $item['selected']);
                    $newFilter[$key] = $item;

                    continue;
                }

                $newFilter[$key] = $item;
            }

            $filter = empty($newFilter) ? $filter : $newFilter;
        }

        $filter = array_map(function ($item) {
            if (empty($item['selected'])) {
                $item['selected'] = $item['empty_selected'] ?? null;
            }

            return $item;
        }, $filter);

        return $filter;
    }

    /**
     * Show the form for creating a new resource.
     * @param array $cards
     * @param mixed $model
     * @param string $viewData
     * @return Renderable
     */
    public static function createData(array $cards, mixed $model = [], array $viewData = [])
    {
        $data = static::buildFields($model, $cards);

        return [
            'data' => $data,
            'viewData' => $viewData,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     * @param mixed $service
     * @param string $model
     * @param mixed $id
     * @param array $cards
     * @param string $viewData
     * @return Renderable
     */
    public static function editData($service, string $model = null, mixed $id = null, array $cards = [], array $viewData = [])
    {
        static::validateId($id);

        $data = static::buildFields($model, $cards);
        $record = $service->show($id);
        
        // set record
        foreach ($data as $i => $field) {
            if (!empty($field['items'])) {
                foreach ($field['items'] as $fieldIndex => $field) {
                    $data[$i]['items'][$fieldIndex]['value'] = $record->{$field['field']} ?? null;
                }

                continue;
            }
            $data[$i]['value'] = $record->{$field['field']} ?? null;
        }

        return [
            'data' => $data,
            'viewData' => $viewData,
        ];
    }

    /**
     * Get data for index.
     * @param mixed $service
     * @param string|null $model
     * @param array $header
     * @param array $filter
     * @param int|null $requestSort
     * @param int|null $requestSortDesc
     * @param array|null $requestFilter
     * @param string|null $requestSearch
     * @param string|null $customMethod
     * @param bool $isUsingDefaultOrder
     * @param array $customMethodParams
     * @return array
     */
    public static function exportData(
        $service,
        string $model = null,
        array $header = [],
        array $filter = [],
        int $requestSort = null,
        int $requestSortDesc = null,
        array $requestFilter = null,
        string $requestSearch = null,
        string $customMethod = 'export',
        bool $isUsingDefaultOrder = true,
        array $customMethodParams = []
    ) {
        $header = static::buildFields($model, $header, setDefaultIndex: true);

        $indexHeader = static::buildIndexHeader($header);
        $selectFilter = static::buildIndexSelectFilter($filter, $requestFilter);
        $sessionFilter = static::getFilterSession($requestFilter, $selectFilter);

        $order = static::buildIndexOrder($indexHeader, $requestSort, $requestSortDesc, $isUsingDefaultOrder);
        $filter = static::buildIndexFilter($indexHeader, $sessionFilter, $requestSearch, $requestFilter);
        $data = $service->{$customMethod}($order ?? [], $filter, ...$customMethodParams);

        return [
            'data' => $data,
            'header' => $header,
        ];
    }

    /**
     * Validasi id.
     * @param mixed $id
     * @return bool
     */
    public static function validateId($id)
    {
        if (is_array($id)) {
            $id = array_filter($id, function ($v) {
                return filter_var($v, FILTER_VALIDATE_INT);
            });

            return !empty($id);
        }

        return filter_var($id, FILTER_VALIDATE_INT);
    }

    /**
     * Validasi data.
     * @param array $data
     * @param string $model
     * @param mixed $id
     * @param array $attributes
     */
    public static function validateData($data, $model, $id = null, $attributes = null, $messages = [])
    {
        if (!empty($id)) {
            $data['id'] = $id;
        }

        // rule validasi
        $rules = $model::rules($data);

        foreach ($model::RULES as $key => $item) {
            if (!array_key_exists($key, $data)) {
                unset($rules[$key]);
                continue;
            }

            // Cek jika type date
            if (isset($item['type']) && $item['type'] == 'date' && !empty($data[$key])) {
                $matchDateFormat = Carbon::hasFormat($data[$key], 'd/m/Y');

                $data[$key] = $matchDateFormat ? Carbon::createFromFormat('d/m/Y', $data[$key])->format('Y-m-d') : $data[$key];
            }

            // Validasi jika file
            if (isset($item['file_type'])) {
                $rules[$key][] = 'mimes:' . implode(',', $item['file_type']);
            }
        }

        // mulai validasi
        if (empty($attributes)) {
            $attributes = Page::translateResource(field: false);
        }

        if (!empty($messages)) {
            $modelMessage = defined($model . '::MESSAGES') ? $model::MESSAGES : [];
            $messages = array_merge($modelMessage, $messages);
        }

        Validator::make($data, $rules, $messages, $attributes)->validate();
    }

    /**
     * Custom Validation.
     */
    public static function customValidate($data, $column)
    {
        $rules = [];
        foreach ($column as $key => $item) {
            $addValidation = [];
            if (!empty($item['required'])) {
                $addValidation[] = 'required';
            }

            $rules[$key] = $addValidation;
        }

        Validator::make($data, $rules)->validate();
    }

    /**
     * Sanitasi input data terlebih dahulu untuk menghindari XSS
     * @param array $data
     * @param array $model
     *
     * @return array
     */
    public static function sanitizeXSS($data, $model)
    {
        if (!is_array($model)) {
            $model = [$model];
        }

        foreach ($model as $key => $item) {
            $rules = $item::RULES;
            foreach ($rules as $field => $rule) {
                $controlType = $rule['control'] ?? null;
                $isFileType = isset($rule['file_type']);

                // Jika wysiwyg, maka remove tag script
                if ($controlType == 'wysiwyg' && isset($data[$field])) {
                    $data[$field] = strip_tags($data[$field], '<p><a><b><i><u><strong><em><br><ul><ol><li><h1><h2><h3><h4><h5><h6><img><table><tr><td><th><tbody><thead><tfoot><caption><div><span><hr><pre><code><blockquote><cite><small><sub><sup><del><ins><mark><abbr><acronym><address><dfn><kbd><samp><var><ul><ol><li><dl><dt><dd>');
                }

                // Jika bukan wysiwyg dan bukan file, maka sanitasi
                if ((isset($data[$field]) && is_string($data[$field])) && ($controlType != 'wysiwyg') && !$isFileType) {
                    $data[$field] = htmlentities(trim($data[$field]));
                }

                // Cek jika type date
                if (isset($rule['type']) && $rule['type'] == 'date' && !empty($data[$field])) {
                    $matchDateFormat = Carbon::hasFormat($data[$field], 'd/m/Y');
                    $data[$field] = $matchDateFormat ? Carbon::createFromFormat('d/m/Y', $data[$field])->format('Y-m-d') : $data[$field];
                }
            }
        }

        return $data;
    }

    /**
     * Build rules.
     * @param string $model
     * @param array $fields
     * @param bool $setDefaultIndex
     * @param bool $flatFields
     * @return array
     */
    public static function buildFields(string $model = null, array $fields = [], bool $setDefaultIndex = false, bool $flattenFields = false)
    {
        if (!empty($model) && defined("$model::RULES")) {
            $rules = $model::RULES;
        }

        if (!empty($rules) && empty($fields)) {
            $fields = [];
            foreach ($rules as $field => $rule) {
                $fields[] = ['field' => $field];
            }
        }

        if (!empty($rules)) {
            foreach ($fields as $i => $field) {
                if (isset($field['separator']) && $field['separator']) {
                    continue;
                }

                if (!empty($field['items'])) {
                    foreach ($field['items'] as $j => $item) {
                        if (isset($item['separator']) && $item['separator']) {
                            continue;
                        }

                        $fields[$i]['items'][$j] += $rules[$item['field']] ?? [];

                        // NOTE: Jika attribute control tidak di-set dan memiliki options, maka set control menjadi select
                        if (isset($fields[$i]['items'][$j]['options']) && !isset($fields[$i]['items'][$j]['control'])) {
                            $fields[$i]['items'][$j]['control'] = 'select';
                        }
                    }
                } else {
                    $fields[$i] += $rules[$field['field']] ?? [];

                    // NOTE: Jika attribute control tidak di-set dan memiliki options, maka set control menjadi select
                    if (isset($fields[$i]['options']) && !isset($fields[$i]['control'])) {
                        $fields[$i]['control'] = 'select';
                    }
                }
            }
        }

        if (empty($setDefaultIndex) && empty($flattenFields)) {
            return $fields;
        }

        $flatFields = [];
        foreach ($fields as $field) {
            if (!empty($field['items'])) {
                foreach ($field['items'] as $item) {
                    $flatFields[] = $item;
                }
            } else {
                $flatFields[] = $field;
            }
        }

        $fields = $flatFields;
        if (!empty($flattenFields)) {
            return $fields;
        }

        // set parameter default untuk index
        $definerIndex = $orderIndex = $nameIndex = null;
        foreach ($fields as $i => $field) {
            if (!empty($field['definer'])) {
                $definerIndex = $i;
            }
            if (!empty($field['order'])) {
                $orderIndex = $i;
            }
            if ($field['field'] == 'name') {
                $nameIndex = $i;
            }
        }

        if (!isset($definerIndex)) {
            $definerIndex = $nameIndex ?? 0;
            $fields[$definerIndex]['definer'] = true;
        }
        if (!isset($orderIndex)) {
            $fields[0]['order'] = true;
        }

        return $fields;
    }

    /**
     * Get filter by request and header.
     * @param array $header sudah di-build
     * @param array $selectFilter sudah di-build
     * @param string|null $requestSearch
     * @param array|null $requestFilter
     * @return array
     */
    private static function buildIndexFilter(array $header, array $selectFilter, string $requestSearch = null, array $requestFilter = null)
    {
        $filter = [];

        // search
        if (!empty($requestSearch)) {
            $search = [];
            foreach ($header['searchable_fields'] as $field) {
                $search[] = ['field' => $field, 'operator' => 'ilike', 'value' => $requestSearch];
            }

            $filter[] = ['or' => $search];
        }

        // filter
        foreach ($selectFilter as $key => $item) {
            if (empty($item['selected']) || $item['selected'] == '-') {
                continue;
            }

            $filter[] = ['field' => $key, 'value' => $item['selected']];
        }

        // menggabungkan filter dari request
        if (!empty($requestFilter)) {
            foreach ($requestFilter as $key => $value) {
                // jika $key sudah ada di selectFilter, maka skip
                if (empty($value) || $value == '-' || is_array($value)) {
                    continue;
                }

                $filter[] = ['field' => $key, 'value' => $value];
            }
        }

        return $filter;
    }

    /**
     * Build header for index.
     * @param array $header
     * @return array
     */
    private static function buildIndexHeader(array $header)
    {
        $indexHeader = [
            'items' => [],
            'searchable_fields' => [],
            'order' => ['field' => 'id', 'desc' => true],
        ];

        $sortIndex = 0;
        foreach ($header as $i => $item) {
            // custom header otomatis tidak bisa di sort atau search
            if ($item['field'] === 'action') {
                $indexHeader['items'][$i] = $item;
                continue;
            }

            // default bisa di-sort
            if (!isset($item['sortable'])) {
                $item['sortable'] = true;
            }

            // beri index sort
            $item['sort_index'] = $item['sortable'] ? ++$sortIndex : null;

            // default sort
            if (!empty($item['order'])) {
                $indexHeader['order'] = [
                    'field' => $item['field'],
                    'desc' => ($item['order'] === 'desc'),
                ];
            }

            // default bisa di-search
            if (!isset($item['searchable'])) {
                $item['searchable'] = true;
            }

            // field yang bisa di-search
            if (!empty($item['searchable'])) {
                $field = $item['field'];
                if (!empty($item['casting'])) {
                    $field .= '::text';
                }

                $indexHeader['searchable_fields'][] = $field;
            }

            $indexHeader['items'][$i] = $item;
        }

        return $indexHeader;
    }

    /**
     * Get order by request and header.
     *
     * @param array $header sudah di-build
     * @param int|null $requestSort
     * @param int|null $requestSortDesc
     * @param bool $isUsingDefaultOrder
     * @return array
     */
    private static function buildIndexOrder(
        array $header,
        int $requestSort = null,
        int $requestSortDesc = null,
        bool $isUsingDefaultOrder = true
    ) {
        if (!empty($requestSort)) {
            $order = ['no' => $requestSort, 'desc' => $requestSortDesc];
        } elseif ($isUsingDefaultOrder) {
            $order = $header['order'];
        }

        if (empty($order)) {
            return null;
        }

        // lengkapi field dan no
        foreach ($header['items'] as $item) {
            if (empty($order['field']) && !empty($order['no']) && $item['sort_index'] == $order['no']) {
                $order['field'] = $item['field'];
            }
            if (empty($order['no']) && !empty($order['field']) && $item['field'] == $order['field']) {
                $order['no'] = $item['sort_index'];
            }
        }

        if (empty($order['field'])) {
            return null;
        }

        if (!empty($order['desc'])) {
            $order['direction'] = 'desc';
        }

        return $order;
    }

    /**
     * Build select filter for index.
     * @param array $selectFilter
     * @param array|null $requestFilter
     * @return array
     */
    private static function buildIndexSelectFilter(array $selectFilter, array $requestFilter = null)
    {
        foreach ($selectFilter as $key => $item) {
            // beri selected
            $hasFilter = !empty($requestFilter) && array_key_exists($key, $requestFilter);
            $selected = $requestFilter[$key] ?? null;

            // Jika tidak ada filter, maka selected adalah default
            if (!$hasFilter) {
                $selected = $selectFilter[$key]['selected'] ?? null;
            }

            if (empty($selected) && !empty($item['options']['-'])) {
                $selected = '-';
            }

            $selectFilter[$key]['selected'] = $selected;
        }

        return $selectFilter;
    }

    /**
     * Menghapus query parameter dari url.
     *
     * @param $url
     * @param $query
     * @return string
     */
    public static function removeQueryParam($url, $query)
    {
        $url = preg_replace('/&?' . $query . '=[^&]*/', '', $url);
        $url = preg_replace('/\?$/', '', $url);

        return $url;
    }
}
