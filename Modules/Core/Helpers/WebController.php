<?php

namespace Modules\Core\Helpers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;

class WebController
{
    /**
     * Display a listing of the resource.
     * @param mixed $service
     * @param Request $request
     * @param array $header
     * @param array $filter
     * @param array $viewData
     * @param string|null $model
     * @param string $customMethod
     * @param bool $isReference
     * @param bool $isUsingDefaultOrder
     * @param string|null $viewBlade
     * @param array $customMethodParams
     * @return Renderable
     */
    public static function index(
        $service,
        Request $request,
        array $header = [],
        array $filter = [],
        array $viewData = [],
        string $model = null,
        string $customMethod = 'index',
        bool $isReference = false,
        bool $isUsingDefaultOrder = true,
        string $viewBlade = null,
        array $customMethodParams = [],
    ) {
        $index = WebRequest::indexData(
            $service,
            $model,
            $header,
            $filter,
            $request->page,
            $request->perPage,
            $request->sort,
            $request->sortDesc,
            $request->filter,
            $request->search,
            $customMethod,
            $isUsingDefaultOrder,
            $customMethodParams
        );
        return static::buildView('index', $isReference ? 'index-reference' : null, $viewBlade)
            ->withHeader($index['header'])
            ->withData($index['data'])
            ->withFilter($index['filter'])
            ->withSort($index['order']['no'] ?? 0)
            ->withSortDesc($index['order']['desc'] ?? 0)
            ->withSearch($request->search)
            ->withCreate(($isReference && $request->permission['post']) ? !empty($request->create) : null)
            ->withEdit(($isReference && $request->permission['put']) ? $request->edit : null)
            ->with($viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @param array $cards
     * @param mixed $model
     * @param string $viewData
     * @return Renderable
     */
    public static function create(array $cards, mixed $model = [], array $viewData = [], string $viewBlade = null,)
    {
        return static::buildView('create', viewBlade: $viewBlade)
            ->withData(static::buildFormCard($cards, $model))
            ->with($viewData);
    }

    /**
     * Store a newly created resource in storage.
     * @param mixed $service
     * @param Request $request
     * @param array|null $fields
     * @param mixed $model
     * @param bool $isReference
     * @param array $messageValidation
     * @param string|null $successURL
     * @param string|null $successMessage
     * @param string $customMethod
     * @param array $customMethodParams
     * @return Renderable
     */
    public static function store(
        $service,
        Request $request,
        array $fields = null,
        mixed $model = [],
        bool $isReference = false,
        array $messageValidation = [],
        string $successURL = null,
        string $successMessage = null,
        string $customMethod = 'store',
        array $customMethodParams = []
    ) {
        $data = static::buildFormData($request, $fields, $model);

        if (!is_array($model)) {
            $model = [$model];
        }

        foreach ($model as $value) {
            static::validate(data: $data, model: $value, messages: $messageValidation);
        }

        $data = WebRequest::sanitizeXSS($data, $model);

        $return = $service->{$customMethod}($data, ...$customMethodParams);
        if (Error::isError($return)) {
            return $return->redirectBack();
        }

        if (empty($successURL)) {
            $url = empty($isReference) ? Page::detailURL($return->id) : Page::indexURL();
        } else {
            $url = $successURL;
        }

        $successMessage ??= 'Berhasil menambahkan data';
        return redirect($url)->withSuccess($successMessage);
    }

    /**
     * Show the specified resource.
     * @param mixed $service
     * @param int $id
     * @param array $cards
     * @param mixed $model
     * @param array $viewData
     * @param string|null $viewBlade
     * @param string $customMethod
     * @param bool $checkNestedResource
     * @param array $customMethodParams
     * @return Renderable
     */
    public static function show(
        $service,
        $id,
        array $cards = [],
        mixed $model = [],
        array $viewData = [],
        string $viewBlade = null,
        string $customMethod = 'show',
        bool $checkNestedResource = false,
        array $customMethodParams = [],
        mixed $data = []
    ) {
        static::validate($id);

        if (empty($data)) {
            $data = $service->{$customMethod}($id, ...$customMethodParams);
        }

        if (Error::isError($data)) {
            return $data->redirectBack();
        }

        // rules dan default cards
        if (!is_array($model)) {
            $model = [$model];
        }
        foreach ($model as $value) {
            $cards = WebRequest::buildFields($value, $cards);
        }

        // melengkapi section
        $sectionNumber = empty(current($cards)['items']) ? null : count($cards);
        $isSingleSection = empty($sectionNumber) || ($sectionNumber == 1);
        if ($isSingleSection) {
            $cards = [$cards];
        }

        $info = static::currentResourceInfo(checkNestedResource: $checkNestedResource);
        //get sub resource
        $subResource = explode('.', $info['type']);
        if (count($subResource) > 1) {
            $subResource = $info['resource'] . '/' . $subResource[0];
        } else {
            $subResource = false;
        }

        $cards = static::processCards($cards, $data, $isSingleSection, $subResource, $info, $id);

        //check page conf
        if (isset($viewData['page_conf']) && is_array($viewData['page_conf'])) {
            foreach ($viewData['page_conf'] as $conf) {
                foreach ($conf as $key => $value) {
                    if ($key == 'custom_page') {
                        $conInfo = static::currentResourceInfo($value['component'], $checkNestedResource);
                        $cards[0]['page_conf']['custom_page_data'] = $value['data'];
                        $value = $conInfo['module'] . '::pages.' . $conInfo['resource'] . '.' . $value['component'];
                    }
                    $cards[0]['page_conf'][$key] = $value;
                }
            }
            unset($viewData['page_conf']);
        }
        return static::buildView('show', viewBlade: $viewBlade)
            ->withData($cards)
            ->withRawData($data)
            ->with($viewData);
    }

    /**
     * @param array $cards
     * @param Collection|array $data
     * @param bool $isSingleSection
     * @param string|bool $subResource
     * @param array $info
     * @param int|null $id
     * @return array
     */
    public static function processCards(
        $cards,
        $data,
        $isSingleSection = false,
        $subResource = false,
        $info = [],
        $id = null
    ) {
        foreach ($cards as $i => $card) {
            if ($isSingleSection) {
                $title = 'Informasi ' . Page::translateResource($subResource ?? $info['resource']);
                $card = [
                    'title' => $title,
                    'subtitle' => $title,
                    'items' => $card,
                    'edit_url' => empty(request()->permission['put'])
                        ? null
                        : Page::editURL($id),
                ];
            }
            foreach ($card['items'] as $j => $item) {
                if (isset($item['text'])) {
                    continue;
                }

                $field = $item['name'] ?? $item['field'] ?? null;
                $value = $data[$field] ?? null;
                $type = $item['type'] ?? null;
                $options = $item['options'] ?? null;

                if (empty($value) && empty($options) && $type != 'boolean') {
                    continue;
                }

                // format data
                $formatted = null;
                $isInvert = false;
                if (isset($item['control']) && $item['control'] == 'switch-invert') {
                    $isInvert = true;
                }

                switch ($type) {
                    case 'timestamp':
                        $formatted = Format::timestamp($value);
                        break;
                    case 'boolean':
                        if ($isInvert) {
                            $value = !$value;
                        }

                        $formatted = $value ? 'Ya' : 'Tidak';
                        break;
                    case 'date':
                        $formatted = date('d-m-Y', strtotime($value));
                        break;
                    case 'breakline':
                        $formatted = html_entity_decode($value, ENT_QUOTES, 'UTF-8');;
                        break;
                }

                // mata uang
                if ($currencyField = $item['currency_field'] ?? false) {
                    $currencyValue = $data[$currencyField] ?? null;
                    $convert = $currencyValue !== config('money.defaults.currency');
                    $formatted = money($value, $currencyValue, $convert);
                }

                // options diambil dari model
                if (empty($formatted) && !empty($options) && !is_array($options)) {
                    $formatted = $options::optionValue($value);
                }

                // options diambil dari key jika array
                if (empty($formatted) && !empty($options) && is_array($options)) {
                    if (is_array($value)) {
                        $formatted = '';
                        foreach ($value as $index => $val) {
                            $formatted .= $options[$val] . (end($value) == $val ? '' : '::::');
                        }
                    } else {
                        $formatted = $options[$value] ?? null;
                    }
                }

                $card['items'][$j]['text'] = $formatted ?? $value;

                // Menambahkan original value
                $card['items'][$j]['original'] = $value;
            }

            $cards[$i] = $card;
        }

        return $cards;
    }

    /**
     * Show the form for editing the specified resource.
     * @param mixed $service
     * @param int $id
     * @param array $cards
     * @param mixed $model
     * @param array $viewData
     * @param string|null $viewBlade
     * @param string $customMethod
     * @param array $customMethodParams
     * @return Renderable
     */
    public static function edit(
        $service,
        $id,
        array $cards,
        mixed $model = [],
        array $viewData = [],
        string $viewBlade = null,
        string $customMethod = 'show',
        array $customMethodParams = [],
    ) {
        static::validate($id);

        $data = $service->{$customMethod}($id, ...$customMethodParams);
        return static::buildView('create', viewBlade: $viewBlade)
            ->withData(static::buildFormCard($cards, $model, $data))
            ->with($viewData);
    }

    /**
     * Update the specified resource in storage.
     * @param mixed $service
     * @param int $id
     * @param Request $request
     * @param array|null $fields
     * @param mixed $model
     * @param bool $isReference
     * @param array $messageValidation
     * @param string|null $successURL
     * @param string|null $successMessage
     * @param string $customMethod
     * @param array $customMethodParams
     * @return Renderable
     */
    public static function update(
        $service,
        $id,
        Request $request,
        array $fields = null,
        mixed $model = [],
        bool $isReference = false,
        array $messageValidation = [],
        string $successURL = null,
        string $successMessage = null,
        string $customMethod = 'update',
        array $customMethodParams = [],
    ) {
        $data = static::buildFormData($request, $fields, $model);
        if (!is_array($model)) {
            $model = [$model];
        }
        foreach ($model as $value) {
            static::validate($id, $data, $value, messages: $messageValidation);
        }

        $data = WebRequest::sanitizeXSS($data, $model);

        $return = $service->{$customMethod}($data, $id, ...$customMethodParams);
        if (Error::isError($return)) {
            return $return->redirectBack();
        }

        if (empty($successURL)) {
            $url = empty($isReference) ? Page::detailURL($id) : Page::indexURL();
        } else {
            $url = $successURL;
        }

        $successMessage ??= 'Berhasil mengubah data';
        return redirect($url)->withSuccess($successMessage);
    }

    /**
     * Remove the specified resource from storage.
     * @param mixed $service
     * @param int $id
     * @param string|null $successMessage
     * @param string $customMethod
     * @param array $customMethodParams
     * @return Renderable
     */
    public static function destroy(
        $service,
        $id,
        string $successMessage = null,
        string $customMethod = 'destroy',
        array $customMethodParams = []
    ) {
        static::validate($id);

        $return = $service->{$customMethod}($id, ...$customMethodParams);
        if (Error::isError($return)) {
            return $return->redirectBack();
        }

        $successMessage ??= 'Berhasil menghapus data';

        return redirect()->to(url()->previous())->withSuccess($successMessage);
    }

    /**
     * Remove some resources from storage.
     * @param mixed $service
     * @param Request $request
     * @return Renderable
     */
    public static function destroySome($service, Request $request)
    {
        foreach ($request->group as $id) {
            static::validate($id);
        }

        $return = $service->destroySome($request->group);
        if (Error::isError($return)) {
            return $return->redirectBack();
        }

        return redirect()->to(url()->previous())->withSuccess('Berhasil menghapus data');
    }

    /**
     * Build form card.
     * @param array $cards
     * @param mixed $model
     * @param mixed $data
     * @param bool $dynamicIndex
     * @return array
     */
    public static function buildFormCard($cards = [], $model = [], $data = null)
    {
        // rules dan default cards
        if (is_array($model)) {
            foreach ($model as $value) {
                $cards = WebRequest::buildFields($value, $cards);
            }
        } else {
            $cards = WebRequest::buildFields($model, $cards);
        }

        // return cards
        return static::processBuildFormCard($cards, $data);
    }

    /**
     * Proses build form card untuk mendapatkan value dari data.
     *
     * @param array $cards
     * @param array|null $data
     * @return array|mixed
     */
    private static function processBuildFormCard(array $cards, $data)
    {
        $processCards = [];

        foreach ($cards as $i => $card) {
            $items = $card['items'] ?? $cards;

            foreach ($items as $j => $item) { // set value dari $data, default null
                $field = $item['name'] ?? $item['field'];
                $item['value'] = $data[$field] ?? null;

                // jika control currency, maka jadikan integer
                if (!empty($item['control']) && $item['control'] === 'currency' && !empty($item['value'])) {
                    $item['value'] = (int) $item['value'];
                }

                $items[$j] = $item;
            }

            if ($card['items'] ?? false) { // cek apakah section
                $card['items'] = $items;
                $processCards[$i] = $card;
            } else {
                $processCards = $items;
            }
        }
        return $processCards;
    }

    /**
     * Build form data.
     * @param Request $request
     * @param array $fields
     * @param mixed $model
     * @return array
     */
    private static function buildFormData(Request $request, array $fields = null, mixed $model = [])
    {
        $data = $request->all();

        if (!is_array($model)) {
            $model = [$model];
        }

        foreach ($model as $value) {
            $fields = WebRequest::buildFields($value, $fields, flattenFields: true);
        }

        if (!empty($fields)) {
            $data = Arr::only($data, array_map(fn ($item) => ($item['name'] ?? $item['field']), $fields));
        }

        // handle data yg fields nya itu control 'currency'
        foreach ($fields as $field) {
            if (!empty($field['control']) && $field['control'] === 'currency') {
                $data[$field['field']] = str_replace(['.', ','], '', $data[$field['field']]);
            }
        }

        return $data;
    }

    /**
     * Build view.
     * @param string $type
     * @param string|null $templateType
     * @param string|null $viewBlade
     * @return \Illuminate\Contracts\View\View
     */
    public static function buildView(string $type, string $templateType = null, string $viewBlade = null)
    {
        $data = static::currentResourceInfo($type);
        $viewBlade ??= $data['module'] . '::pages.' . $data['resource'] . '.' . $data['type'];

        $theme = config($data['module'].".theme") ?? '';

        if (!empty($theme)) {
             $theme .= '.';
        }
        return View::first([$viewBlade, "core::pages.{$theme}templates." . ($templateType ?? $data['type'])]);
    }

    /**
     * Show current resource info.
     * @param string|null $type
     * @param bool $checkNestedResource
     * @return array
     */
    private static function currentResourceInfo(string $type = null, bool $checkNestedResource = false)
    {
        $routeName = Route::currentRouteName();
        if (!empty($routeName)) {
            if (!$checkNestedResource) {
                $routeNames = explode('.', $routeName);
                $module = $routeNames[0] ?? null;
                $resource = $routeNames[count($routeNames) - 2] ?? null;
                $routeType = $routeNames[count($routeNames) - 1] ?? null;
            } else {
                [$module, $resource, $routeType] = explode('.', $routeName, 3);
            }

            return [
                'module' => $module,
                'resource' => $resource,
                'type' => $type ?? $routeType,
            ];
        }

        $data = Arr::only(Page::showURLInfo(), ['module', 'resource']);
        if (!empty($type)) {
            $data['type'] = $type;
        }

        return $data;
    }

    /**
     * Validasi dari request.
     * @param string $id
     * @param array $data
     * @param string $model
     * @return \Illuminate\Contracts\View\View
     */
    public static function validate(string $id = null, array $data = [], string $model = null, array $messages = [])
    {
        if (isset($id) && !filter_var($id, FILTER_VALIDATE_INT)) {
            abort(404);
        }

        if (isset($id) && !WebRequest::validateId($id)) {
            abort(404);
        }

        if (!empty($data) && !empty($model)) {
            $data = WebRequest::validateData($data, $model, $id, messages: $messages);
        }
    }

    /**
     * Export Resource
     *
     * @param mixed $service
     * @param Request $request
     * @param array $header
     * @param array $filter
     * @param string|null $model
     * @param string|null $exportableClass
     * @param string $customMethod
     * @param bool $isUsingDefaultOrder
     * @param array $customMethodParams
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public static function export(
        string $fileName,
        mixed $service,
        Request $request,
        array $header = [],
        array $filter = [],
        string $model = null,
        string $exportableClass = ExportFromArray::class,
        string $customMethod = 'export',
        bool $isUsingDefaultOrder = true,
        array $customMethodParams = [],
    ) : mixed
    {
        $exportData = WebRequest::exportData(
            $service,
            $model,
            $header,
            $filter,
            $request->sort,
            $request->sortDesc,
            $request->filter,
            $request->search,
            $customMethod,
            $isUsingDefaultOrder,
            $customMethodParams
        );
        if (empty($exportData['data'])) {
            $info = Page::showURLInfo();
            $message = [
                "Oops.. Terjadi Kesalahan",
                "Data ". Page::translateResource($info['resource'], null, $info['module']) ." tidak dapat diekspor karena tidak ada data yang tersedia. Pastikan data sudah diinput atau coba sesuaikan filter pencarian sebelum mengekspor.",
                "double"
            ];
            return (new Error($message))->redirectBack();
        }
        $exportObject = new $exportableClass(...$exportData);
        return Excel::download($exportObject, $fileName);
    }
}
