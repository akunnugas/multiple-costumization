<?php

namespace Modules\Kerjasama\Livewire;

use Arr;
use Carbon\Carbon;
use DB;
use Dotenv\Exception\ValidationException;
use Livewire\Attributes\On;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Page;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Models\Mahasiswa;
use Modules\Core\Services\MahasiswaManagementService;
use Modules\Kerjasama\Models\BentukKegiatan;
use Modules\Kerjasama\Models\Kerjasama;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Modules\Core\Helpers\Cstr;
use Modules\Core\Helpers\WebController;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\Kerjasama\Models\Kegiatan;
use Modules\Kerjasama\Models\Mitra;
use Modules\Kerjasama\Models\PelaksanaKegiatan;
use Modules\Kerjasama\Models\SasaranKinerja;
use Modules\Kerjasama\Services\DokumenKegiatanManagementService;
use Modules\Kerjasama\Services\IndikatorSasaranManagementService;
use Modules\Kerjasama\Services\KegiatanManagementService;
use Modules\Kerjasama\Services\KerjasamaManagementService;
use Modules\Kerjasama\Services\MitraManagementService;
use Modules\Kerjasama\Services\PelaksanaKegiatanManagementService;
use Modules\Kerjasama\Services\PenanggungJawabManagementService;
use Modules\Kerjasama\Services\PihakPenanggungJawabManagementService;
use Str;

class FormKegiatan extends FormKerjasama
{
    protected KerjasamaManagementService $kerjasamaService;
    protected IndikatorSasaranManagementService $indikatorSasaranService;
    protected MahasiswaManagementService $mahasiswaService;
    protected PelaksanaKegiatanManagementService $pelaksanaKegiatanService;

    protected string $hasPenanggungJawabModel = Kegiatan::class;

    public array $kontakMitra = [];
    public array $kontakMitra2 = [];
    protected $jenisPihak;
    protected DokumenKegiatanManagementService $dokumenKegiatanService;
    protected string $dokumenServiceThrough = 'dokumenKegiatanService';
    protected string $dokumenModelColumn = "id_kegiatan";
    protected $view = 'kerjasama::components.livewire.kegiatan.create';

    #[Url('id_kerjasama')]
    public $queryParamIdKerjasama = null;

    #[Url('backUrl')]
    public $customBackUrl = null;

    public Kerjasama|null $indukKerjasama = null;
    public array $sasaranKinerjaOptions = [];
    public $optionRadio;
    public ?int $pihakMitraIndex = 2;
    public $record = [
        'pelaksana' => []
    ];

    public string $selected1 = '';
    public string $selected2 = '';
    public array $pelaksanaFields = [];

    public function mount()
    {
        parent::mount();
        $this->record['id_induk_kerjasama'] = $this->queryParamIdKerjasama ?? $this->record['id_induk_kerjasama'] ?? null;

        if (!empty($this->queryParamIdKerjasama) && empty($this->edit)) {
            $this->loadIndukKerjasama($this->queryParamIdKerjasama);
        }

        if (!empty($this->penanggungJawab[1]['pihak_penanggung_jawab']['jenis_pihak'])) {
            $this->jenisPihak = $this->penanggungJawab[1]['pihak_penanggung_jawab']['jenis_pihak'];
            $this->optionRadio = $this->jenisPihak;
            $this->selected1 = $this->jenisPihak;
            $this->loadDefaultPihak($this->jenisPihak);
        }


        if (!empty($this->customBackUrl)) {
            $this->backSubFooter = $this->customBackUrl;
        }
    }

    #[Renderless]
    public function loadService()
    {
        $this->service = new KegiatanManagementService();
        $this->kerjasamaService = new KerjasamaManagementService();
        $this->pihakPenanggungJawabService = new PihakPenanggungJawabManagementService();
        $this->penanggungJawabService = new PenanggungJawabManagementService();
        $this->unitKerjaService = new UnitKerjaManagementService();
        $this->indikatorSasaranService = new IndikatorSasaranManagementService();
        $this->dokumenKegiatanService = new DokumenKegiatanManagementService();
        $this->mahasiswaService = new MahasiswaManagementService();
        $this->pelaksanaKegiatanService = new PelaksanaKegiatanManagementService();
        $this->mitraService = new MitraManagementService();
    }

