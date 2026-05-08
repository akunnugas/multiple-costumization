<?php

namespace Modules\Kerjasama\Livewire;

use Arr;
use DB;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Modules\Core\Livewire\CreateEditComponent;
use Illuminate\Support\Facades\View;
use Modules\Core\Helpers\Cstr;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Page;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Kerjasama\Models\IndikatorSasaran;
use Modules\Kerjasama\Models\SasaranKinerja;
use Modules\Kerjasama\Services\IndikatorSasaranManagementService;
use Modules\Kerjasama\Services\SasaranKinerjaManagementService;

class FormSasaranKinerja extends CreateEditComponent
{
    use ViewData, WithFileUploads;

    // services
    protected IndikatorSasaranManagementService $indikatorService;

    public $overrideFormFields = [];
    public $indikatorFields = [];

    public $record = [
        'indikator' => [
            []
        ]
    ];

    protected $view = 'kerjasama::components.livewire.sasaran_kinerja.create';

    public function loadService()
    {
        $this->service = new SasaranKinerjaManagementService();
        $this->indikatorService = new IndikatorSasaranManagementService();
    }

    public function loadModel()
    {
        $this->model = SasaranKinerja::class;
    }

    public function mount()
    {
        parent::mount();

        $this->loadRecord();
    }

    protected function scrollToErrorInput(ValidationException $exception)
    {
        $idElement = "form-control-" . array_keys($exception->validator->errors()->toArray())[0];
        $this->dispatch('scroll-to-element', ['id' => $idElement]);
    }

    /**
     * Proses set wire:model (dan selected ketika options)
     *
     * @return void
     */
    protected function initForm()
    {
        // init function utk process item
        $processItem = function ($item, &$record) {
            if (empty($item['field'])) {
                return $item;
            }

            if (empty($item['wire:model']) && empty($item['wire:model.change'])) {
                $item['wire:model'] = 'record.' . $item['field'];
            }

            $record[$item['field']] = $record[$item['field']] ?? null;

            // TODO: Sementara masih menggunakan cara untuk mengakali choices
            if (isset($item['options'])) {
                $selectedValue = $this->selectValue($record[$item['field']]);
                if ($item['control'] === 'select-multiple-v2') {
                    $selectedValue = $selectedValue ?? [];
                    $item = array_merge($item, ['selected' => $selectedValue, 'values' => $selectedValue]);
                } elseif ($selectedValue !== null && !is_array($selectedValue)) {
                    $item = array_merge($item, ['selected' => $selectedValue, 'value' => $selectedValue]);
                }
            }
            return $item;
        };

        foreach ($this->data as $i => $card) {
            $items = $card['items'] ?? [];
            foreach ($items as $j => $item) {
                $this->data[$i]['items'][$j] = $processItem($item, $this->record);
            }
        }
    }

    protected function defineIndikatorFormFields($num)
    {
        $fields = [];
        for ($i = 0; $i < $num; $i++) {
            $baseKey = "record.indikator.$i";
            $fields[] = [
                [
                    'field' => "$baseKey.indikator",
                    ...IndikatorSasaran::RULES["indikator"],
                    "label" => __('kerjasama::indikator_sasaran.indikator'),
                    'wire:model' => "$baseKey.indikator"
                ],
                [
                    'field' => "$baseKey.keterangan",
                    ...IndikatorSasaran::RULES["keterangan"],
                    "label" => __('kerjasama::indikator_sasaran.keterangan'),
                    'wire:model' => "$baseKey.keterangan"
                ],
                [
                    'field' => "$baseKey.volume",
                    ...IndikatorSasaran::RULES["volume"],
                    "label" => __('kerjasama::indikator_sasaran.volume'),
                    'wire:model' => "$baseKey.volume",
                    'column' => '8'
                ],
                [
                    'field' => "$baseKey.satuan",
                    ...IndikatorSasaran::RULES["satuan"],
                    "label" => __('kerjasama::indikator_sasaran.satuan'),
                    'wire:model' => "$baseKey.satuan",
                    'column' => '4'
                ],
            ];
        }

        return $fields;
    }

    protected function defineFormFields()
    {
        $this->indikatorFields = $this->defineIndikatorFormFields(count($this->record['indikator']));

        return [
            'informasi-sasaran-kinerja' => [
                'title' => 'Informasi Sasaran Kinerja',
                'icon' => 'award',
                'items' => [
                    ['field' => 'sasaran'],
                    ['field' => 'keterangan'],
                    ['field' => 'level'],
                ],
            ],
            'informasi-indikator-sasaran' => [
                'title' => 'Informasi Indikator Sasaran',
                'icon' => 'bookmark',
                'items' => [
                    // ditambahkan ini agar card bisa muncul tanpa error
                    ['field' => '_', 'type' => 'hidden'],
                ]
            ]
        ];
    }

    public function addIndikator()
    {
        $this->record['indikator'][] = [];
    }

    public function removeIndikator($index)
    {
        unset($this->record['indikator'][$index]);
        $this->record['indikator'] = array_values($this->record['indikator']);
    }

