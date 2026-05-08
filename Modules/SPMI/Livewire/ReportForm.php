<?php

namespace Modules\SPMI\Livewire;

use Livewire\Component;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Http\Controllers\Report\DocumentReports;
use Illuminate\Support\Arr;

class ReportForm extends Component
{
    public $period;
    public $unit;
    public $id_jadwal_audit;
    public $type;

    public $record = [
        'using_kop' => false,
    ];

    public $listPeriod = [];
    public $listUnit = [];
    public $listJadwalAudit = [];
    public $listType = [];

    public function mount()
    {
        $this->listPeriod = AuditPeriode::orderBy('tahun_audit', 'desc')->pluck('tahun_audit', 'id')->toArray();
        $this->listUnit = UnitKerja::optionByType([UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI], isOnlyActive: true);
        $this->listType = (new DocumentReports())->listReport();

        // Set default values
        $this->period = array_key_first($this->listPeriod);
        $this->unit = array_key_first($this->listUnit);

        $this->updateJadwalAudit();
    }

    public function updatedPeriod($value)
    {
        $this->period = $this->selectValue($value);
        $this->updateJadwalAudit();
    }

    public function updatedUnit($value)
    {
        $this->unit = $this->selectValue($value);
        $this->updateJadwalAudit();
    }

    public function updatedIdJadwalAudit($value)
    {
        $this->id_jadwal_audit = $this->selectValue($value);
    }

    public function updatedType($value)
    {
        $this->type = $this->selectValue($value);
    }

    protected function selectValue($value)
    {
        if (is_array($value) && array_key_exists('value', $value)) {
            return $value['value'];
        }

        return $value ?? null;
    }

    protected function updateJadwalAudit()
    {
        if ($this->period && $this->unit) {
            $this->listJadwalAudit = JadwalAudit::where('id_audit_periode', $this->period)
                ->whereHas('organizations', function ($query) {
                    $query->where('core.unit_kerja.id', $this->unit);
                })
                ->orderBy('nama_jadwal_audit', 'asc')
                ->pluck('nama_jadwal_audit', 'id')
                ->toArray();
        } else {
            $this->listJadwalAudit = [];
        }

        // Reset selected ID if not in list
        if (!isset($this->listJadwalAudit[$this->id_jadwal_audit])) {
            $this->id_jadwal_audit = null;
        }

    }

    protected function defineFormFields()
    {
        return [
            [
                'field' => 'period',
                'name' => 'period',
                'label' => 'Periode AMI',
                'type' => 'select',
                'options' => $this->listPeriod,
                'required' => true,
                'value' => $this->period,
                'wire:model.live' => 'period',
                'wire:key' => 'field-period'
            ],
            [
                'field' => 'unit',
                'name' => 'unit',
                'label' => 'Unit Kerja',
                'type' => 'select',
                'variant' => 'search',
                'options' => $this->listUnit,
                'required' => true,
                'value' => $this->unit,
                'wire:model.live' => 'unit',
                'wire:key' => 'field-unit'
            ],
            [
                'field' => 'id_jadwal_audit',
                'name' => 'id_jadwal_audit',
                'label' => 'Nama Kegiatan AMI',
                'type' => 'select',
                'options' => $this->listJadwalAudit,
                'placeholder' => 'Pilih Nama Kegiatan AMI',
                'required' => true,
                'value' => $this->id_jadwal_audit,
                'wire:model.live' => 'id_jadwal_audit',
                'wire:key' => 'field-jadwal-' . count($this->listJadwalAudit)
            ],
            [
                'field' => 'type',
                'name' => 'type',
                'label' => 'Jenis Laporan',
                'type' => 'select',
                'options' => $this->listType,
                'placeholder' => 'Pilih Jenis Laporan',
                'required' => true,
                'value' => $this->type,
                'wire:model.live' => 'type',
                'wire:key' => 'field-type'
            ],
            [
                'field' => 'using_kop',
                'name' => 'using_kop',
                'label' => 'Gunakan Kop',
                'control' => 'checkbox',
                'choiceLabel' => 'Menggunakan KOP Laporan',
                'value' => $this->record['using_kop'],
                'wire:model.live' => 'record.using_kop',
                'wire:key' => 'field-kop'
            ],
        ];
    }

    public function render()
    {
        return view('spmi::pages.reports.report-form', [
            'fields' => $this->defineFormFields()
        ]);
    }
}
