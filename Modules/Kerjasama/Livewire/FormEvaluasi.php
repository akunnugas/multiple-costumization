<?php

namespace Modules\Kerjasama\Livewire;

use Arr;
use DB;
use Illuminate\Support\Arr as SupportArr;
use Illuminate\Support\Facades\Log;
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
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\Kerjasama\Enums\JenisMitra;
use Modules\Kerjasama\Models\Evaluasi;
use Modules\Kerjasama\Models\Kerjasama;
use Modules\Kerjasama\Models\Kontak;
use Modules\Kerjasama\Models\Mitra;
use Modules\Kerjasama\Models\Pertanyaan;
use Modules\Kerjasama\Models\OpsiJawaban;
use Modules\Kerjasama\Services\EvaluasiKerjasamaManagementService;
use Modules\Kerjasama\Services\KerjasamaManagementService;
use Modules\Kerjasama\Services\KontakManagementService;
use Modules\Kerjasama\Services\MitraManagementService;
use Illuminate\Support\Str;
use Modules\Kerjasama\Services\OpsiPertanyaanManagementService;
use Modules\Kerjasama\Services\PertanyaanEvaluasiManagementService;
use Modules\Kerjasama\Helpers\Menu;

class FormEvaluasi extends CreateEditComponent
{
    use ViewData, WithFileUploads;

    public Kerjasama|null $indukKerjasama = null;

    protected  KerjasamaManagementService $kerjasamaService;
    protected UnitKerjaManagementService $unitKerjaService;
    protected MitraManagementService $mitraService;


    protected PertanyaanEvaluasiManagementService $pertanyaanService;

    protected OpsiPertanyaanManagementService $opsiJawabanService;


    public $parent;

    protected $service;
    #[Url]
    public $id_parent;

    #[Url]
    public ?string $redirectAfterCreate = null;

    #[Url]
    public ?string $refer = null;




    protected function defineViewData()
    {
        $idParent = $this->id_parent ?? ($this->indukKerjasama->id ?? null);
        $sidebar = [];

        if ($idParent) {
            $sidebar = Menu::kerjasamaSidebar($idParent);

            // Inject active state
            if (isset($sidebar['items'][0]['items'])) {
                 foreach ($sidebar['items'][0]['items'] as &$item) {
                     if (($item['path'] ?? '') === 'evaluasi-kerjasama') {
                         $item['items'][] = [
                             'label' => $this->edit ? 'Edit Evaluasi' : 'Tambah Evaluasi',
                             'active' => true,
                         ];
                         break;
                     }
                 }
            }
        }

        $data = Page::buildViewData(
            'kerjasama', 
            Menu::navbar(), 
            $sidebar, 
            [], 
            lastBreadcrumb: [] 
        ) + parent::defineViewData();

        // Suppress sidebar rendering
        unset($data['submenu']);

        return $data;
    }


    public $optionsWilayah = [
        'negara' => [],
        'provinsi' => [],
        'kota' => [],
        'kecamatan' => []
    ];

    public $overrideFormFields = [];
    protected $kontakCount = 1;
    public $kontakFields = [];

    public $pertanyaanFields = [];

    public $record = [
        'kontak' => [],
        'pertanyaan' => [],
    ];

    protected $view = 'kerjasama::components.livewire.evaluasi.create';

    public function loadService()

    {
        $this->kerjasamaService = new KerjasamaManagementService();
        $this->unitKerjaService = new UnitKerjaManagementService();
        $this->mitraService = new MitraManagementService();
        $this->service = new EvaluasiKerjasamaManagementService();
        $this->pertanyaanService = new PertanyaanEvaluasiManagementService();
        $this->opsiJawabanService = new OpsiPertanyaanManagementService();
    }

    public function loadModel()
    {
        $this->model = Evaluasi::class;
    }

