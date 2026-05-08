<?php

namespace Modules\Kerjasama\Livewire;

use Arr;
use DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Modules\Core\Livewire\CreateEditComponent;
use Illuminate\Support\Facades\View;
use Modules\Core\Helpers\Cstr;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Page;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Models\Wilayah;
use Modules\Kerjasama\Enums\JenisMitra;
use Modules\Kerjasama\Models\Kontak;
use Modules\Kerjasama\Models\Mitra;
use Modules\Kerjasama\Services\KontakManagementService;
use Modules\Kerjasama\Services\MitraManagementService;

class FormMitra extends CreateEditComponent
{
    use ViewData, WithFileUploads;

    #[Url]
    public ?string $redirectAfterCreate = null;

    #[Url]
    public ?string $refer = null;

    // services
    protected KontakManagementService $kontakService;

    public $optionsWilayah = [
        'negara' => [],
        'provinsi' => [],
        'kota' => [],
        'kecamatan' => []
    ];

    public $overrideFormFields = [];
    protected $kontakCount = 1;
    public $kontakFields = [];

    public $record = [
        'kontak' => []
    ];

    protected $view = 'kerjasama::components.livewire.mitra.create';

    public function loadService()
    {
        $this->service = new MitraManagementService();
        $this->kontakService = new KontakManagementService();
    }

    public function loadModel()
    {
        $this->model = Mitra::class;
    }

    public function mount()
    {
        parent::mount();

        $this->loadRecord();
        
       
    }

