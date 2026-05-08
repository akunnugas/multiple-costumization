<?php

namespace Modules\Kerjasama\Livewire;

use Arr;
use DB;
use Livewire\Attributes\On;
use Modules\Core\Livewire\CreateEditComponent;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Page;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Livewire\Traits\WithMultipleFileUploads;
use Modules\Core\Models\UnitKerja;
use Modules\Kerjasama\Models\Kerjasama;
use Modules\Kerjasama\Models\Mitra;
use Modules\Kerjasama\Models\PenanggungJawab;
use Modules\Kerjasama\Services\DokumenKerjasamaManagementService;
use Modules\Kerjasama\Services\KerjasamaManagementService;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;
use Modules\Core\Helpers\Cstr;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\WebController;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\Kerjasama\Services\MitraManagementService;
use Modules\Kerjasama\Services\PenanggungJawabManagementService;
use Modules\Kerjasama\Services\PihakPenanggungJawabManagementService;
use Throwable;

class FormKerjasama extends CreateEditComponent
{
    use ViewData, WithMultipleFileUploads;

    // services
    protected PihakPenanggungJawabManagementService $pihakPenanggungJawabService;
    protected PenanggungJawabManagementService $penanggungJawabService;
    protected UnitKerjaManagementService $unitKerjaService;
    protected MitraManagementService $mitraService;
    protected DokumenKerjasamaManagementService $dokumenKerjasamaService;

    protected string $hasPenanggungJawabModel = Kerjasama::class;
    protected string $dokumenServiceThrough = 'dokumenService';
    protected string $dokumenModelColumn = "id_kerjasama";

    protected mixed $return;

    const MITRA = 'mitra';
    const UNITKERJA = 'unit';

    const MODEL_PIHAK = [
        self::MITRA => Mitra::class,
        self::UNITKERJA => UnitKerja::class
    ];

    public array $kontakMitra = [];

    public ?int $pihakMitraIndex;

    public $dataPihakPenanggungJawab = [];
    public $dataPenanggungJawab = [];
    public $pihakPenanggungJawabNum = 2;
    public $penanggungJawabNum = [
        '1' => 1,
        '2' => 1
    ];

    public array $unitKerjaOptions;
    public array $mitraOptions;
    public $penanggungJawab = [
        1 => [
            "pihak_penanggung_jawab" => [],
            "penanggung_jawab" => [
                0 => []
            ],
        ],
        2 => [
            "pihak_penanggung_jawab" => [],
            "penanggung_jawab" => [
                0 => []
            ],
        ]
    ];

    // dokumen untuk proses upload
    public array $dokumen = [];

    // dokumen ini akan dijadikan dari model Dokumen menjadi array assoc
    // karena nantinya akan ditampilkan via js
    public array $dokumenUploaded = [];

    // dokumen yang akan diremove disimpan disini
    public array $dokumenShouldRemove = [];

    public $fieldParams = [
        'overridePenanggungJawab' => [
            1 => [],
            2 => []
        ],
    ];

    protected $view = 'kerjasama::components.livewire.kerjasama.create';

    #[Renderless]
    public function loadService()
    {
        $this->service = new KerjasamaManagementService();
        $this->pihakPenanggungJawabService = new PihakPenanggungJawabManagementService();
        $this->penanggungJawabService = new PenanggungJawabManagementService();
        $this->unitKerjaService = new UnitKerjaManagementService();
        $this->mitraService = new MitraManagementService();
        $this->dokumenService = new DokumenKerjasamaManagementService();
    }

    public function loadModel()
    {
        $this->model = Kerjasama::class;
    }

    public function mount()
    {
        parent::mount();

        $this->loadOptions();

        if (!empty($this->edit)) {
            $this->loadPenanggungJawab($this->edit);
        } else {
            // jika create default set tanggal mulai berlaku hari ini
            $this->record['tanggal_mulai_berlaku'] = now()->format('Y-m-d');
        }

        // Set value ke record ketika edit
        $this->loadRecord();

        //set value record jika ada di session
        $this->loadRecordFromSession();
    }

