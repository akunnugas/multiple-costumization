<?php

namespace Modules\Admission\Livewire;

use Illuminate\Support\Arr;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\Core\Services\PeriodeAkademikManagementService;
use Modules\PMB\Models\Pendaftar;
use Modules\PMB\Services\KontenManagementService;
use Modules\PMB\Services\PendaftarManagementService;

class HomeSingle extends FrontComponent
{
    protected $view = 'admission::livewire.home-single';

    public string $title = 'Detail Jalur Pendaftaran';

    public $content = [];
    public $record = [];
    public $alert;
    public $data;

    public $accreditation_list = UnitKerja::ACCREDITATION_LIST;

    protected $model = Pendaftar::class;

    public $parentNav = [];

    public array $formFields;

    public function mount()
    {
        $this->loadContent();
    }

    /**
     * Render adalah fungsi yang akan dijalankan setiap komponen dipanggil
     *
     * @return mixed
     */
    public function render()
    {
        $this->loadData();
        return $this->buildView($this->view);
    }

    protected function buildView($pageView)
    {
        return view($pageView)->layout('admission::layouts.front-single', [
            'title' => $this->title,
        ]);
    }

    protected function loadData()
    {
        $create = WebRequest::createData($this->defineFormFields(), $this->model, []);
        $this->data = $create['data'];
        $this->initForm();
    }

    public function save()
    {

        $fields = WebRequest::buildFields($this->model, $this->defineFormFields(), flattenFields: true);
        $data = Arr::only($this->record, array_map(fn($item) => $item['field'], $fields));

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $value['value'] ?? $value;
            }
        }

        try {
            WebRequest::validateData($data, $this->model);
        } catch (\Exception $e) {
            throw $e;
        }

        $activePeriod = (new PeriodeAkademikManagementService)->getActivePeriod();

        $data['period_id'] = $activePeriod;

        $return = (new PendaftarManagementService)->store($data);

        if (Error::isError($return)) {
            // Jika error, maka kembalikan alert
            $this->alert = [
                'type' => 'error',
                'message' => $return->message,
            ];

            return $this->loadData();

        }

        $this->alert = [
            'type' => 'success',
            'message' => 'Pendaftaran berhasil. Silahkan cek email anda.',
        ];

        return $this->loadData();

    }
    private function defineFormFields()
    {
        return [
            ['field' => 'name', 'required' => true],
            ['field' => 'phone_number', 'required' => true],
            ['field' => 'email', 'required' => true],
            ['field' => 'desired_program_id', 'required' => true,
                'options' => UnitKerja::optionByType([UnitKerja::STUDY_PROGRAM])],
        ];
    }

    private function initForm()
    {
        // Menambahkan wire:model
        foreach ($this->data as $k => $item) {
            $this->data[$k] = [...$item, 'wire:model' => 'record.' . $item['field']];

            // Jika field tidak ada di record maka tambahkan dengan nilai null
            if (!array_key_exists($item['field'], $this->record)) {
                $this->record[$item['field']] = null;
            }

            // Jika options seperti select, maka pertahankan value yang dipilih ketika rerender
            if (isset($item['options'])) {
                $record = $this->record[$item['field']] ?? null;
                $record = isset($record['value']) ? $record['value'] : $record;

                // Jika valuenya tidak array atau multiple maka pertahankan value yang dipilih ketika rerender
                if (isset($record)) {
                    $this->data[$k] = array_merge($this->data[$k], ['selected' => $record, 'value' => $record]);
                }
            }
        }
    }
    protected function loadContent()
    {
        $this->content['programs'] = (new UnitKerjaManagementService)->getProgramsWithImage();
        $this->content['facilities'] = (new KontenManagementService)->getFacilityContents();
        $this->content['alumni'] = (new KontenManagementService)->getAlumniContents();
    }

}
