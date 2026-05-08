<?php

namespace Modules\Admission\Livewire;

use Modules\Core\Helpers\Date;
use Modules\PMB\Models\SebaranProdi;
use Modules\PMB\Models\SyaratJenis;
use Modules\PMB\Services\SeleksiManagementService;
use Modules\PMB\Services\SebaranProdiManagementService;
use Modules\PMB\Services\PeriodePendaftaranManagementService;
use Modules\PMB\Services\SyaratPendaftaranManagementService;
use Modules\PMB\Services\SyaratJenisManagementService;

class RegistrationPathDetail extends FrontComponent
{
    protected $view = 'admission::livewire.registration-path-detail';

    public string $title;

    public $parentNav = [
        [
            'path' => 'registration-path',
            'label' => 'Jalur Pendaftaran'
        ]
    ];

    private string $registrationPathKey;
    public $data;
    public bool $isRegistrationPeriodOpen = false;
    public array $studyProgramOpt = [];
    public $selectedStudyProgram;

    public function mount()
    {
        // cek session registration path
        $return = $this->validateKey(session('admission.registration_path'));
        if (!empty($return)) {
            return $return;
        }

        // set key
        $this->registrationPathKey = session()->get('admission.registration_path');
    }

    public function render()
    {
        // load data
        $this->loadData();

        // cek apakah periode pendaftaran dibuka
        $this->isRegistrationPeriodOpen = Date::isDateInRange($this->data['rp_opened_at'], $this->data['rp_closed_at']);

        // set title
        $this->title = "{$this->data['rp_name']} - {$this->data['registration_path_name']} - {$this->data['batch_name']}";

        return $this->buildView($this->view);
    }

    public function doNextStep()
    {
        $studyProgramValue = $this->selectedStudyProgram['value'] ?? null;
        if (empty($studyProgramValue)) {
            return $this->render();
        }

        // set session pilihan prodi yg dipilih peserta
        session()->put('admission.registrant_study_program.option_1', $studyProgramValue);

        // set session for data
        session()->put('admission.registration_period_detail', $this->data);

        // redirect ke pengisian data peserta
        $this->redirectRoute('admission.registration');
    }

    private function loadData()
    {
        // explode key
        [$periodId, $batchId, $registrationPathId, $lectureSystemId, $registrationPeriodId] = explode('/', $this->registrationPathKey);

        // get data
        $this->data = (new PeriodePendaftaranManagementService())->showByRegistrationCollection(
            id: $registrationPeriodId,
            periodId: $periodId,
            batchId: $batchId,
            registrationPathId: $registrationPathId,
            lectureSystemId: $lectureSystemId
        );

        // get jenjang dari list program studi
        $studyPrograms = (new SebaranProdiManagementService())->getListByRegistrationPeriodIds([$registrationPeriodId]);
        $degrees = array_column($studyPrograms, 'degree_code', 'degree_id');
        $this->data['degrees'] = strtoupper(implode(', ', $degrees));

        // get syarat administrasi dari cache
        $requirementTypes = (new SyaratJenisManagementService())->getAll();
        $administrationType = array_filter($requirementTypes, fn($item) => $item['code'] == SyaratJenis::CODE_ADMINISTRASI);
        $administrationType = reset($administrationType); // get first key
        $this->data['administration_requirements'] = (new SyaratPendaftaranManagementService())->getByRequirementType(
            requirementTypeId: $administrationType['id'],
            registrationPeriodId: $registrationPeriodId
        );

        // get kuota
        $quota = [];
        foreach ($studyPrograms as $val) {
            $dataStudyProgram['information'] = $val['organization_name'] . ' - ' . (empty($val['program_distribution_quota'])
                ? ' Belum ada informasi'
                : $val['program_distribution_quota'] . ' Mahasiswa');
            $totalPerStudyProgram = ($quota[$val['degree_code']]['total'] ?? 0) + $val['program_distribution_quota'];

            $quota[$val['degree_code']]['study_programs'][$val['program_distribution_id']] = $dataStudyProgram;
            $quota[$val['degree_code']]['degree_name'] = $val['degree_name'];
            $quota[$val['degree_code']]['total'] = $totalPerStudyProgram;
        }

        $this->data['quotas'] = $quota;

        // get jadwal seleksi
        $this->data['assessments'] = (new SeleksiManagementService())->getByRegistrationPeriodId($registrationPeriodId);

        // load select options
        $this->loadOptions($registrationPeriodId);
    }

    private function loadOptions($registrationPeriodId)
    {
        // get sebaran prodi
        $this->studyProgramOpt = SebaranProdi::optionStudyPrograms($registrationPeriodId);
    }

    private function validateKey($key = null)
    {
        // nggk boleh kosong, kenapa di set default null? biar nggk ada error di tampilan usernya
        if (empty($key)) {
            return redirect()->route('admission.registration-path');
        }

        // harus berupa string, dan mengandung 4 slash (/)
        if (!is_string($key) || substr_count($key, '/') != 4) {
            return redirect()->route('admission.registration-path');
        }

        // pecah, lalu cek semua value harus berupa angka (karena merupakan id)
        $key = explode('/', $key);
        foreach ($key as $value) {
            if (!is_numeric($value)) {
                return redirect()->route('admission.registration-path');
            }
        }

        return null;
    }
}