    public function loadModel()
    {
        $this->model = Kegiatan::class;
    }

    public function updating($field, $value)
    {
        if ($field == 'penanggungJawab.1.pihak_penanggung_jawab.jenis_pihak') {
            $this->optionRadio = $value;
            if ($this->optionRadio == 'mitra') {
                $this->loadDefaultPihak($this->optionRadio);
                $this->loadKontakMitra($this->indukKerjasama->mitra->id);
                $this->selected1 = 'mitra';
                $this->selected2 = '';
            }
            if ($this->optionRadio == 'unit') {
                $this->loadDefaultPihak($this->optionRadio);
                $this->loadKontakMitra($this->indukKerjasama->mitra->id);
                $this->selected1 = 'unit';
                $this->selected2 = '';
            }
        }
        if ($field == 'record.id_induk_kerjasama') {
            $this->loadIndukKerjasama($value);
        }
    }


    protected function renderPihakPenangungJawab($jenisPihak, $idUnitKerja = null, $idMitra = null)
    {
        // get referenced mitra data
        $idUnitKerja ??= $this->record['id_unit_kerja'] ?? null;
        $idMitra ??= $this->record['id_mitra'] ?? null;

        $unit = $this->unitKerjaOptions[$idUnitKerja] ?? null;
        $unitData = null;

        if (!empty($idUnitKerja)) {
            $unitData = $this->unitKerjaService->show($idUnitKerja);
        }

        $mitra = $this->mitraOptions[$idMitra] ?? null;
        if (!empty($idMitra)) {
            $mitraService = new MitraManagementService();
            $modelMitra = $mitraService->show($idMitra);
        }

        $inputMitra = [
            'disabled' => false,
            'selected' => $idMitra,
            'options' => [
                $idMitra => $mitra
            ],
        ];

        $inputUnit = [
            'disabled' => false,
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

        $this->penanggungJawab[$pihakMitraIndex]['pihak_penanggung_jawab']['alamat'] = Cstr::unescapeDeep($modelMitra->alamat) ?? null;
        $this->penanggungJawab[$pihakUnitIndex]['pihak_penanggung_jawab']['alamat'] = $unitData?->alamat ?? null;

        if (empty($this->edit)) {
            $this->refresh();
        }
    }

    public function updated($field, $value)
    {
        if ($field == 'record.id_unit_kerja') {
            $this->loadDefaultPihak($this->optionRadio);
        }
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
            // cek validasi model
            WebRequest::validateData($data, $this->model, $this->edit, $attributes, $this->messageValidation());

            // cek custom validasi jika ada
            $resultCustomValidate = $this->customValidationBeforeSave();

            // cek apakah bentuk kegiatan sudah dipakai oleh kegiatan lain di lingkup induk kerjasama
            $this->validateBentukKegiatan();

            // cek link dokumentasi, harus berupa valid link
            $this->validateLinkDokumentasiKegiatan();

            // cek durasi kegiatan
            $this->validateDurasiKegiatan();

            // cek pelaksana kegiatan
            $this->validatePelaksanaKegiatan();

            $this->validatePenanggungJawab();
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

            $this->saveDokumen(kerjasama: $return);
            $this->savePelaksanaKegiatan($return);
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

        return redirect()->route($this->urlInfo['module'] . '.' . $this->urlInfo['resource'] . '.show', [$return->id, 'backUrl' => $this->customBackUrl])->with('success', $message);
    }

    #[On('autocomplete')]
    public function setAutocompleteSearch(string $componentId, string $search)
    {
        if (Str::startsWith($componentId, 'form-control-record.pelaksana')) {
            $this->loadMahasiswaAutocomplete($componentId, strtolower($search));
        }
    }

    public function addPelaksanaKegiatan()
    {
        $this->record['pelaksana'][] = [];
    }

    public function removePelaksanaKegiatan($index)
    {
        unset($this->record['pelaksana'][$index]);
        $this->record['pelaksana'] = array_values($this->record['pelaksana']);
    }

    protected function validatePelaksanaKegiatan()
    {
        $baseRules = PelaksanaKegiatan::rules();
        $baseKey = "record.pelaksana.*";
        $langBaseKey = "kerjasama::pelaksana_kegiatan";
        $bentukKegiatanOptions = BentukKegiatan::options();

        foreach ($baseRules as $key => $value) {
            // mapping ulang key rulesnya
            $baseRules["$baseKey.$key"] = $value;
            // hapus rule yang tak terpakai
            unset($baseRules[$key]);
        }

        // validasi pelaksana kegiatan jika id_bentuk_kegiatan 
        // merupakan joint atau double degree
        if (
            !empty($this->record['id_bentuk_kegiatan']) &&
            $this->isBentukKegiatanJointOrDoubleDegree($this->record['id_bentuk_kegiatan'], $bentukKegiatanOptions)
        ) {
            $this->validate(
                rules: [
                    ...$baseRules,
                    "$baseKey.id_mahasiswa" => 'distinct'
                ],
                messages: [
                    'distinct' => ':attribute sudah ada sebelumnya'
                ],
                attributes: [
                    "$baseKey.id_mahasiswa" => __("$langBaseKey.id_mahasiswa"),
                    "$baseKey.program_studi" => __("$langBaseKey.program_studi"),
                    "$baseKey.informasi_tambahan" => __("$langBaseKey.informasi_tambahan"),
                ]
            );
        }
    }

    protected function validateBentukKegiatan()
    {
        [$isAlreadyPick, $bentukKegiatan] = $this->service->isBentukKegiatanAlreadyPick(
            $this->record['id_bentuk_kegiatan'],
            $this->indukKerjasama->id,
            !empty($this->edit) ? [$this->edit] : null
        );

        if (!$isAlreadyPick) {
            return;
        }

        throw \Illuminate\Validation\ValidationException::withMessages([
            'id_bentuk_kegiatan' => "Induk kerjasama sudah memiliki kegiatan dengan bentuk kegiatan " . $bentukKegiatan
        ]);
    }

    protected function savePelaksanaKegiatan($kegiatan)
    {
        // jika data tidak ada maka skip
        if (empty($this->record['pelaksana'])) {
            return;
        }

        $dataRaw = $this->record['pelaksana'];

        // save mahasiswa to DB v2, jika belum ada
        $this->mahasiswaService->syncSingleMahasiswaFromSiakadv1(array_column($dataRaw, 'id_mahasiswa'));

        // hapus pelaksana jika pelaksana tidak ada pada record 
        // (dihapus melalui fungsi removePelaksanaKegiatan)
        if (!empty($this->edit)) {
            $this->pelaksanaKegiatanService->destroyByKegiatan(
                $kegiatan->id,
                array_column($this->record['pelaksana'], 'id') ?? []
            );
        }

        foreach ($dataRaw as $pelaksana) {
            if (empty($pelaksana)) {
                return;
            }

            // ambil data mahasiswa dari nim
            $mahasiswaId = Mahasiswa::toBase()
                ->where('nim', $pelaksana['id_mahasiswa'])
                ->first(['id'])
                ->id ?? null;

            if (empty($mahasiswaId) && empty($pelaksana['id_mahasiswa'])) {
                continue;
            }

            $data = [
                ...$pelaksana,
                'id_mahasiswa' => $mahasiswaId ?? $pelaksana['id_mahasiswa'],
                'id_kegiatan' => $kegiatan->id
            ];
            if (!empty($pelaksana['id'])) {
                $this->pelaksanaKegiatanService->update($data, $pelaksana['id']);

                continue;
            }

            $this->pelaksanaKegiatanService->store($data);
        }
    }

    protected function loadMahasiswaAutocomplete(string $componentId, string $searchTerm)
    {
        $options = Mahasiswa::searchOptionV1($searchTerm, limit: 5);
        $optionsMapped = [];

        foreach ($options as $value => $label) {
            $optionsMapped[] = [
                'label' => $label,
                'value' => $value
            ];
        }

        $this->dispatch('autocompleteLoaded:' . $componentId, options: $optionsMapped);
    }

    protected function validateDurasiKegiatan()
    {
        $tanggalMulaiIndukKerjasama = Carbon::parse($this->indukKerjasama->tanggal_mulai_berlaku);
        $tanggalAkhirIndukKerjasama = Carbon::parse($this->indukKerjasama->tanggal_akhir_berlaku);

        $tanggalMulai = Carbon::parse($this->record['tanggal_mulai_berlaku']);
        $tanggalAkhir = Carbon::parse($this->record['tanggal_akhir_berlaku']);

        if (!$tanggalMulaiIndukKerjasama->lte($tanggalMulai)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'tanggal_mulai_berlaku' => __('kerjasama::kegiatan.tanggal_mulai_berlaku') . ' tidak boleh kurang dari ' . __('kerjasama::data_kerjasama.tanggal_mulai_berlaku') . ' Kerjasama'
            ]);
        }