    protected function scrollToErrorInput(ValidationException $exception)
    {
        $idElement = "form-control-".array_keys($exception->validator->errors()->toArray())[0];
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
            if(empty($item['field'])) {
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

    public function updatedOverrideFormFields()
    {

    }

    protected function loadWilayah(): array
    {
        // optimize load wilayah
        $isLoadCountry = !empty($this->record['tingkat_mitra']) && $this->record['tingkat_mitra'] == Mitra::LEVEL_INTERNASIONAL;
        $isLoadProvinsi = !$isLoadCountry;
        $isLoadKota =  !empty($this->record['id_provinsi']);
        $isLoadKecamatan = !empty($this->record['id_provinsi']) && !empty($this->record['id_kota']);

        $optionsNegara = $isLoadCountry ? Wilayah::optionsByLevel(Wilayah::LEVEL_NEGARA) : [];
        $optionsProvinsi = $isLoadProvinsi ? Wilayah::optionsByLevel(Wilayah::LEVEL_PROVINSI) : [];
        $optionsKota = $isLoadKota ? Wilayah::optionsByLevel(Wilayah::LEVEL_KOTA_KABUPATEN, $this->record['id_provinsi'] ?? null) : [];
        $optionsKecamatan = $isLoadKecamatan ? Wilayah::optionsByLevel(Wilayah::LEVEL_KECAMATAN, $this->record['id_kota'] ?? null) : [];

        return [
            $optionsNegara,
            $optionsProvinsi,
            $optionsKota,
            $optionsKecamatan
        ];
    }

    protected function renderSelectWilayah(): array
    {
        [  
            $optionsNegara, 
            $optionsProvinsi, 
            $optionsKota, 
            $optionsKecamatan
        ] = $this->loadWilayah();

        if (!empty($this->record['tingkat_mitra']) && $this->record['tingkat_mitra'] == Mitra::LEVEL_INTERNASIONAL) {
            $fields = [
                ['field' => 'id_negara', 'options' => $optionsNegara, 'wire:model.change' => 'record.id_negara', 'column' => 6],
            ];
        } else {
            $fields = [
                ['field' => 'id_provinsi', 'options' => $optionsProvinsi, 'wire:model.change' => 'record.id_provinsi', 'column' => 6],
                ['field' => 'id_kota', 'options' => $optionsKota, 'wire:model.change' => 'record.id_kota', 'column' => 6],
                ['field' => 'id_kecamatan', 'options' => $optionsKecamatan, 'wire:model' => 'record.id_kecamatan', 'column' => 6],
            ];
        }

        return $fields;
    }

    protected function loadKontak(string $idMitra): void
    {
        $kontak = $this->kontakService->getAllKontakByMitra($idMitra)->toArray();
        foreach ($kontak as $index => $data) {
            $this->record['kontak'][$index] = $data;
        }
    }

    protected function defineKontakFormFields($num)
    {
        $fields = [];
        for ($i=0; $i < $num; $i++) { 
            $baseKey = "record.kontak.$i";
            $fields[] = [
                [
                    'field' => "$baseKey.nama_kontak", 
                    ...Kontak::RULES["nama_kontak"],
                    "label" => __('kerjasama::kontak.nama_kontak'), 
                    'wire:model' => "$baseKey.nama_kontak", 
                    'column' => 6
                ],
                [
                    'field' => "$baseKey.jabatan", 
                    ...Kontak::RULES["jabatan"],
                    "label" => __('kerjasama::kontak.jabatan'), 
                    'wire:model' => "$baseKey.jabatan", 
                    'column' => 6
                ],
                [
                    'field' => "$baseKey.telepon", 
                    ...Kontak::RULES["telepon"],
                    "label" => __('kerjasama::kontak.telepon'), 
                    'wire:model' => "$baseKey.telepon", 
                    'column' => 6
                ],
                [
                    'field' => "$baseKey.email", 
                    ...Kontak::RULES["email"],
                    "label" => __('kerjasama::kontak.email'), 
                    'wire:model' => "$baseKey.email", 
                    'column' => 6
                ],
            ];
        }

        return $fields;
    }

    protected function defineFormFields()
    {
        $this->kontakFields = $this->defineKontakFormFields(count($this->record['kontak']));
        return [
            'informasi-mitra' => [
                'title' => 'Informasi Mitra',
                'icon' => 'building-05',
                'items' => [
                    ['field' => 'jenis_mitra', 'options' => JenisMitra::getOptions()],
                    ['field' => 'nama_mitra'],
                    ['field' => 'id_kriteria_mitra'],
                    ['field' => 'kode_mitra', 'column' => 6, 'placeholder' => '(opsional)'],                    
                    ['field' => 'npwp_mitra', 'column' => 6, 'placeholder' => '(opsional)'],                    
                    ['field' => 'tingkat_mitra', 'wire:model.change' => 'record.tingkat_mitra', 'selected' => Mitra::LEVEL_REGIONAL],

                    // render wilayah
                    ...$this->renderSelectWilayah(),

                    ['field' => 'kode_pos', 'column' => 6],
                    ['field' => 'alamat'],
                    // ['field' => 'link_googlemap'],
                    ['field' => 'email'],
                    ['field' => 'telepon'],
                    ['field' => 'website', 'component' => true],
                ],
            ],
            'informasi-kontak-mitra' => [
                'title' => 'Informasi Kontak Mitra',
                'icon' => 'phone',
                'items' => [
                    // ditambahkan ini agar card bisa muncul tanpa error
                    ['field' => '_', 'type' => 'hidden'],
                ]
            ]
        ];
    }

    public function addKontak()
    {
        $this->record['kontak'][] = [];
    }

    public function removeKontak($index)
    {
        unset($this->record['kontak'][$index]);
        $this->record['kontak'] = array_values($this->record['kontak']);
    }

    protected function validateKontak()
    {
        $baseRules = Kontak::rules();
        $baseKey = "record.kontak.*";
        $langBaseKey = "kerjasama::kontak";
        foreach ($baseRules as $key => $value) {
            // mapping ulang key rulesnya
            $baseRules["$baseKey.$key"] = $value;
            // hapus rule yang tak terpakai
            unset($baseRules[$key]);
        }

        $this->validate(
            rules: $baseRules,
            attributes: [
                "$baseKey.nama_kontak" => __("$langBaseKey.nama_kontak"),
                "$baseKey.jabatan" => __("$langBaseKey.jabatan"),
                "$baseKey.telepon" => __("$langBaseKey.telepon"),
                "$baseKey.email" => __("$langBaseKey.email"),
            ]
        );
    }

    protected function saveKontak($mitra)
    {
        // hapus kontak jika kontak sudah dihapus
        if (!empty($this->edit)) {
            $this->kontakService->destroyByMitra(idMitra: $mitra->id, exceptIds: array_column($this->record['kontak'], 'id') ?? []);
        }

        foreach ($this->record['kontak'] as $kontak) {
            if (!empty($kontak['id'])) {
                // jika ada id maka update
                $this->kontakService
                    ->update($kontak, $kontak['id']);

                continue;
            }

            // jika tidak ditemukan id berarti store
            $this->kontakService
                ->store([
                    ...$kontak,
                    'id_mitra' => $mitra->id
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
            $this->validateKontak();

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
            $this->saveKontak($return);

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

        if ($this->refer == 'data-kerjasama') {
            return $this->handleDirectCreateKerjasama($return, $message);
        }

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

    protected function successUrlAfterSave()
    {
        return $this->redirectAfterCreate;
    }
    
    /**
     * Handle ketika refer langsung dari data-kerjasama
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function handleDirectCreateKerjasama($return, $message) 
    {
        $sessionData = session()->get('kerjasama.data-kerjasama/create');
        session()->put([
            'kerjasama' => [
                'data-kerjasama/create' => [
                    ...$sessionData,
                    'id_mitra' => $return->id
                ]
            ]
        ]);

        return redirect()->to($this->successUrlAfterSave())->with('success', $message);
    }
}
