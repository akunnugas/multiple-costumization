<?php

namespace Modules\Admission\Livewire;

use Illuminate\Support\Str;
use Modules\Admission\Services\RegistrationService;
use Modules\Core\Helpers\Date;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Models\JenisInstitusi;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\Wilayah;
use Modules\Core\Models\Sekolah;
use Modules\PMB\Models\SebaranProdi;

class Registration extends FrontComponent
{
    protected $view;

    // default active page
    public string $activePage;
    private array $listPage = ['form', 'preview'];
    private array $dataFields;

    public string $title;
    public string $subtitle;

    public array $parentNav = [];
    public int $stepPercentage;

    public array $period;

    public array $record = [];
    public $data;

    // [Start] options
    public array $countriesOpt = [];
    public array $provincesOpt = [];
    public array $institutionTypesOpt = [];
    public array $graduationYearsOpt = [];
    public $selectedProvince;
    public $selectedCity;
    public $selectedInstitutionType;
    // [End] options

    public array $alert = [];

    public function mount()
    {
        $return = $this->checkAccess();
        if ($return) {
            return $return;
        }

        $this->loadOptions();
    }

    private function checkAccess()
    {
        // check jalur periode pendaftaran yg dipilih user by session, kalo kosong arahkan ke halaman jalur pendaftaran
        if (empty(session('admission.registration_period_detail'))) {
            return redirect()->route('admission.registration-path');
        }

        // cek active page
        $activePage = session('admission.registration_active_page');
        if ($activePage == 'preview') {
            // jika data peserta kosong, arahkan ke halaman form
            if (empty(session('admission.registrant_candidate'))) {
                session()->put('admission.registration_active_page', 'form');
                return redirect()->route('admission.registration');
            }

            // jika registration_path berbeda, artinya beda periode pendaftaran yang dipilih
            if (session('admission.old_registration_path') != session('admission.registration_path')) {
                session()->put('admission.registration_active_page', 'form');
                session()->put('admission.old_registration_path', session('admission.registration_path'));
                return redirect()->route('admission.registration');
            }
        }

        // set active page from session
        $this->activePage = $activePage ?? 'form';
    }

    private function loadData()
    {
        // get informasi periode pendaftaran yang dipilih
        $this->period = session('admission.registration_period_detail');

        // get data peserta, dibutuhkan ketika di halaman preview
        $registrantCandidate = session('admission.registrant_candidate') ?? [];
        if ($this->activePage == 'form') {
            // get fields
            $this->defineFormFields();

            $this->data = WebController::buildFormCard(
                $this->dataFields,
                [Biodata::class],
                $registrantCandidate,
                true
            );
        } elseif ($this->activePage == 'preview') {
            // make array $data flatten
            $flattenData = [];
            foreach ($registrantCandidate as $fields) {
                foreach ($fields as $key => $value) {
                    $flattenData[$key] = $value;
                }
            }

            // get fields
            $this->defineFormFields();

            // get validation rules from models
            $cards = WebRequest::buildFields(Biodata::class, $this->dataFields);

            // get value
            $this->data = WebController::processCards($cards, $flattenData);
        }

        $this->loadOldData();
        $this->initForm();
        $this->loadRecord();
    }

    private function loadOptions()
    {
        $lastGraduationYear = $this->period['rp_last_graduation_year'] ?? null;

        $this->countriesOpt = Wilayah::optionsByLevel(Wilayah::LEVEL_COUNTRY);
        $this->provincesOpt = Wilayah::optionsByLevel(Wilayah::LEVEL_PROVINCE);
        // TODO: tipe institusi belum ngecek RPL
        $this->institutionTypesOpt = JenisInstitusi::optionNonUniversity();
        $this->graduationYearsOpt = Date::getYearOptions($lastGraduationYear);
    }

    public function render()
    {
        // set active page from session
        $this->activePage = session('admission.registration_active_page', 'form');

        // set label, dll sesuai active page
        $this->setComponentActivePage();

        // set form fields and data
        $this->loadData();

        $this->dispatch('hide-loading');

        // display view
        return $this->buildView($this->view);
    }

    private function setComponentActivePage()
    {
        if ($this->activePage == 'form' ||
            !in_array($this->activePage, $this->listPage) // jika active page bukan salah satu dari accepted page
        ) {
            $this->title = 'Formulir Pendaftaran';
            $this->subtitle = 'Lengkapi data pendaftaran untuk melanjutkan ke tahap selanjutnya.';
            $this->stepPercentage = 50;
            $this->view = 'admission::livewire.registration-process.form';
        } elseif ($this->activePage == 'preview') {
            $this->title = 'Pratinjau Pendaftaran';
            $this->subtitle = 'Silakan periksa kembali data pendaftaran yang sudah kamu masukkan, pastikan data yang kamu inputkan sudah benar.';
            $this->stepPercentage = 75;
            $this->view = 'admission::livewire.registration-process.preview';
        }
    }