    public function save()
    {
        $this->dispatch('show-loading');
        $this->render();

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

        $this->dispatch('saveAction');
    }

    #[On('saveAction')]
    public function saveAction()
    {
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
        }

        $attributes = Page::defineLabelByField(false, $this->urlInfo);

        try {
            // cek custom validasi jika ada
            $resultCustomValidate = $this->customValidationBeforeSave();

            // cek validasi model
            WebRequest::validateData($data, $this->model, $this->edit, $attributes, $this->messageValidation());
            $this->validatePenanggungJawab();
            $this->validateKerjasama();
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
            if ($this->edit) {
                $return = $this->service->update($data, $this->edit);
            } else {
                $return = $this->service->store($data);
            }

            $this->saveDokumen($return);
            $this->savePenanggungJawab($return);
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

        $this->return = $return;

        $message = $this->getSuccessMessage();

        $customRedirect = $this->successUrlAfterSave();
        if (!empty($customRedirect)) {
            return redirect()->to($customRedirect)->with('success', $message);
        }

        if (count($this->urlInfo['segments']) > 4) {
            $param = [];
            // sub resource
            foreach ($this->urlInfo['parameters'] as $key => $value) {
                $param[] = (int) $value;
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

    public function tambahMitra()
    {
        // set session recordnya agar tidak hilang
        session()->put([
            'kerjasama' => [
                'data-kerjasama/create' => $this->record
            ]
        ]);

        $this->redirect(
            route('kerjasama.mitra.create')
                . '?redirectAfterCreate=' . route('kerjasama.data-kerjasama.create')
                . '&refer=data-kerjasama'
        );
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

            $this->dataPihakPenanggungJawab = WebController::buildFormCard(
                $this->defineFormFieldsPihakPenanggungJawab($this->pihakPenanggungJawabNum),
                $this->model,
                $this->penanggungJawab
            );
        } else {
            $this->data = WebController::buildFormCard(
                $this->defineFormFields(),
                $this->model
            );

            $this->dataPihakPenanggungJawab = WebController::buildFormCard(
                $this->defineFormFieldsPihakPenanggungJawab($this->pihakPenanggungJawabNum),
                $this->model
            );
        }

        // define form untuk penanggung jawab
        foreach ($this->penanggungJawab as $keyPihak => $item) {
            foreach ($item['penanggung_jawab'] as $keyPenanggungJawab => $child) {
                $this->dataPenanggungJawab[$keyPihak][$keyPenanggungJawab] = $this->defineFormFieldsPenanggungJawab($keyPihak, $keyPenanggungJawab);
            }
            // Ensure the array key exists even if empty
            if (!isset($this->dataPenanggungJawab[$keyPihak])) {
                $this->dataPenanggungJawab[$keyPihak] = [];
            }
        }
        // Ensure both keys exist for pihak 1 and 2
        foreach ([1, 2] as $pihakKey) {
            if (!isset($this->dataPenanggungJawab[$pihakKey])) {
                $this->dataPenanggungJawab[$pihakKey] = [];
            }
        }

        // [End] proses get rule dari model (dan set value jika edit)
        // Set wire:model (dan selected value ketika options)
        $this->initForm();
    }

    public function updated($field, $value)
    {
        $this->dispatch('property-updated', ['field' => $field, 'value' => $value]);
    }

    public function updating($field, $value)
    {
        if (Str::startsWith($field, 'penanggungJawab.1.pihak_penanggung_jawab.jenis_pihak')) {
            $this->renderPihakPenangungJawab($value);
        }

        if ($field == 'record.id_unit_kerja') {
            $this->renderPihakPenangungJawab(
                jenisPihak: $this->penanggungJawab[1]['pihak_penanggung_jawab']['jenis_pihak'] ?? null,
                idUnitKerja: $value,
                idMitra: $this->record['id_mitra'],
            );
        }

        if ($field == 'record.id_mitra') {
            $this->renderPihakPenangungJawab(
                jenisPihak: $this->penanggungJawab[1]['pihak_penanggung_jawab']['jenis_pihak'] ?? null,
                idMitra: $value,
                idUnitKerja: $this->record['id_unit_kerja'],
            );

            $this->loadKontakMitra($value);
        }
    }


    public function fillPenanggungJawabFromSelectKontak($idKontak, $keyPihak, $keyPenanggungJawab)
    {
        $selectedKontak = $this->kontakMitra[$idKontak] ?? null;
        if (empty($selectedKontak)) {
            return;
        }

        $this->penanggungJawab[$keyPihak]['penanggung_jawab'][$keyPenanggungJawab] = [
            'nama_penanggung_jawab' => $selectedKontak['nama_kontak'],
            'jabatan' => $selectedKontak['jabatan'],
            'email' => $selectedKontak['email'],
            'telepon' => $selectedKontak['telepon']
        ];
    }

    protected function loadOptions()
    {
        $unitKerjaOptions = UnitKerja::options();

        $this->unitKerjaOptions = $unitKerjaOptions;
        $this->mitraOptions = Mitra::options();
    }

    /**
     * Save data penanggung jawab
     * 
     * @param mixed $kerjasama
     * @return [type]
     */
    protected function savePenanggungJawab($kerjasama)
    {
        if (!empty($this->edit)) {
            $idsPihakPenanggungJawab = array_column($this->record['pihak_penanggung_jawab'], 'id');
            $exceptIds = [];
            foreach (array_column($this->penanggungJawab, 'penanggung_jawab') as $item) {
                foreach ($item as $key => $value) {
                    if (empty($value['id'])) {
                        continue;
                    }
                    $exceptIds[] = $value['id'];
                }
            }
            $this->penanggungJawabService->destroyByPihakPenanggungJawab($idsPihakPenanggungJawab, $exceptIds);
        }

        foreach ($this->penanggungJawab as $key => $item) {
            $item['pihak_penanggung_jawab']['model_pihak'] = self::MODEL_PIHAK[$item['pihak_penanggung_jawab']['jenis_pihak']];
            unset($item['pihak_penanggung_jawab']['jenis_pihak']);

            // prepare data pihak penanggung jawab
            $paramDataPihakPenanggungJawab = [
                ...$item['pihak_penanggung_jawab'],
                'pihak_ke' => $key,
                "model" => $this->hasPenanggungJawabModel,
                "model_id" => $kerjasama->id
            ];

            if (!empty($this->edit) && !empty($item['pihak_penanggung_jawab']['id'])) {
                // aksi update
                $pihakPenanggungJawab = $this->pihakPenanggungJawabService
                    ->update($paramDataPihakPenanggungJawab, $item['pihak_penanggung_jawab']['id']);
            } else {
                // aksi store
                $pihakPenanggungJawab = $this->pihakPenanggungJawabService
                    ->store($paramDataPihakPenanggungJawab);
            }

            if (Error::isError($pihakPenanggungJawab)) {
                throw $pihakPenanggungJawab->exception;
            }

            foreach ($item['penanggung_jawab'] as $penanggungJawab) {
                $paramDataPenanggungJawab = [
                    ...$penanggungJawab,
                    "id_pihak_penanggung_jawab" => $pihakPenanggungJawab->id
                ];

                if (!empty($this->edit) && !empty($penanggungJawab['id'])) {
                    // aksi update
                    $this->penanggungJawabService->update($paramDataPenanggungJawab, $penanggungJawab['id']);
                } else {
                    // aksi store
                    $this->penanggungJawabService->store($paramDataPenanggungJawab);
                }
            }
        }
    }

    /**
     * Load data penanggung jawab ke record livewire
     * 
     * @param string $idKerjasama
     * @return void
     */
    protected function loadPenanggungJawab(string $idKerjasama): void
    {

        $pihakPenanggungJawab = $this->pihakPenanggungJawabService
            ->getAllPihakPenanggungJawab($this->hasPenanggungJawabModel, $idKerjasama);

        $penanggungJawabJenisPihakPertama = null;
        $listIdPihak = [
            self::UNITKERJA => null,
            self::MITRA => null
        ];

        foreach ($pihakPenanggungJawab as $pihak) {
            $pihakKe = $pihak->pihak_ke;
            $jenisPihak = array_flip(self::MODEL_PIHAK)[$pihak->model_pihak];

            if ($pihakKe == 1) {
                // butuh jenis pihak untuk render pihak setelah asign data
                $penanggungJawabJenisPihakPertama = $jenisPihak;
            }

            $listIdPihak[$jenisPihak] = $pihak->id_pihak;

            $this->penanggungJawabNum[$pihak->pihak_ke] = $pihak->penanggung_jawab->count();
            $this->penanggungJawab[$pihakKe]['pihak_penanggung_jawab'] = [
                ...$pihak->only(['id', 'alamat', 'id_pihak', 'model_pihak', 'model', 'model_id']),
                "jenis_pihak" => $jenisPihak,
            ];

            unset($this->penanggungJawab[$pihakKe]['penanggung_jawab'][0]);
            foreach ($pihak->penanggung_jawab as $index => $penanggungJawab) {
                $this->penanggungJawab[$pihakKe]['penanggung_jawab'][$index] = $penanggungJawab->only([
                    'id',
                    'nama_penanggung_jawab',
                    'email',
                    'telepon',
                    'jabatan',
                    'id_pihak_penanggung_jawab',
                    'nip'
                ]);
            }
        }
        $this->renderPihakPenangungJawab(
            $penanggungJawabJenisPihakPertama,
            $listIdPihak[self::UNITKERJA] ?? null,
            $listIdPihak[self::MITRA] ?? null
        );
    }
    protected function validatePenanggungJawab()
    {
        $this->validate(
            rules: [
                "penanggungJawab.*.pihak_penanggung_jawab" => 'required',
                "penanggungJawab.*.penanggung_jawab.*.nama_penanggung_jawab" => 'required',
                "penanggungJawab.*.penanggung_jawab.*.jabatan" => 'nullable',
                "penanggungJawab.*.penanggung_jawab.*.email" => 'nullable|email',
                "penanggungJawab.*.penanggung_jawab.*.telepon" => 'nullable|numeric',
            ],
            attributes: [
                "penanggungJawab.*.pihak_penanggung_jawab.alamat" => __("kerjasama::pihak_penanggung_jawab.alamat"),

                "penanggungJawab.*.penanggung_jawab.*.nama_penanggung_jawab" => __("kerjasama::penanggungjawab.nama_penanggung_jawab"),
                "penanggungJawab.*.penanggung_jawab.*.email" => __("kerjasama::penanggungjawab.email"),
                "penanggungJawab.*.penanggung_jawab.*.telepon" => __("kerjasama::penanggungjawab.telepon"),
                "penanggungJawab.*.penanggung_jawab.*.jabatan" => __("kerjasama::penanggungjawab.jabatan"),
            ]
        );
    }

    protected function validateKerjasama()
    {


        $judul = $this->record['judul_kerjasama'] ?? null;
        $idMitra = $this->record['id_mitra'] ?? null;

        if ($judul && $idMitra) {
            $query = Kerjasama::where('judul_kerjasama', $judul)
                ->where('id_mitra', $idMitra);

            // Exclude current record if editing
            if ($this->edit) {
                $query->where('id', '!=', $this->edit);
            }

            if ($query->exists()) {

                $mitra = Mitra::find($idMitra);
                throw ValidationException::withMessages([
                    'judul_kerjasama' => ["Nama kerjasama tidak boleh sama dengan yang ada pada mitra ($mitra->nama_mitra) yang sama."],
                ]);
            }
        }
    }

    protected function scrollToErrorInput(Throwable $exception)
    {
        if (!$exception instanceof ValidationException) {
            return;
        }

        $idElement = "form-control-" . array_keys($exception->validator->errors()->toArray())[0];
        $this->dispatch('scroll-to-element', ['id' => $idElement]);
    }

    protected function renderPihakPenangungJawab($jenisPihak, $idUnitKerja = null, $idMitra = null)
    {
        if (empty($jenisPihak) && !empty($this->edit)) {
            $this->fieldParams['overridePenanggungJawab'][1] = [
                'disabled' => true,
                'selected' => null,
                'options' => []
            ];
            $this->fieldParams['overridePenanggungJawab'][2] = [
                'disabled' => true,
                'selected' => null,
                'options' => []
            ];
            return;
        }
        // get referenced mitra data
        $idUnitKerja ??= $this->record['id_unit_kerja'] ?? null;
        $idMitra ??= $this->record['id_mitra'] ?? null;
        $modelMitra = null; // Initialize variable

        $unit = $this->unitKerjaOptions[$idUnitKerja] ?? null;
        $unitData = null;

        if (!empty($idUnitKerja)) {
            $unitData = $this->unitKerjaService->show($idUnitKerja);
        }

        $mitra = $this->mitraOptions[$idMitra] ?? null;
        if (!empty($idMitra)) {
            $mitra = $this->mitraOptions[$idMitra] ?? null;
            $mitraService = new MitraManagementService();
            $modelMitra = $mitraService->show($idMitra);
        }

        $inputMitra = [
            'disabled' => true,
            'selected' => $idMitra,
            'options' => [
                $idMitra => $mitra
            ],
        ];

        $inputUnit = [
            'disabled' => true,
            'selected' => $idUnitKerja,
            'options' => [
                $idUnitKerja => $unit
            ],
        ];

        $pihakUnitIndex = null;
        $pihakMitraIndex = null;

        if ($jenisPihak == self::UNITKERJA) {
            if (empty($this->record['id_mitra']) && empty($this->edit)) {
                return;
            }

            $pihakUnitIndex = 1;
            $pihakMitraIndex = 2;
        } else {
            if (empty($this->record['id_unit_kerja']) && empty($this->edit)) {
                return;
            }

            $pihakUnitIndex = 2;
            $pihakMitraIndex = 1;
        }

        $this->pihakMitraIndex = $pihakMitraIndex;

        // asign data
        $this->penanggungJawab[$pihakUnitIndex]['pihak_penanggung_jawab']['id_pihak'] = $idUnitKerja;
        $this->penanggungJawab[$pihakUnitIndex]['pihak_penanggung_jawab']['jenis_pihak'] = self::UNITKERJA;
        $this->penanggungJawab[$pihakMitraIndex]['pihak_penanggung_jawab']['jenis_pihak'] = self::MITRA;
        $this->penanggungJawab[$pihakMitraIndex]['pihak_penanggung_jawab']['id_pihak'] = $idMitra;

        $this->fieldParams['overridePenanggungJawab'][$pihakUnitIndex] = $inputUnit;
        $this->fieldParams['overridePenanggungJawab'][$pihakMitraIndex] = $inputMitra;
        $this->penanggungJawab[$pihakMitraIndex]['pihak_penanggung_jawab']['alamat'] = $modelMitra ? Cstr::unescapeDeep($modelMitra->alamat ?? null) : null;
        $this->penanggungJawab[$pihakUnitIndex]['pihak_penanggung_jawab']['alamat'] = $unitData?->alamat ?? null;


        if (empty($this->edit)) {
            $this->refresh();
        }
    }

    public function addPenanggungJawab($keyPihak)
    {
        $this->penanggungJawabNum[$keyPihak]++;
        $this->penanggungJawab[$keyPihak]['penanggung_jawab'][] = [];
    }

    public function deletePenanggungJawab($keyPihak, $keyPenanggungJawab)
    {
        $this->penanggungJawabNum[$keyPihak] -= 1;
        unset($this->penanggungJawab[$keyPihak]['penanggung_jawab'][$keyPenanggungJawab]);
        unset($this->dataPenanggungJawab[$keyPihak][$keyPenanggungJawab]);
    }

    protected function defineFormFieldsPihakPenanggungJawab($numPihak)
    {
        $fields = [];
        foreach (range(1, $numPihak) as $key) {
            $baseModelKey = "penanggungJawab.$key.";
            $fields['pihak-ke' . $key] = [
                'title' => 'Pihak ke ' . $key,
                'icon' => 'users',
                'items' => [
                    [
                        'field' => $baseModelKey . "pihak_penanggung_jawab.pihak",
                        'label' => "Pihak Penanggung Jawab",
                        'required' => true,
                        'wire:model.change' => $baseModelKey . "pihak_penanggung_jawab.pihak",
                        'disabled' => false,
                        'withLabel' => false
                    ],
                    [
                        'field' => $baseModelKey . "pihak_penanggung_jawab.alamat",
                        'label' => "Alamat",
                        'control' => 'textarea',
                        'wire:model' => $baseModelKey . "pihak_penanggung_jawab.alamat"
                    ],
                ]
            ];

            $fields['pihak-ke' . $key]['items'][0] = [
                ...$fields['pihak-ke' . $key]['items'][0],
                ...$this->fieldParams['overridePenanggungJawab'][$key]
            ];

            if ($key == 1) {
                $override = [
                    'field' => $baseModelKey . "pihak_penanggung_jawab.jenis_pihak",
                    'label' => "Pihak ke $key dari",
                    'control' => 'radio',
                    'required' => true,
                    'disabled' => empty($this->edit) && (empty($this->record['id_unit_kerja']) || empty($this->record['id_mitra'])),
                    'options' => ['unit' => 'Unit', 'mitra' => 'Mitra'],
                    'wire:model.change' => $baseModelKey . "pihak_penanggung_jawab.jenis_pihak",
                    'selected' => null // Explicitly set no default selection

                ];
                array_unshift($fields['pihak-ke' . $key]['items'], $override);
            }
        }

        return $fields;
    }

    protected function defineFormFieldsPenanggungJawab($keyPihak, $keyPenanggungJawab)
    {
        $fields = [];
        $baseModelKey = "penanggungJawab.$keyPihak.";
        $fields = [
            [
                'field' => $baseModelKey . "penanggung_jawab.$keyPenanggungJawab.nama_penanggung_jawab",
                'label' => __('kerjasama::penanggungjawab.nama_penanggung_jawab'),
                ...PenanggungJawab::RULES['nama_penanggung_jawab'],
                'wire:model' => $baseModelKey . "penanggung_jawab.$keyPenanggungJawab.nama_penanggung_jawab",
            ],
            [
                'field' => $baseModelKey . "penanggung_jawab.$keyPenanggungJawab.nip",
                'label' => __('kerjasama::penanggungjawab.nip'),
                ...PenanggungJawab::RULES['nip'],
                'wire:model' => $baseModelKey . "penanggung_jawab.$keyPenanggungJawab.nip",
                'column' => 6
            ],
            [
                'field' => $baseModelKey . "penanggung_jawab.$keyPenanggungJawab.jabatan",
                'label' => __('kerjasama::penanggungjawab.jabatan'),
                ...PenanggungJawab::RULES['jabatan'],
                'wire:model' => $baseModelKey . "penanggung_jawab.$keyPenanggungJawab.jabatan",
                'column' => 6
            ],
            [
                'field' => $baseModelKey . "penanggung_jawab.$keyPenanggungJawab.email",
                'label' => __('kerjasama::penanggungjawab.email'),
                ...PenanggungJawab::RULES['email'],
                'wire:model' => $baseModelKey . "penanggung_jawab.$keyPenanggungJawab.email",
                'column' => '6'
            ],
            [
                'field' => $baseModelKey . "penanggung_jawab.$keyPenanggungJawab.telepon",
                'type' => 'number',
                'label' => __('kerjasama::penanggungjawab.telepon'),
                ...PenanggungJawab::RULES['telepon'],
                'wire:model' => $baseModelKey . "penanggung_jawab.$keyPenanggungJawab.telepon",
                'column' => '6'
            ],
        ];

        return $fields;
    }

    protected function defineFormFields()
    {
        return [
            'informasi-kerjasama' => [
                'title' => 'Informasi Kerjasama',
                'items' => [
                    ['field' => 'nomor_dokumen', 'column' => 6],
                    ['field' => 'nomor_dokumen_mitra', 'column' => 6],
                    ['field' => 'id_jenis_dokumen'],
                    ['field' => 'id_unit_kerja', 'options' => $this->unitKerjaOptions, 'column' => 6],
                    ['field' => 'id_mitra', 'wire:model.change' => 'record.id_mitra', 'options' => $this->mitraOptions, 'column' => 6, 'component' => 'forms.id_mitra', 'module' => 'kerjasama'],
                    ['field' => 'judul_kerjasama'],
                    ['field' => 'deskripsi'],
                    ['field' => 'id_sumber_dana', 'column' => 6],
                    ['field' => 'anggaran', 'column' => 6],
                    ['field' => 'tanggal_mulai_berlaku'],
                    ['field' => 'tanggal_akhir_berlaku'],
                    ['field' => 'id_status_kerjasama'],
                    ['field' => 'id_dokumen', 'wire:model' => 'dokumen', 'name' => 'dokumen', 'dokumenUploaded' => json_encode($this->dokumenUploaded)],
                    ['field' => 'hasil_pelaksanaan'],
                ]
            ]
        ];
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

    protected function loadRecord()
    {
        if (empty($this->edit)) {
            return;
        }

        $data = Cstr::unescapeDeep(($this->service->show($this->edit))->toArray());
        unset($data['id_dokumen']);

        $this->record = [
            ...$data,
            ...$this->record
        ];

        if (!empty($data['id_mitra'])) {
            // load kontak mitra
            $this->loadKontakMitra($data['id_mitra']);
        }

        // load dokumen
        $this->{$this->dokumenServiceThrough}->getByModelId($this->edit)->each(function ($model) {
            $this->dokumenUploaded[] = [
                'id' => $model->id,
                'name' => $model->dokumen->nama_dokumen . '.' . $model->dokumen->extension_versi_terbaru,
                'size' => $model->dokumen->lastVersionSize,
                'url' => $model->dokumen->lastVersionTemporaryUrl(),
                'type' => $model->dokumen->extension_versi_terbaru
            ];
        });
    }

    protected function getSuccessMessage()
    {
        return ['Kerjasama', $this->edit ? 'Berhasil mengubah data' : 'Berhasil menambahkan data'];
    }

    protected function saveDokumen($kerjasama)
    {
        foreach ($this->dokumen as $doc) {
            $returns[] = $this->{$this->dokumenServiceThrough}->store([
                $this->dokumenModelColumn => $kerjasama->id,
                'id_dokumen' => $doc
            ]);
        }
        if (!empty($this->dokumenShouldRemove)) {
            $this->{$this->dokumenServiceThrough}->destroySome(array_column($this->dokumenShouldRemove, 'id'));
        }
    }

    protected function loadRecordFromSession()
    {
        $recordData = session()->get('kerjasama.data-kerjasama/create');

        if (empty($recordData)) {
            return;
        }

        $this->record = [
            ...$this->record,
            ...$recordData
        ];

        if (!empty($recordData['id_mitra'])) {
            // load kontak mitra
            $this->loadKontakMitra($recordData['id_mitra']);
        }

        session()->remove('kerjasama.data-kerjasama/create');
    }

    protected function loadKontakMitra($idMitra)
    {
        $mitra = $this->mitraService->show($idMitra);

        if (empty($mitra)) {
            return;
        }

        $kontakMitra = $mitra->kontak->toArray() ?? [];

        if (empty($kontakMitra)) {
            return;
        }

        $kontakMapped = [];
        foreach ($kontakMitra as $kontak) {
            $kontakMapped[$kontak['id']] = $kontak;
        }

        $this->kontakMitra = $kontakMapped;
    }
}