    public function mount($evaluasi_kuesioner = null)
    {
        parent::mount();

        // if (request()->has('backUrl')) {
        //     $this->backSubFooter = request()->get('backUrl');
        // }

        if (!empty($this->id_parent)) {
            $this->loadIndukKerjasama($this->id_parent);
            $this->viewData = $this->defineViewData();
        }

        if (!empty($evaluasi_kuesioner)) {
            $this->parent = $this->service->determinteModelTypeAndId($evaluasi_kuesioner);

            if ($this->parent->tipe_model == Kerjasama::class) {
                $this->loadIndukKerjasama($this->parent->model_id);
                $this->viewData = $this->defineViewData();
            }
        }
        $this->loadRecord();


        // set default tingkat mitra to regional
        if (empty($this->edit)) {
            $this->record['tingkat_mitra'] = Mitra::LEVEL_REGIONAL;
        }

        // Ensure form data is always built and initialized
        $this->loadData();
    }

    protected function scrollToErrorInput($exception)
    {
        if ($exception instanceof ValidationException) {
            $idElement = "form-control-" . array_keys($exception->validator->errors()->toArray())[0];
            $this->dispatch('scroll-to-element', ['id' => $idElement]);
        } else {
            $this->dispatch('scroll-to-top');
        }
    }