    // [Start] Registration Form Page
    public function doProcessPreviewData()
    {
        $this->dispatch('show-loading');

        // get value dari record dan dikelompokkan berdasarkan form
        $registrantCandidateData = [];
        foreach ($this->data as $formKey => $formField) {
            foreach ($formField['items'] as $item) {
                $value = (isset($item['options']) && !empty($this->record[$item['field']]['value']))
                    ? $this->record[$item['field']]['value']
                    : Str::upper($this->record[$item['field']]);

                $registrantCandidateData[$formKey][$item['field']] = $value;
            }
        }

        // set session
        session()->put('admission.registrant_candidate', $registrantCandidateData);

        // TODO: validasi dan proses data
        // validasi RPL, nik, country_id, school_id, dll.

        // set active page
        session()->put('admission.registration_active_page', 'preview');
        $this->activePage = 'preview';

        $this->render();
    }

    public function onChangedProvince($value)
    {
        $this->selectedProvince = $value;
        $this->record['province_id'] = $value;

        if (empty($value)) {
            $this->selectedCity = '';
            $this->record['city_id'] = '';
            $this->record['school_id'] = '';

            // TODO: belum bisa disable
            $this->dispatch('disable-city');
        }
    }

    public function onChangedCity($value)
    {
        $this->selectedCity = $value;

        if (empty($value)) {
            $this->record['school_id'] = '';

            // TODO: belum bisa disable
            $this->dispatch('disable-school');
        }
    }

    public function onChangedInstitutionType($value)
    {
        $this->selectedInstitutionType = $value;
    }
    // [End] Registration Form Page

    // [Start] Registration Preview Page
    public function doSubmitPreview()
    {
        $this->dispatch('show-loading');

        $registrantCandidate = session('admission.registrant_candidate')['data_registrant'];
        $registrationPathKey = session('admission.registration_path');
        $registrationPeriod = $this->period;
        [$periodId, $batchId, $registrationPathId, $lectureSystemId, $registrationPeriodId] = explode('/', $registrationPathKey);

        // TODO: process data berbagai pengecekan di pembayaran.php

        // [Start] save pendaftar
        $registrationService = new RegistrationService();
        $registrantCandidate['period_id'] = $periodId;
        $registrantCandidate['registration_period_id'] = $registrationPeriodId;
        $registrantCandidate['active_at'] = $registrationPeriod['rp_is_paid'] ? null : now();
        // cek user by email
        [$user, $person, $registrant, $error] = $registrationService->save($registrantCandidate, $registrationPeriod);
        if (Error::isError($error)) { // jika error
            $this->alert = [
                'type' => 'error',
                'message' => $error->message,
            ];

            $this->dispatch('hide-loading');

            return $this->render();
        }
        // [End] save pendaftar

        // unset all session from admission.*
        session()->forget('admission');

        // redirect to success page
        return redirect()->route('admission.registration-success');
    }
    public function doChangeData()
    {
        $this->dispatch('show-loading');

        // set active page
        session()->put('admission.registration_active_page', 'form');
        $this->activePage = 'form';

        $this->render();
    }
    // [End] Registration Preview Page

    private function loadOldData()
    {
        // set old record to value $this->data
        foreach ($this->data as $key1 => $fields) {
            foreach ($fields['items'] as $key2 => $item) {
                if (!empty($this->record[$item['field']])) {
                    $this->data[$key1]['items'][$key2]['value'] = $this->record[$item['field']];
                }
            }
        }
    }

    private function initForm()
    {
        foreach ($this->data as $key1 => $fields) {
            foreach ($fields['items'] as $key2 => $item) {
                // Menambahkan wire:model
                $this->data[$key1]['items'][$key2] = [...$item, 'wire:model' => 'record.' . $item['field']];

                // jika field tidak ada di record maka tambahkan dengan nilai null
                if (!array_key_exists($item['field'], $this->record)) {
                    $this->record[$item['field']] = null;
                }

                // jika options seperti select, maka pertahankan value yang dipilih ketika rerender
                // TODO: Sementara masih menggunakan cara untuk mengakali choices
                if (isset($item['options'])) {
                    $record = $this->record[$item['field']] ?? null;
                    $record = $record['value'] ?? $record ?? $item['selected'] ?? null;

                    // Jika valuenya tidak array atau multiple maka pertahankan value yang dipilih ketika rerender
                    if (isset($record)) {
                        $mergeData = array_merge($this->data[$key1]['items'][$key2], ['selected' => $record, 'value' => $record]);
                        $this->data[$key1]['items'][$key2] = $mergeData;
                    }
                }
            }
        }
    }

    private function loadRecord()
    {
        foreach ($this->data as $fields) {
            foreach ($fields['items'] as $item) {
                if (isset($item['options'])) {
                    $this->record[$item['field']] = $item['value'] ?? $item['selected'] ?? $item['original'] ?? null;
                } else {
                    $this->record[$item['field']] = $item['value'] ?? $item['text'] ?? null;
                }
            }
        }
    }

