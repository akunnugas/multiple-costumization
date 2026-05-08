<?php

namespace Modules\SPMI\Livewire;

use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Modules\Core\Livewire\CreateEditComponent;
use Modules\Core\Models\Pegawai;
use Modules\SPMI\Models\SkAuditor;
use Modules\SPMI\Models\SuratTugasAuditor;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;
use Modules\SPMI\Services\SkAuditorManagementService;

class FormSKAuditor extends CreateEditComponent
{
    use SpmiViewData, WithFileUploads;

    public $editEmployeeId = null;

    public $selectedEmployee;
    public $employees = [];
    public $employeeOptions = [];
    public $savedEmployees = [];

    public $tempEmployeesAuditor = [];

    public function loadService()
    {
        $this->service = new SkAuditorManagementService();
    }

    public function loadModel()
    {
        $this->model = SkAuditor::class;
    }

    public function mount()
    {
        parent::mount();

        $this->renderEmployeeOptions();

        $idFilterPeriode = session()->get('filter.spmi.sk-auditor.id_audit_periode');

        if ($this->edit) {
            $idFilterPeriode = $this->model::find($this->edit)->id_audit_periode;

            $this->savedEmployees = $this->service->showEmployees($this->edit);

            $suratTugasAuditor = SuratTugasAuditor::where('id_audit_periode', $this->model::find($this->edit)->id_audit_periode)->first();

            if (!empty($suratTugasAuditor)) {
                $this->tempEmployeesAuditor = SuratTugasAuditorPegawai::where('id_surat_tugas_auditor', $suratTugasAuditor->id)->distinct()->pluck('id_personil')->toArray();
            }
        }

        $this->record['id_audit_periode'] = $idFilterPeriode;
    }

    protected function defineFormFields()
    {
        return [
            ['field' => 'id_audit_periode'],
            ['field' => 'nomor_sk', 'label' => 'Nomor SK Auditor'],
            ['field' => 'tanggal_diterbitkan', 'label' => 'Tanggal SK Diterbitkan'],
            ['field' => 'tanggal_awal_berlaku', 'label' => 'Tanggal Awal Berlaku'],
            ['field' => 'tanggal_akhir_berlaku', 'label' => 'Tanggal Akhir Berlaku'],
            ['field' => 'id_dokumen', 'label' => 'Dokumen SK Auditor'],
        ];
    }

    public function addEmployee()
    {
        // Jika sedang edit pegawai, maka save edit pegawai, dan return
        if (isset($this->editEmployeeId)) {
            $this->saveEditEmployee($this->editEmployeeId);
            $this->selectedEmployee = null;
        }

        $this->loadData();
    }

    public function editEmployee($id = null)
    {
        $this->editEmployeeId = $id;

        if (isset($id)) {
            $this->renderEmployeeOptions();
        }

        $this->loadData();
    }

    public function deleteEmployee($id)
    {
        // pengecekan simpan pegawai
        $suratTugasAuditor = SuratTugasAuditor::where('id_audit_periode', $this->model::find($this->edit)->id_audit_periode)->first();
        if (!empty($suratTugasAuditor)) {
            $this->tempEmployeesAuditor = SuratTugasAuditorPegawai::where('id_surat_tugas_auditor', $suratTugasAuditor->id)->distinct()->pluck('id_personil')->toArray();
        }
        if (in_array($id, $this->tempEmployeesAuditor)) {
            $this->throwValidationError('employee', 'Pegawai sudah ditambahkan di surat tugas auditor, tidak bisa dihapus.');
        }

        $this->savedEmployees = array_filter($this->savedEmployees, function ($item) use ($id) {
            return $item['id'] != $id;
        });

        $this->loadData();
    }

    public function saveEmployee()
    {
        // Simpan pegawai yang dipilih
        $employeeId = $this->selectedEmployee['value'] ?? null;


        // Validasi pegawai jika kosong
        if (empty($employeeId)) {
            $this->throwValidationError('employee', 'Kolom pegawai tidak boleh kosong.');
        }

        $employee = $this->employees[$employeeId] ?? null;
        if (empty($employee)) {
            return;
        }

        // Cek apakah pegawai sudah ada di list, jika sudah ada, maka return
        $isEmployeeExist = !!array_filter($this->savedEmployees, function ($item) use ($employee) {
            return $item['id'] == $employee['id'];
        });

        if ($isEmployeeExist) {
            $this->selectedEmployee = null;

            $this->throwValidationError('employee', 'Pegawai sudah ditambahkan sebelumnya, Silahkan pilih pegawai lain.');
        }

        array_push($this->savedEmployees, $employee);

        $this->selectedEmployee = null;
    }

    public function saveEditEmployee($id)
    {
        // Simpan edit pegawai yang dipilih
        $employeeId = $this->selectedEmployee['value'] ?? null;

        $employee = $this->employees[$employeeId] ?? null;
        if (empty($employee)) {
            $employee = array_filter($this->savedEmployees, function ($item) use ($id) {
                return $item['id'] == $id;
            });

            $employee = array_values($employee);
            $employee = $employee[0] ?? null;
        }

        // Cek apakah pegawai sudah ada di list, jika sudah ada, maka return
        $isEmployeeExist = !!array_filter($this->savedEmployees, function ($item) use ($employee, $id) {
            return $item['id'] == $employee['id'] && $item['id'] != $id;
        });

        if ($isEmployeeExist) {
            $this->selectedEmployee = null;

            $this->throwValidationError('employee', 'Pegawai sudah ditambahkan sebelumnya, Silahkan pilih pegawai lain.');
        }

        $this->savedEmployees = array_map(function ($item) use ($employee, $id) {
            if ($item['id'] == $id) {
                $item = $employee;
            }
            return $item;
        }, $this->savedEmployees);

        $this->editEmployeeId = null;
        $this->selectedEmployee = null;
    }

    public function save()
    {
        if (empty($this->savedEmployees)) {
            $this->throwValidationError('employee', 'Minimal 1 pegawai harus ditambahkan.');
        }

        $this->mergeData['sk_auditor_pegawai'] = array_map(function ($item) {
            return $item['id'];
        }, $this->savedEmployees);

        // check change period
        if ($this->edit) {
            $tempAuditPeriod = $this->model::find($this->edit)->id_audit_periode;
            if ($tempAuditPeriod != $this->selectValue($this->record['id_audit_periode'])) {
                if (SuratTugasAuditor::where('id_audit_periode', $tempAuditPeriod)->exists()) {
                    $this->throwValidationError('id_audit_periode', 'Perubahan data SK Auditor gagal, data masih dijadikan referensi');
                }
            }
        }

        parent::save();
    }

    private function renderEmployeeOptions()
    {
        $employees = Pegawai::optionWithPersons();
        foreach ($employees as $employee) {
            if (isset($this->employeeOptions[$employee->id])) {
                continue;
            }

            if (isset($this->employees[$employee->id])) {
                continue;
            }

            $this->employeeOptions[$employee->id]
                = "{$employee->nip} - {$employee->nama}";

            $this->employees[$employee->id] = [
                'id' => $employee->id,
                'nip' => $employee->nip,
                'nama' => $employee->nama
            ];
        }
    }

    private function throwValidationError($column, $message)
    {
        $error = ValidationException::withMessages([
            $column => $message,
        ]);

        throw $error;
    }
}