        if (!$tanggalAkhirIndukKerjasama->gte($tanggalAkhir)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'tanggal_akhir_berlaku' => __('kerjasama::kegiatan.tanggal_akhir_berlaku') . ' tidak boleh lebih dari ' . __('kerjasama::data_kerjasama.tanggal_akhir_berlaku') . ' Kerjasama'
            ]);
        }
    }

    protected function validateLinkDokumentasiKegiatan()
    {
        // jika link dokumentasi kosong, maka skip
        if (empty($this->record['link_dokumentasi'])) {
            return;
        }

        $regexValidUrl = '/\bhttps?:\/\/[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(\/\S*)?\b/';
        if (!preg_match($regexValidUrl, $this->record['link_dokumentasi'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'link_dokumentasi' => __('kerjasama::kegiatan.link_dokumentasi') . ' harus berupa valid link'
            ]);
        }
    }

    protected function loadDefaultPihak($value)
    {
        // Only set defaults if not in edit mode

        if (empty($this->optionRadio)) {
            return;
        }

        $idMitra = $this->indukKerjasama->id_mitra ?? null;
        $idUnitKerja = $this->record['id_unit_kerja'] ?? null;
        $idUnitKerjasama = $this->indukKerjasama->id_unit_kerja ?? null;

        $jenisPihak = $value;

        if ($jenisPihak === 'unit') {
            $this->penanggungJawab[1]['pihak_penanggung_jawab']['id_pihak'] = $idUnitKerja;
            $this->penanggungJawab[1]['pihak_penanggung_jawab']['jenis_pihak'] = self::UNITKERJA;
        } elseif ($jenisPihak === 'mitra') {
            $this->penanggungJawab[1]['pihak_penanggung_jawab']['id_pihak'] = $idMitra;
            $this->penanggungJawab[1]['pihak_penanggung_jawab']['jenis_pihak'] = self::MITRA;
        }

        $this->penanggungJawab[2]['pihak_penanggung_jawab']['id_pihak'] = ($jenisPihak === 'unit') ? $idMitra : $idUnitKerja;
        $this->penanggungJawab[2]['pihak_penanggung_jawab']['jenis_pihak'] = ($jenisPihak === 'unit') ? self::MITRA : self::UNITKERJA;

        $mitra = $this->mitraOptions[$idMitra] ?? null;
        $unit = $this->unitKerjaOptions[$idUnitKerja] ?? null;
        $unit = str_replace("&nbsp;", '', $unit);
        $unitDariKerjasama = $this->unitKerjaOptions[$idUnitKerjasama] ?? null;
        $unitDariKerjasama = str_replace("&nbsp;", '', $unitDariKerjasama);

        // Cek unit kosong -> disable
        if (empty($unit)) {
            $inputUnit = [
                'disabled' => true,
                'options' => [],
                'withLabel' => false
            ];

            $inputMitra = [
                'disabled' => true,
                'options' => [],
                'withLabel' => false
            ];
        } else {
            // Jika data lengkap, lanjut ke logic seperti biasa
            if ($value == 'unit') {
                $unitOptions = [
                    $idUnitKerjasama => $unitDariKerjasama,
                    $idUnitKerja => $unit
                ];
                ksort($unitOptions);

                $mitraOptions = [
                    $idMitra => $mitra
                ];
                ksort($mitraOptions);

                $inputUnit = [
                    'disabled' => false,
                    'options' => $unitOptions,
                    'withLabel' => false
                ];

                $inputMitra = [
                    'disabled' => true,
                    'options' => $mitraOptions,
                    'withLabel' => false
                ];
            }

            if ($value == 'mitra') {
                $unitOptions = [
                    $idUnitKerja => $unit,
                    $idUnitKerjasama => $unitDariKerjasama
                ];
                ksort($unitOptions);

                $mitraOptions = [
                    $idMitra => $mitra
                ];
                ksort($mitraOptions);

                $inputUnit = [
                    'disabled' => true,
                    'options' => $mitraOptions,
                    'withLabel' => false
                ];

                $inputMitra = [
                    'disabled' => false,
                    'options' => $unitOptions,
                    'withLabel' => false
                ];
            }
        }


        $this->fieldParams['overridePenanggungJawab'][1] = $inputUnit;
        $this->fieldParams['overridePenanggungJawab'][2] = $inputMitra;
    }

    protected function defineFormFields()
    {
        $bentukKegiatanOptions = BentukKegiatan::options();
        $sasaranKinerjaOptions = SasaranKinerja::options();
        $indikatorSasaranOptions = $this->loadIndikatorSasaran();
        $indukKerjasamaOptions = $this->kerjasamaService->getIndukKerjasamaOptions();

        $sectionPelaksanaKegiatan = [];

        // filter bentuk kegiatan
        if (!empty($this->indukKerjasama) && $this?->indukKerjasama->mitra->jenis_mitra == Mitra::MITRA_NON_PERGURUAN_TINGGI) {
            foreach ($bentukKegiatanOptions as $key => $value) {
                if ($this->isBentukKegiatanJointOrDoubleDegree($key, $bentukKegiatanOptions)) {
                    unset($bentukKegiatanOptions[$key]);
                }
            }
        }

        // cek apakah bentuk kegiatan termasuk joint atau double degree
        if (
            !empty($this->record['id_bentuk_kegiatan']) &&
            $this->isBentukKegiatanJointOrDoubleDegree($this->record['id_bentuk_kegiatan'], $bentukKegiatanOptions)
        ) {

            if (empty($this->record['pelaksana'])) {
                $this->addPelaksanaKegiatan();
            }

            $sectionPelaksanaKegiatan = [
                'mahasiswa-pelaksana' => [
                    'title' => 'Mahasiswa Pelaksana',
                    'icon' => 'users',
                    'items' => [
                        ['field' => '_', 'type' => 'hidden'],
                    ]
                ],
            ];
        }

        $this->pelaksanaFields = $this->defineFormFieldPelaksanaKegiatan(count($this->record['pelaksana']));

        return [
            'informasi-kegiatan' => [
                'title' => 'Informasi Kegiatan',
                'items' => [
                    ['field' => 'id_induk_kerjasama', 'required' => true, 'wire:model.change' => 'record.id_induk_kerjasama', 'disabled' => !empty($this->queryParamIdKerjasama), 'options' => $indukKerjasamaOptions],
                    ['field' => 'nomor_dokumen', 'column' => 6],
                    ['field' => 'nomor_dokumen_mitra', 'column' => 6],
                    ['field' => 'id_unit_kerja', 'wire:model.change' => 'record.id_unit_kerja', 'options' => $this->unitKerjaOptions, 'wire:model.change' => 'record.id_unit_kerja'],
                    ['field' => 'judul_kegiatan'],
                    ['field' => 'id_mitra', 'disabled' => true],
                    ['field' => 'id_bentuk_kegiatan', 'wire:model.change' => 'record.id_bentuk_kegiatan', 'options' => $bentukKegiatanOptions],
                    ['field' => 'id_sasaran_kinerja', 'wire:model.change' => 'record.id_sasaran_kinerja', 'options' => $sasaranKinerjaOptions ?? []],
                    ['field' => 'id_indikator_sasaran', 'wire:model.change' => 'record.id_indikator_sasaran', 'disabled' => empty($indikatorSasaranOptions), 'options' => $indikatorSasaranOptions ?? []],
                    ['field' => 'tanggal_mulai_berlaku'],
                    ['field' => 'tanggal_akhir_berlaku'],
                    ['field' => 'ruang_lingkup'],
                    ['field' => 'hasil_pelaksanaan'],
                    ['field' => 'anggaran'],
                    ['field' => 'link_dokumentasi'],
                    ['field' => 'id_dokumen', 'wire:model' => 'dokumen', 'name' => 'dokumen', 'dokumenUploaded' => json_encode($this->dokumenUploaded)],
                ]
            ],
            ...$sectionPelaksanaKegiatan
        ];
    }


    protected function defineFormFieldPelaksanaKegiatan(int $num)
    {
        $fields = [];

        for ($i = 0; $i < $num; $i++) {
            $baseKey = "record.pelaksana.$i";
            $fields[$i] = [
                [
                    'field' => "$baseKey.id_mahasiswa",
                    'wire:model.change' => $baseKey . '.id_mahasiswa',
                    ...PelaksanaKegiatan::RULES["id_mahasiswa"],
                    'label' => __('kerjasama::pelaksana_kegiatan.id_mahasiswa'),
                    'queryInitial' => $this->record['pelaksana'][$i]['id_mahasiswa-label'] ?? ''
                ],
                [
                    'field' => "$baseKey.program_studi",
                    'wire:model' => $baseKey . '.program_studi',
                    ...PelaksanaKegiatan::RULES["program_studi"],
                    'label' => __('kerjasama::pelaksana_kegiatan.program_studi')
                ],
                [
                    'field' => "$baseKey.informasi_tambahan",
                    'wire:model' => $baseKey . '.informasi_tambahan',
                    ...PelaksanaKegiatan::RULES["informasi_tambahan"],
                    'label' => __('kerjasama::pelaksana_kegiatan.informasi_tambahan')
                ]
            ];
        }
        return $fields;
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
                        'disabled' => empty($this->edit),
                        'withLabel' => false
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
                    'disabled' => false,
                    'options' => ['unit' => 'Unit', 'mitra' => 'Mitra'],
                    'wire:model.change' => $baseModelKey . "pihak_penanggung_jawab.jenis_pihak"
                ];
                array_unshift($fields['pihak-ke' . $key]['items'], $override);
            }
        }

        return $fields;
    }

    public function fillPenanggungJawabFromSelectKontak($idKontak, $keyPihak, $keyPenanggungJawab)
    {

        $selectedKontak = $this->kontakMitra[$idKontak] ?? null;
        if (empty($selectedKontak)) {
            $selectedKontak = $this->kontakMitra2[$idKontak] ?? null;
            if (empty($selectedKontak)) {
                return;
            }
        }
        $this->penanggungJawab[$keyPihak]['penanggung_jawab'][$keyPenanggungJawab] = [
            'nama_penanggung_jawab' => $selectedKontak['nama_kontak'],
            'jabatan' => $selectedKontak['jabatan'],
            'email' => $selectedKontak['email'],
            'telepon' => $selectedKontak['telepon']
        ];
    }

    protected function isBentukKegiatanJointOrDoubleDegree(string $idBentukKegiatan, array $options)
    {
        $namaBentukKegiatan = $options[$idBentukKegiatan] ?? '';

        if (empty($namaBentukKegiatan)) {
            return false;
        }

        return Str::contains(
            $namaBentukKegiatan,
            ['joint degree', 'dual degree'],
            true
        );
    }

    protected function loadRecord()
    {
        parent::loadRecord();

        if (!empty($this->edit)) {
            $this->loadIndukKerjasama($this->record['id_induk_kerjasama']);
            $this->loadPelaksanaKegiatan($this->record['id']);
        }
    }

    protected function loadIndukKerjasama($idParent)
    {
        if (empty($idParent)) {
            return;
        }

        $this->indukKerjasama = $this->kerjasamaService->show($idParent);
        $this->record['id_mitra'] = $this->indukKerjasama->mitra->nama_mitra;
        // $this->loadKontakMitra($this->indukKerjasama->mitra->id);
    }

    protected function loadSasaranKinerja()
    {
        $sasaranKinerjaOptions = [];
        if (empty($this->record['id_bentuk_kegiatan'])) {
            return;
        }
        $sasaranKinerjaOptions = SasaranKinerja::options();

        return $sasaranKinerjaOptions ?? [];
    }

    protected function loadIndikatorSasaran()
    {
        $indikatorSasaranOptions = [];
        if (empty($this->record['id_sasaran_kinerja'])) {
            return;
        }
        $indikatorSasaranOptions = $this->indikatorSasaranService->getIndikatorSasaranBySasaranKineja($this->record['id_sasaran_kinerja']);

        return $indikatorSasaranOptions ?? [];
    }

    protected function loadPelaksanaKegiatan($idKegiatan)
    {
        $data = $this->pelaksanaKegiatanService->getByKegiatan($idKegiatan);
        foreach ($data as $pelaksana) {
            $this->record['pelaksana'][] = [
                ...$pelaksana,
                'id_mahasiswa' => $pelaksana['nim'],
                'id_mahasiswa-label' => $pelaksana['nim'] . ' - ' . $pelaksana['nama_mahasiswa']
            ];
        }
    }

    protected function getSuccessMessage()
    {
        return ['Kegiatan', parent::getSuccessMessage()[1]];
    }

    protected function loadKontakMitra($idMitra)
    {
        $indukKerjasama = $this->indukKerjasama;

        if (empty($indukKerjasama)) {
            return;
        }

        $pihakPenanggungJawab = $indukKerjasama->pihak_penanggung_jawab;

        // Determine model for selected and not selected
        $selectedModel = $this->optionRadio === 'unit'
            ? "Modules\Core\Models\UnitKerja"
            : "Modules\Kerjasama\Models\Mitra";
        $otherModel = $this->optionRadio === 'unit'
            ? "Modules\Kerjasama\Models\Mitra"
            : "Modules\Core\Models\UnitKerja";

        // Selected kontak
        $filteredSelected = collect($pihakPenanggungJawab)->filter(function ($item) use ($selectedModel) {
            return $item->model_pihak === $selectedModel;
        });

        $penanggungJawabMappedSelected = [];
        foreach ($filteredSelected as $pihak) {
            foreach ($pihak->penanggung_jawab as $pj) {
                $penanggungJawabMappedSelected[$pj->id] = [
                    ...$pj->only([
                        'id',
                        'nama_penanggung_jawab',
                        'email',
                        'telepon',
                        'jabatan',
                        'nip'
                    ]),
                    'nama_kontak' => $pj->nama_penanggung_jawab
                ];
            }
        }

        // Other kontak
        $filteredOther = collect($pihakPenanggungJawab)->filter(function ($item) use ($otherModel) {
            return $item->model_pihak === $otherModel;
        });

        $penanggungJawabMappedOther = [];
        foreach ($filteredOther as $pihak) {
            foreach ($pihak->penanggung_jawab as $pj) {
                $penanggungJawabMappedOther[$pj->id] = [
                    ...$pj->only([
                        'id',
                        'nama_penanggung_jawab',
                        'email',
                        'telepon',
                        'jabatan',
                        'nip'
                    ]),
                    'nama_kontak' => $pj->nama_penanggung_jawab
                ];
            }
        }

        $this->kontakMitra = $penanggungJawabMappedSelected;
        $this->kontakMitra2 = $penanggungJawabMappedOther;
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
}