    // ganti ini lur
    protected function loadIndukKerjasama($idParent)
    {
        if (empty($idParent)) {
            return;
        }

        $this->indukKerjasama = $this->kerjasamaService->show($idParent);
        // $this->record['id_mitra'] = $this->indukKerjasama->mitra->nama_mitra;

        $this->record['id_mitra'] = $this->indukKerjasama->mitra->id;
        $this->record['nama_mitra'] = $this->indukKerjasama->mitra->nama_mitra ?? '-';

        $this->record['id_induk_kerjasama'] = $this->indukKerjasama->id;
        $this->record['nama_kerjasama'] = $this->indukKerjasama->judul_kerjasama ?? '-';
        $this->record['model_id'] = $this->indukKerjasama->id;    

        if ($this->edit) {
            $this->backSubFooter = route('kerjasama.evaluasi-kuesioner.show', $this->edit);
        } else {
            $this->backSubFooter = route('kerjasama.evaluasi-kerjasama.index', $this->indukKerjasama->id);
        }
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


    public function updatedOverrideFormFields() {}




  
    protected function defineFormFields()
    {
        // Ensure display values for disabled inputs are available in record
        if ($this->indukKerjasama) {
            $this->record['nama_kerjasama'] = $this->indukKerjasama->judul_kerjasama ?? '-';
            $this->record['nama_mitra'] = $this->indukKerjasama->mitra->nama_mitra ?? '-';
            
            // Ensure IDs are consistent
            $this->record['id_induk_kerjasama'] = $this->indukKerjasama->id;
            $this->record['id_mitra'] = $this->indukKerjasama->mitra->id;
        }

        $indukKerjasamaOptions = $this->kerjasamaService->getIndukKerjasamaOptions();

        $mitraOptions = Mitra::options();

        // dd($mitraOptions, $indukKerjasamaOptions, $this->record, $this->parent);

        // Use key-value array for options, similar to options() pattern

        return [
            'informasi-evaluasi' => [
                'title' => 'Informasi Evaluasi',
                'icon' => 'building-05',
                'items' => [
                    ['field' => 'judul_evaluasi'],
                    ['field' => 'tipe_evaluasi'],
                    // Jika ada parent/id_parent (disabled), tampilkan input text disabled + hidden value
                    ...(!empty($this->id_parent) || !empty($this->parent) 
                        ? [
                            ['field' => 'nama_kerjasama', 'disabled' => true, 'control' => 'text', 'label' => 'Kerjasama'],
                            ['field' => 'model_id', 'type' => 'hidden', 'wire:model' => 'record.id_induk_kerjasama'],
                            ['field' => 'nama_mitra', 'disabled' => true, 'control' => 'text', 'label' => 'Mitra'],
                            ['field' => 'id_mitra', 'type' => 'hidden', 'wire:model' => 'record.id_mitra']
                        ] 
                        : [
                            ['field' => 'model_id', 'required' => true, 'wire:model.change' => 'record.id_induk_kerjasama', 'options' => $indukKerjasamaOptions],
                            ['field' => 'id_mitra', 'required' => true, 'wire:model.change' => 'record.id_mitra', 'options' => $mitraOptions]
                        ]
                    ),
                    ['field' => 'mulai'],
                    ['field' => 'selesai'],
                    [
                        'field' => 'is_published',
                    ],
                    // ...$this->renderSelectWilayah(),
                ],
            ],
            'pertanyaan' => [
                'title' => 'Pertanyaan',
                'icon' => 'plus',
                'items' => [
                    // dummy field to keep card visible, actual pertanyaan handled in blade
                    ['field' => '_', 'type' => 'hidden'],
                ]
            ]
        ];
    }



    public function addPertanyaan()
    {
        $nextNomor = count($this->record['pertanyaan']) + 1;
        $this->record['pertanyaan'][] = [
            'nomor' => $nextNomor,
            // Set default max_rating to 5 for rating type
            'max_rating' => 5,
        ];
    }

    public function removePertanyaan($index)
    {
        unset($this->record['pertanyaan'][$index]);
        $this->record['pertanyaan'] = array_values($this->record['pertanyaan']);
        // Re-number after removal
        foreach ($this->record['pertanyaan'] as $i => &$pertanyaan) {
            $pertanyaan['nomor'] = $i + 1;
        }
    }

    public function movePertanyaanUp($index)
    {
        if ($index <= 0) return;
        $temp = $this->record['pertanyaan'][$index];
        $this->record['pertanyaan'][$index] = $this->record['pertanyaan'][$index - 1];
        $this->record['pertanyaan'][$index - 1] = $temp;
        $this->renumberPertanyaan();
    }

    public function movePertanyaanDown($index)
    {
        if ($index >= count($this->record['pertanyaan']) - 1) return;
        $temp = $this->record['pertanyaan'][$index];
        $this->record['pertanyaan'][$index] = $this->record['pertanyaan'][$index + 1];
        $this->record['pertanyaan'][$index + 1] = $temp;
        $this->renumberPertanyaan();
    }

    public function duplicatePertanyaan($index)
    {
        if (!isset($this->record['pertanyaan'][$index])) return;
        $copy = $this->record['pertanyaan'][$index];
        unset($copy['id']);

        if (isset($copy['opsi_jawaban']) && is_array($copy['opsi_jawaban'])) {
            foreach ($copy['opsi_jawaban'] as &$opsi) {
                if (is_array($opsi)) {
                    unset($opsi['id']);
                }
            }
        }

        $copy['nomor'] = count($this->record['pertanyaan']) + 1;
        $this->record['pertanyaan'] = array_values($this->record['pertanyaan']);
        array_splice($this->record['pertanyaan'], $index + 1, 0, [$copy]);
        $this->renumberPertanyaan();
    }

    private function renumberPertanyaan()
    {
        foreach ($this->record['pertanyaan'] as $i => &$pertanyaan) {
            $pertanyaan['nomor'] = $i + 1;
        }
    }

    protected function validatePertanyaan()
    {
        $baseRules = Pertanyaan::RULES;
        $baseKey = "record.pertanyaan.*";
        $langBaseKey = "kerjasama::pertanyaan";

        // Remove fields not present in form input
        unset($baseRules['evaluasi_id'], $baseRules['rating']);

        // Ensure all pertanyaan have all fields (especially nullable ones)
        foreach ($this->record['pertanyaan'] as $i => &$pertanyaan) {
            if (!array_key_exists('deskripsi', $pertanyaan)) {
                $pertanyaan['deskripsi'] = null;
            }
            if (!array_key_exists('tipe', $pertanyaan)) {
                $pertanyaan['tipe'] = null;
            }
            if (!array_key_exists('apakah_wajib', $pertanyaan)) {
                $pertanyaan['apakah_wajib'] = false;
            }
        }
        unset($pertanyaan);

        // Build expanded rules array
        $expandedRules = [];
        foreach ($baseRules as $key => $value) {
            if (is_array($value) && Arr::isAssoc($value)) {
                $ruleString = [];
                foreach ($value as $ruleKey => $ruleVal) {
                    if ($ruleKey === 'required' && $ruleVal) {
                        $ruleString[] = 'required';
                    } elseif ($ruleKey === 'maxlength') {
                        $ruleString[] = 'max:' . $ruleVal;
                    } elseif ($ruleKey === 'type' && $ruleVal === 'integer') {
                        $ruleString[] = 'integer';
                    } elseif ($ruleKey === 'nullable' && $ruleVal) {
                        $ruleString[] = 'nullable';
                    }
                }
                if (empty($ruleString)) {
                    $ruleString[] = 'nullable';
                }
                $expandedRules["$baseKey.$key"] = implode('|', $ruleString);
            } else {
                $expandedRules["$baseKey.$key"] = $value;
            }
        }

        $this->validate(
            rules: $expandedRules,
            attributes: [
                "$baseKey.nomor" => __("$langBaseKey.nomor"),
                "$baseKey.pertanyaan" => __("$langBaseKey.pertanyaan"),
                "$baseKey.deskripsi" => __("$langBaseKey.deskripsi"),
                "$baseKey.apakah_wajib" => __("$langBaseKey.apakah_wajib"),
                "$baseKey.tipe" => __("$langBaseKey.tipe"),
            ]
        );
    }

    protected function savePertanyaan($evaluasi)
    {
        // Remove pertanyaan not present anymore
        if (!empty($this->edit)) {
            $ids = array_filter(array_column($this->record['pertanyaan'], 'id'));
            // Use service for delete
            $pertanyaanToDelete = Pertanyaan::where('evaluasi_id', $evaluasi->id)
                ->whereNotIn('id', $ids)
                ->pluck('id')
                ->toArray();
            if (!empty($pertanyaanToDelete)) {
                $deleteResult = $this->pertanyaanService->destroySome($pertanyaanToDelete);
                if (Error::isError($deleteResult)) {
                    throw ValidationException::withMessages([
                        'record.pertanyaan' => $deleteResult->message
                    ]);
                }
            }
        }


        foreach ($this->record['pertanyaan'] as $pertanyaanIndex => $pertanyaan) {
            // Save/update pertanyaan
            if (!empty($pertanyaan['id'])) {
                $this->pertanyaanService->update([
                    'nomor' => $pertanyaan['nomor'] ?? null,
                    'pertanyaan' => $pertanyaan['pertanyaan'] ?? null,
                    'deskripsi' => $pertanyaan['deskripsi'] ?? null,
                    'apakah_wajib' => $pertanyaan['apakah_wajib'] ?? false,
                    'tipe' => $pertanyaan['tipe'] ?? null,
                    'rating' => $pertanyaan['max_rating'] ?? null,
                ], $pertanyaan['id']);
                $pertanyaanId = $pertanyaan['id'];
            } else {
                $created = $this->pertanyaanService->store([
                    'evaluasi_id' => $evaluasi->id,
                    'nomor' => $pertanyaan['nomor'] ?? null,
                    'pertanyaan' => $pertanyaan['pertanyaan'] ?? null,
                    'deskripsi' => $pertanyaan['deskripsi'] ?? null,
                    'apakah_wajib' => $pertanyaan['apakah_wajib'] ?? false,
                    'tipe' => $pertanyaan['tipe'] ?? null,
                    'rating' => $pertanyaan['max_rating'] ?? null,
                ]);
                $pertanyaanId = $created->id;
            }

            // Save opsi_jawaban
            if (isset($pertanyaan['opsi_jawaban'])) {
                // Remove opsi_jawaban not present anymore (for edit)
                if (!empty($pertanyaan['id'])) {
                    $existingOpsi = OpsiJawaban::where('pertanyaan_id', $pertanyaanId)->pluck('id')->toArray();
                    $inputOpsi = array_filter(array_map(function ($v) {
                        return $v['id'] ?? null;
                    }, $pertanyaan['opsi_jawaban']));
                    $toDelete = array_diff($existingOpsi, $inputOpsi);
                    if (!empty($toDelete)) {
                        $this->opsiJawabanService->destroySome($toDelete);
                    }
                }
                foreach ($pertanyaan['opsi_jawaban'] as $opsiIndex => $opsi) {
                    // Support both string and array input for opsi_jawaban
                    if (is_array($opsi)) {
                        $jawaban = $opsi['jawaban'] ?? '';
                        $opsiId = $opsi['id'] ?? null;
                    } else {
                        $jawaban = $opsi;
                        $opsiId = null;
                    }
                    if (!empty($opsiId)) {
                        $this->opsiJawabanService->update([
                            'jawaban' => $jawaban,
                            'pertanyaan_id' => $pertanyaanId,
                            'urutan' => $opsiIndex + 1,
                        ], $opsiId);
                    } else {
                        if (trim($jawaban) !== '') {
                            $this->opsiJawabanService->store([
                                'pertanyaan_id' => $pertanyaanId,
                                'jawaban' => $jawaban,
                                'urutan' => $opsiIndex + 1,
                            ]);
                        }
                    }
                }
            } else {
                // If no opsi_jawaban provided, remove all for this pertanyaan
                $opsiToDelete = OpsiJawaban::where('pertanyaan_id', $pertanyaanId)->pluck('id')->toArray();
                if (!empty($opsiToDelete)) {
                    $this->opsiJawabanService->destroySome($opsiToDelete);
                }
            }
        }
    }

    protected function validateOpsiJawaban()
    {

        foreach ($this->record['pertanyaan'] as $i => $pertanyaan) {
            $tipe = $pertanyaan['tipe'] ?? null;
            if (in_array($tipe, ['option', 'select'])) {
                $opsi = $pertanyaan['opsi_jawaban'] ?? [];
                // Remove empty opsi
                $opsi = array_filter($opsi, function ($v) {
                    if (is_array($v)) {

                        return !empty($v['jawaban']);
                    }
                    return !empty($v);
                });
                if (count($opsi) < 2) {

                    throw ValidationException::withMessages([
                        "record.pertanyaan.$i.opsi_jawaban" => "Minimal 2 opsi jawaban harus diisi untuk pertanyaan ke-" . ($i + 1),
                    ]);
                }
                // Validate no empty opsi
                foreach ($opsi as $j => $v) {
                    $val = is_array($v) ? ($v['jawaban'] ?? '') : $v;
                    if (trim($val) === '') {
                        throw ValidationException::withMessages([
                            "record.pertanyaan.$i.opsi_jawaban.$j" => "Opsi jawaban tidak boleh kosong pada pertanyaan ke-" . ($i + 1),
                        ]);
                    }
                }
            }
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

        $data = SupportArr::only($this->record, array_map(fn($item) => $item['field'], $fields));

        // $data = $this->record;
        // --- Populate uuid, model_id, tipe_model before validation and save ---
        if (empty($data['uuid']) && empty($this->edit)) {
            $data['uuid'] = (string) Str::uuid();
        }

        // Set model_id and tipe_model for morph relation if indukKerjasama is set
        if ($this->indukKerjasama) {
            $data['model_id'] = $this->indukKerjasama->id;
            // Set tipe_model to the actual model class name (FQCN)
            $data['tipe_model'] = get_class($this->indukKerjasama);
        }

        foreach ($data as $key => $value) {
            $filterField = array_filter($fields, fn($item) => $item['field'] == $key);
            $filterField = array_values($filterField)[0] ?? [];

            // if ($key === 'is_published') {
            //     // Normalize is_published to integer for validation
            //     $data[$key] = (int)($value ?? 0);
            // }

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

        // --- Normalize mulai & selesai to Y-m-d string if needed (before validation) ---
        foreach (['mulai', 'selesai'] as $dateField) {
            if (!empty($data[$dateField])) {
                if ($data[$dateField] instanceof \Carbon\Carbon) {
                    $data[$dateField] = $data[$dateField]->format('Y-m-d');
                } elseif (is_object($data[$dateField]) && method_exists($data[$dateField], 'format')) {
                    $data[$dateField] = $data[$dateField]->format('Y-m-d');
                } elseif (is_string($data[$dateField])) {
                    $data[$dateField] = preg_replace('/\s.*/', '', $data[$dateField]); // remove time if present
                    $data[$dateField] = trim($data[$dateField]) ?: null;
                }
            } else {
                $data[$dateField] = null;
            }
        }

        $attributes = Page::defineLabelByField(false, $this->urlInfo);

        try {
            // cek custom validasi jika ada
            $resultCustomValidate = $this->customValidationBeforeSave();

            // cek validasi model
            WebRequest::validateData($data, $this->model, $this->edit, $attributes, $this->messageValidation());
            $this->validatePertanyaan();

            $this->validateOpsiJawaban();
        } catch (\Exception $e) {
            $this->dispatch('hide-loading');
            if ($e instanceof ValidationException) {
                $this->scrollToErrorInput($e);
            } else {
                $this->dispatch('scroll-to-top');
            }
            throw $e;
        }

        if ($resultCustomValidate === false) { // jika custom validasi gagal
            $this->dispatch('hide-loading');
            return;
        }

        // --- Normalize mulai & selesai again before saving (to be extra safe) ---
        foreach (['mulai', 'selesai'] as $dateField) {
            if (!empty($data[$dateField])) {
                if ($data[$dateField] instanceof \Carbon\Carbon) {
                    $data[$dateField] = $data[$dateField]->format('Y-m-d');
                } elseif (is_object($data[$dateField]) && method_exists($data[$dateField], 'format')) {
                    $data[$dateField] = $data[$dateField]->format('Y-m-d');
                } elseif (is_string($data[$dateField])) {
                    $data[$dateField] = preg_replace('/\s.*/', '', $data[$dateField]);
                    $data[$dateField] = trim($data[$dateField]) ?: null;
                }
            } else {
                $data[$dateField] = null;
            }
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

            $this->savePertanyaan($return);

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

            // Always reload form data after error to keep form in sync
            $this->loadData();
            return;
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

        // Always reload form data after save to keep form in sync (if not redirecting)
        $this->loadData();

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
            'headerClass' => 'header_position-static'
            // 'backSubFooter' => $this->backSubFooter, // Explicitly pass backSubFooter
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

        // Ensure model_id is set for select field
        if (!empty($this->record['model_id'])) {
            $this->record['id_induk_kerjasama'] = $this->record['model_id'];
        }

        // Ensure mulai and selesai are Y-m-d strings
        foreach (['mulai', 'selesai'] as $dateField) {
            if (!empty($this->record[$dateField])) {
                if (is_string($this->record[$dateField]) && preg_match('/^\d{4}-\d{2}-\d{2}T/', $this->record[$dateField])) {
                    $this->record[$dateField] = \Carbon\Carbon::parse($this->record[$dateField])->format('Y-m-d');
                } elseif ($this->record[$dateField] instanceof \Carbon\Carbon) {
                    $this->record[$dateField] = $this->record[$dateField]->format('Y-m-d');
                } elseif (is_object($this->record[$dateField]) && method_exists($this->record[$dateField], 'format')) {
                    $this->record[$dateField] = $this->record[$dateField]->format('Y-m-d');
                } elseif (is_string($this->record[$dateField])) {
                    $this->record[$dateField] = preg_replace('/\s.*/', '', $this->record[$dateField]);
                    $this->record[$dateField] = trim($this->record[$dateField]) ?: null;
                }
            } else {
                $this->record[$dateField] = null;
            }
        }

        // Load pertanyaan for edit
        $pertanyaan = Pertanyaan::where('evaluasi_id', $this->edit)
            ->withCount('jawabanPeserta')
            ->orderBy('nomor')
            ->get()
            ->toArray();
        foreach ($pertanyaan as $i => &$p) {
            $p['nomor'] = $i + 1;
            // Always flatten opsi_jawaban to array with id and jawaban
            $opsiJawaban = OpsiJawaban::where('pertanyaan_id', $p['id'])->orderBy('urutan')->get();
            $p['opsi_jawaban'] = $opsiJawaban->map(function($opsi) {
                return [
                    'id' => $opsi->id,
                    'jawaban' => $opsi->jawaban
                ];
            })->toArray();
            // Map rating field from database to max_rating for form
            if (isset($p['rating'])) {
                $p['max_rating'] = $p['rating'];
            }
            // Set default max_rating to 5 if tipe is rating and max_rating is empty
            if (($p['tipe'] ?? null) === 'rating' && empty($p['max_rating'])) {
                $p['max_rating'] = 5;
            }
            // Ensure apakah_wajib is string '1' or '0' for select binding
            $p['apakah_wajib'] = isset($p['apakah_wajib']) && ($p['apakah_wajib'] == 1 || $p['apakah_wajib'] === true) ? '1' : '0';

            // Check if question has answers
            $p['has_answers'] = Pertanyaan::find($p['id'])->jawabanPeserta()->exists();
        }
        unset($p);
        $this->record['pertanyaan'] = $pertanyaan;

        // Ensure form fields are initialized in record
        if (!empty($this->data)) {
            $this->initForm();
        }
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

    public function addOpsiJawaban($pertanyaanIndex)
    {
        if (!isset($this->record['pertanyaan'][$pertanyaanIndex]['opsi_jawaban'])) {
            $this->record['pertanyaan'][$pertanyaanIndex]['opsi_jawaban'] = [];
        }
        $this->record['pertanyaan'][$pertanyaanIndex]['opsi_jawaban'][] = '';
    }

    public function removeOpsiJawaban($pertanyaanIndex, $opsiIndex)
    {
        if (isset($this->record['pertanyaan'][$pertanyaanIndex]['opsi_jawaban'][$opsiIndex])) {
            unset($this->record['pertanyaan'][$pertanyaanIndex]['opsi_jawaban'][$opsiIndex]);
            // Re-index array to avoid holes
            $this->record['pertanyaan'][$pertanyaanIndex]['opsi_jawaban'] = array_values($this->record['pertanyaan'][$pertanyaanIndex]['opsi_jawaban']);
        }
    }

    public function updatedRecord($value, $key)
    {
        // Only handle pertanyaan tipe changes
        if (Str::startsWith($key, 'pertanyaan.') && Str::endsWith($key, '.tipe')) {
            $parts = explode('.', $key);
            $pertanyaanIndex = $parts[1] ?? null;
            $newType = $value;

            if ($pertanyaanIndex !== null) {
                if (in_array($newType, ['option', 'select'])) {
                    // If switching to option/select, ensure opsi_jawaban exists
                    if (!isset($this->record['pertanyaan'][$pertanyaanIndex]['opsi_jawaban'])) {
                        $this->record['pertanyaan'][$pertanyaanIndex]['opsi_jawaban'] = [];
                    }
                } else {
                    // Remove opsi_jawaban if type is not option or select
                    unset($this->record['pertanyaan'][$pertanyaanIndex]['opsi_jawaban']);
                }
                // Also clear max_rating if not rating
                if ($newType !== 'rating') {
                    unset($this->record['pertanyaan'][$pertanyaanIndex]['max_rating']);
                } elseif (!isset($this->record['pertanyaan'][$pertanyaanIndex]['max_rating'])) {
                    // If switching to rating, set default max_rating if not set
                    $this->record['pertanyaan'][$pertanyaanIndex]['max_rating'] = 5;
                }
            }
        }

    }
}
