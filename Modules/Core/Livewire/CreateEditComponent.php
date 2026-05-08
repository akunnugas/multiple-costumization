<?php

namespace Modules\Core\Livewire;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Page;
use Modules\Core\Helpers\WebRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class CreateEditComponent extends MainComponent
{
    use AuthorizesRequests;
    public $edit = null;

    public $record = [];
    public $alert;
    public $routeName;
    public $backSubFooter;
    public $mergeData = [];

    protected $data;
    protected $model;
    protected $view = 'core::components.livewire.layouts.create-edit';
    protected array $customViewData = [
        'showCollapseInSection' => false
    ];

    public function mount()
    {
        parent::mount();

        $this->routeName = Route::currentRouteName();

        $this->edit = $this->viewData['resourceId'];

        // back url for sub footer
        if (count($this->urlInfo['segments']) > 4) {
            $url = $this->urlInfo['segments'];
            array_pop($url);
            $this->backSubFooter = implode('/', $url);
        }
    }

    public function boot()
    {
        parent::boot();

        $this->loadModel();
    }

    public function render()
    {
        $this->loadData();

        $this->beforeRender();

        return $this->buildView($this->view);
    }

    protected function beforeRender()
    {
    }

    protected function loadData()
    {
        $isCanAction = $this->validatePermission('post') || $this->validatePermission('put');

        if (!$isCanAction) {
            abort(403);
        }

        // [Start] proses get rule dari model (dan set value jika edit)
        if ($this->edit) {
            $edit = WebRequest::editData($this->service, $this->model, $this->edit, $this->defineFormFields(), $this->viewData);
            $this->data = $edit['data'];
        } else {
            $create = WebRequest::createData($this->defineFormFields(), $this->model, $this->viewData);
            $this->data = $create['data'];
        }
        // [End] proses get rule dari model (dan set value jika edit)

        // Set wire:model (dan selected value ketika options)
        $this->initForm();

        // Set value ke record ketika edit
        $this->loadRecord();

        // Jika menggunakan trait ChoicesMultiple maka load choices
        if (method_exists($this, 'loadChoices')) {
            $this->loadChoices();
        }
    }

    protected function buildView($page)
    {
        View::share($this->viewData + [
            'isLivewire' => true,
            'permission' => $this->permission,
            'urlInfo' => $this->urlInfo,
            'data' => $this->data,
            'headerClass' => 'header_position-static',
        ] + $this->customViewData);

        $data = $this->currentResourceInfo('create');

        return View::first([
            $data['module'] . '::pages.' . $data['resource'] . '.' . $data['type'],
            $page,
        ])
            ->layout('core::components.layouts.main-outer');
    }

    protected function defineFormFields()
    {
        return [];
    }

    /**
     * Set value ke record ketika edit
     *
     * @return void
     */
    protected function loadRecord()
    {
        if (empty($this->edit)) {
            return;
        }
        foreach ($this->data as $row) {
            // Jika file maka skip value
            if (isset($row['file_type'])) {
                continue;
            }

            if (isset($row['control']) && $row['control'] === 'checkbox') {
                $this->record[$row['field']] = [];

                $records = $row['value'];

                foreach ($records as $val) {
                    $value = $this->record[$row['field']][$val] ?? null;
                    if (!isset($value)) {
                        $this->record[$row['field']][$val] = true;
                    }
                }

                continue;
            }

            $fromInputValue = $this->record[$row['field']] ?? null;
            $value = $fromInputValue ?? $row['value'];

            if (isset($row['control']) && $row['control'] === 'switch') {
                $value = $value ?? false;
            }

            $this->record[$row['field']] = $value;
        }
    }

    public $saving = false;

    public function save()
    {
        if ($this->saving) {
            return;
        }
        $this->saving = true;

        $this->dispatch('show-loading');

        $isCanAction = $this->validatePermission('post');

        if (!$isCanAction) {
            $this->saving = false;
            $this->dispatch('hide-loading');

            $this->alert = [
                'type' => 'error',
                'message' => "Anda tidak memiliki akses untuk menyimpan data",
            ];

            $this->dispatch('scroll-to-top');
            return;
        }

        // validasi data
        $defineFields = $this->defineFormFields();
        if (isset($this->additionalRecords) && !empty($this->additionalRecords)) {
            $defineFields = array_merge($defineFields, $this->additionalRecords);
        }
        $fields = WebRequest::buildFields($this->model, $defineFields, flattenFields: true);
        $data = Arr::only($this->record, array_map(fn ($item) => $item['field'], $fields));

        foreach ($data as $key => $value) {
            $filterField = array_filter($fields, fn ($item) => $item['field'] == $key);
            $filterField = array_values($filterField)[0] ?? [];

            if (isset($filterField['options']) && (isset($filterField['control']) && $filterField['control'] == 'select')) {
                $selectValue = $this->selectValue($value);
                $data[$key] = $selectValue;

                // Jika select dan tidak ada value maka set null
                if (is_null($selectValue)) {
                    $data[$key] = null;
                }
            }

            // penyesuaian jika memiliki control
            if (isset($filterField['control'])) {
                if ($filterField['control'] === 'switch') {
                    $data[$key] = $value ?? false;
                } elseif ($filterField['control'] === 'currency' && !empty($value)) {
                    // hapus titik ataupun koma, karena value aslinya adalah number
                    $data[$key] = (int) str_replace(['.', ','], '', $value);
                }
            }

            if (isset($filterField['type']) && $filterField['type'] === 'number') {
                $data[$key] = $value === '' ? null : $value;
            }
        }

        $attributes = Page::defineLabelByField(false, $this->urlInfo);

        try {
            // cek custom validasi jika ada
            $resultCustomValidate = $this->customValidationBeforeSave();

            // cek validasi model
            WebRequest::validateData($data, $this->model, $this->edit, $attributes, $this->messageValidation());
        } catch (\Exception $e) {
            $this->saving = false;
            $this->dispatch('hide-loading');
            throw $e;
        }

        if ($resultCustomValidate === false) { // jika custom validasi gagal
            $this->saving = false;
            $this->dispatch('hide-loading');
            return;
        }

        // Sanitasi input
        $data = WebRequest::sanitizeXSS($data, $this->model);

        if (!empty($this->mergeData)) {
            $data = array_merge($data, $this->mergeData);
        }

        if (!empty($this->choices)) {
            $data = array_merge($data, $this->choices);
        }

        try {
            if ($this->edit) {
                $return = $this->service->update($data, $this->edit);
            } else {
                $return = $this->service->store($data);
            }
        } catch (ValidationException $e) {
            $this->saving = false;
            $this->dispatch('hide-loading');
            throw $e;
        }

        if (Error::isError($return)) {
            $this->saving = false;
            $this->dispatch('hide-loading');

            // Jika error, maka kembalikan alert
            $this->alert = [
                'type' => 'error',
                'message' => $return->message,
            ];

            $this->dispatch('scroll-to-top');

            return $this->loadData();
        }

        $message = $this->edit ? 'Berhasil mengubah data' : 'Berhasil menambah data';

        $customRedirect = $this->successUrlAfterSave();
        if (!empty($customRedirect)) {
            return redirect()->to($customRedirect)->with('success', $message);
        }

        if (count($this->urlInfo['segments']) > 4) {
            $param = [];
            // sub resource
            foreach ($this->urlInfo['parameters'] as $key => $value) {
                $param[] = (int)$value;
            }

            $param[] = $return->id;
            if ($this->edit) {
                array_pop($this->urlInfo['segments']);
                return redirect()->to(implode('/', $this->urlInfo['segments']))->with('success', $message);
            } else {
                // delete last segment
                array_pop($this->urlInfo['segments']);
                return redirect()->to(implode('/', $this->urlInfo['segments']) . '/' . $return->id)->with('success', $message);
            }
        }

        return redirect()->route($this->urlInfo['module'] . '.' . $this->urlInfo['resource'] . '.show', $return->id)->with('success', $message);
    }

    protected function successUrlAfterSave()
    {
        return null;
    }

    protected abstract function loadModel();

    /**
     * Show current resource info.
     * @param string $type
     * @return array
     */
    private function currentResourceInfo(string $type = null)
    {
        if (!empty($this->routeName)) {
            [$module, $resource, $routeType] = explode('.', $this->routeName, 3);

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

    public function refresh()
    {
        $this->loadModel();

        $this->loadData();
    }

    /**
     * Proses set wire:model (dan selected ketika options)
     *
     * @return void
     */
    protected function initForm()
    {
        // Menambahkan wire:model
        foreach ($this->data as $k => $item) {
            $this->data[$k] = [...$item, 'wire:model' => 'record.' . $item['field']];

            $record = $this->record[$item['field']] ?? null;

            // Jika field tidak ada di record maka tambahkan dengan nilai null
            if (!array_key_exists($item['field'], $this->record)) {
                $this->record[$item['field']] = null;
            }

            // Jika options seperti select, maka pertahankan value yang dipilih ketika rerender
            // TODO: Sementara masih menggunakan cara untuk mengakali choices
            if (isset($item['options'])) {
                $record = $this->selectValue($record);

                if (!empty($item['control']) && $item['control'] === 'select-multiple-v2') {
                    $record = $record ?? [];
                    $this->data[$k] = array_merge($this->data[$k], ['selected' => $record, 'values' => $record]);
                } elseif (isset($record) && !is_array($record)) {
                    // Jika valuenya tidak array atau multiple maka pertahankan value yang dipilih ketika rerender
                    $this->data[$k] = array_merge($this->data[$k], ['selected' => $record, 'value' => $record]);
                }
            }
        }
    }

    protected function selectValue($value)
    {
        if (is_array($value) && array_key_exists('value', $value)) {
            return $value['value'];
        }

        return $value ?? null;
    }



    /**
     * Fungsi untuk menampilkan alert.
     *
     * @param string $type
     * @param string $message
     */
    protected function alert($type, $message)
    {
        $this->alert = [
            'type' => $type,
            'message' => $message,
        ];
    }

    /**
     * Bisa digunakan ketika membutuhkan custom validasi.
     * Method ini jalan sebelum validasi model WebRequest::validateData() yg ada di dalam proses save()
     *
     * @return bool
     */
    protected function customValidationBeforeSave(): bool
    {
        return true;
    }

    /**
     * Digunakan ketika membutuhkan custom message validation.
     * Berdasarkan validation yang ada di model.
     *
     * @return array
     */
    protected function messageValidation()
    {
        return [];
    }
}