    private function defineFormFields()
    {
        $this->dataFields = [
            'data_registrant' => ['title' => 'Informasi Pribadi',
                'items' => [
                    ['field' => 'name', 'label' => __('admission::registration.name'), 'required' => true,
                        'autocomplete' => 'off'],
                    ['field' => 'gender', 'label' => __('admission::registration.gender'),
                        'control' => 'radio', 'required' => true],
                    ['field' => 'phone_number', 'label' => __('admission::registration.phone_number'),
                        'placeholder' => '0812xxxxxxxx', 'required' => true],
                    ['field' => 'email', 'label' => __('admission::registration.email'),
                        'placeholder' => 'email@domain.com', 'required' => true, 'autocomplete' => 'email'],
                    ['field' => 'birth_date', 'label' => __('admission::registration.birth_date'),
                        'required' => true],
                    ['field' => 'birth_place', 'label' => __('admission::registration.birth_place'),
                        'required' => true],
                    ['field' => 'country_id', 'label' => __('admission::registration.country_id'),
                        'options' => $this->countriesOpt, 'required' => true],
                    ['field' => 'nik', 'label' => __('admission::registration.nik'), 'required' => true],
                ],
            ],
            'data_school' => ['title' => 'Asal Sekolah',
                'items' => [
                    ['field' => 'province_id', 'label' => __('admission::registration.province_id'), 'required' => true,
                        'options' => $this->provincesOpt, 'id' => 'province_id', 'no_class_default' => true,
                        'wire:change' => 'onChangedProvince($event.target.value)'],
                    ['field' => 'city_id', 'label' => __('admission::registration.city_id'), 'required' => true,
                        'options' => [], 'id' => 'city_id', 'no_class_default' => true,
                        'wire:change' => 'onChangedCity($event.target.value)'],
                    ['field' => 'institution_type_id', 'label' => __('admission::registration.institution_type_id'),
                        'options' => $this->institutionTypesOpt, 'required' => true,
                        'wire:change' => 'onChangedInstitutionType($event.target.value)'],
                    ['field' => 'school_id', 'label' => __('admission::registration.npsn'), 'required' => true,
                        'options' => [], 'id' => 'school_id', 'no_class_default' => true],
                    ['field' => 'graduation_year', 'label' => __('admission::registration.graduation_year'),
                        'required' => true, 'options' => $this->graduationYearsOpt],
                ],
            ],
            'data_program' => ['title' => 'Pilihan Program Studi',
                'items' => [],
            ],
        ];

        $this->setDataSchoolItems(); // set data school items (option utk city_id dan school_id)
        $this->setDataProgramItems(); // data program items
    }

    private function setDataSchoolItems()
    {
        $registrantSchoolCandidate = session('admission.registrant_candidate')['data_school'] ?? null;
        $dataSchoolItems = $this->dataFields['data_school']['items'];
        $provinceId = $this->selectedProvince ?? $registrantSchoolCandidate['province_id'] ?? null;
        $cityId = $this->selectedCity ?? $registrantSchoolCandidate['city_id'] ?? null;

        // kalo province_id udah ada, set city options by province_id
        $fieldCityKey = array_search('city_id', array_column($dataSchoolItems, 'field'));
        if (!empty($provinceId)) {
            $dataSchoolItems[$fieldCityKey]['options'] = Wilayah::optionsByLevel(
                level: Wilayah::LEVEL_CITY,
                parentId: $provinceId
            );
        }

        // kalo city_id udah ada, set school options by city_id
        $fieldSchoolKey = array_search('school_id', array_column($dataSchoolItems, 'field'));
        if (!empty($cityId)) {
            $dataSchoolItems[$fieldSchoolKey]['options'] = Sekolah::optionsByCity(
                $cityId
            );
        }

        $this->dataFields['data_school']['items'] = $dataSchoolItems;
    }

    private function setDataProgramItems()
    {
        // jumlah pilihan program studi
        $amountOfProgram = $this->period['rp_amount_of_program_options'] ?? 1;
        $amountOfRequiredProgram = $this->period['rp_amount_of_required_options_required'] ?? 1;
        // pilihan peserta sebelumnya
        $registrantCandidateStudyProgramOpt = session('admission.registrant_study_program');

        // daftar sebaran program studi
        $studyProgramOpt = SebaranProdi::optionStudyPrograms(
            registrationPeriodId: $this->period['registration_period_id'],
            institutionTypeId: $this->selectedInstitutionType,
            rawResult: true
        );
        $studyProgramOptIndexed = [];
        foreach ($studyProgramOpt as $val) {
            $studyProgramOptIndexed[$val->option_number][$val->key] = $val->value;
        }

        // looping sebanyak jumlah pilihan program studi
        for ($i = 1; $i <= $amountOfProgram; $i++) {
            // set options berdasarkan sebaran program studi
            $this->dataFields['data_program']['items'][] = [
                'field' => 'option_' . $i,
                'label' => __('admission::registration.option_' . $i),
                'options' => $studyProgramOptIndexed[$i] ?? [],
                'selected' => $registrantCandidateStudyProgramOpt['option_' . $i] ?? null,
            ];
        }

        // set option mana aja yg required sesuai setingan
        for ($i = 1; $i <= $amountOfRequiredProgram; $i++) {
            $this->dataFields['data_program']['items'][$i - 1]['required'] = true;
        }
    }
}