    protected function validateIndikator()
    {
        $baseRules = IndikatorSasaran::rules();
        $baseKey = "record.indikator.*";
        $langBaseKey = "kerjasama::indikator_sasaran";
        foreach ($baseRules as $key => $value) {
            // mapping ulang key rulesnya
            $baseRules["$baseKey.$key"] = $value;
            // hapus rule yang tak terpakai
            unset($baseRules[$key]);
        }

        $this->validate(
            rules: $baseRules,
            attributes: [
                "$baseKey.indikator" => __("$langBaseKey.indikator"),
                "$baseKey.keterangan" => __("$langBaseKey.keterangan"),
                "$baseKey.volume" => __("$langBaseKey.volume"),
                "$baseKey.satuan" => __("$langBaseKey.satuan"),
            ]
        );
    }

    protected function saveIndikator($sasaran)
    {
        // hapus kontak jika kontak sudah dihapus
        if (!empty($this->edit)) {
            $this->indikatorService->destroyBySasaran(idSasaran: $sasaran->id, exceptIds: array_column($this->record['indikator'], 'id') ?? []);
        }

        foreach ($this->record['indikator'] as $indikator) {
            if (!empty($indikator['id'])) {
                // jika ada id maka update
                $this->indikatorService
                    ->update([
                        ...$indikator,
                        'isian_default' => false
                    ], $indikator['id']);

                continue;
            }

            // jika tidak ditemukan id berarti store
            $this->indikatorService
                ->store([
                    ...$indikator,
                    'id_sasaran_kinerja' => $sasaran->id
                ]);
        }
    }

    public function save()
    {
        $this->dispatch('show-loading');

        $isCanAction = $this->validatePermission('post');
        if (!$isCanAction) {
            $this->dispatch('hide-loading');

            $this->alert = [
                'type' => 'error',
                'message' => "Anda tidak memiliki akses untuk menyimpan data",
            ];

            $this->dispatch('scroll-to-top');
            return;
        }

        // validasi data
        $fields = WebRequest::buildFields($this->model, $this->defineFormFields(), flattenFields: true);
        $data = Arr::only($this->record, array_map(fn($item) => $item['field'], $fields));

        foreach ($data as $key => $value) {
            $filterField = array_filter($fields, fn($item) => $item['field'] == $key);
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

            if ($key == 'sasaran') {
                $data[$key] = rtrim($value);
            }
        }

        $attributes = Page::defineLabelByField(false, $this->urlInfo);

        try {
            // cek custom validasi jika ada
            $resultCustomValidate = $this->customValidationBeforeSave();

            // cek validasi model
            WebRequest::validateData($data, $this->model, $this->edit, $attributes, $this->messageValidation());
            $this->validateIndikator();
        } catch (\Exception $e) {
            $this->dispatch('hide-loading');
            $this->scrollToErrorInput($e);
            throw $e;
        }

        if ($resultCustomValidate === false) { // jika custom validasi gagal
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
            DB::beginTransaction();
            $return = null;
            if ($this->edit) {
                $return = $this->service->update($data, $this->edit);
            } else {
                $return = $this->service->store($data);
            }

            if (Error::isError($return)) {
                $this->dispatch('hide-loading');
    
                // Jika error, maka kembalikan alert
                $this->alert = [
                    'type' => 'error',
                    'message' => $return->message,
                ];
                $this->dispatch('scroll-to-top');
    
                return $this->loadData();
            }

            $this->saveIndikator($return);
            DB::commit();
        } catch (ValidationException $e) {
            $this->dispatch('hide-loading');
            DB::rollBack();
            throw $e;
        }

        if (Error::isError($return)) {
            $this->dispatch('hide-loading');

            // Jika error, maka kembalikan alert
            $this->alert = [
                'type' => 'error',
                'message' => $return->message,
            ];

            $this->dispatch('scroll-to-top');

            return $this->loadData();
        }

        $message = $this->edit ? 'Berhasil mengubah data' : 'Berhasil menambahkan data';

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

        // dd($return);
        return redirect()->route($this->urlInfo['module'] . '.' . $this->urlInfo['resource'] . '.show', $return->id)->with('success', $message);
    }

    // sementara overide dari parent
    protected function buildView($page)
    {
        View::share($this->viewData + [
            'isLivewire' => true,
            'permission' => $this->permission,
            'urlInfo' => $this->urlInfo,
            'data' => $this->data,
            'headerClass' => 'header_position-static',
        ] + $this->customViewData);

        return View::first([
            $page,
        ])->layout('core::components.quantum-3.layouts.main-outer')
            ->layoutData(['headerClass' => 'position-static']);
    }

    // sementara di override karena tidak support advance form
    protected function loadRecord()
    {
        if (empty($this->edit)) {
            return;
        }

        $data = Cstr::unescapeDeep(($this->service->show($this->edit))->toArray());
        $this->record = $data;
    }

    protected function loadData()
    {
        $isCanAction = $this->validatePermission('post') || $this->validatePermission('put');

        if (!$isCanAction) {
            abort(403);
        }

        // [Start] proses get rule dari model (dan set value jika edit)
        if ($this->edit) {
            $this->data = WebController::buildFormCard(
                $this->defineFormFields(),
                $this->model,
                $this->record
            );
        } else {
            $this->data = WebController::buildFormCard(
                $this->defineFormFields(),
                $this->model
            );
        }

        // Set wire:model (dan selected value ketika options)
        $this->initForm();
    }
}
