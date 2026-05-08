<?php

namespace Modules\SPMI\Livewire;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Core\Helpers\Error;
use Modules\Core\Livewire\CreateEditComponent;
use Modules\Core\Models\UnitKerja;
use Modules\Gate\Models\Modul;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\AkreditasiStandar;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Models\JadwalAuditUnit;
use Modules\SPMI\Models\JenisStandar;
use Modules\SPMI\Services\JenisStandarManagementService;

class FormJenisStandart extends CreateEditComponent
{
    use SpmiViewData;

    public $butirStandar = [];
    public $kode_butir = '';
    public $nama_butir = '';
    public $editIndex = null;
    public $editKodeButir = '';
    public $editNamaButir = '';
    public $viewOnly = false; // Tambahkan properti ini

    public function loadModel()
    {
        $this->model = JenisStandar::class;
    }

    public function loadService()
    {
        $this->service = new JenisStandarManagementService();
    }

    public function mount()
    {
        parent::mount();

        // Cek jika ada parameter viewOnly dari route atau request
        $this->viewOnly = request()->route()->getName() === 'spmi.jenis-standar.show';

        if ($this->edit) {
            $this->record['id'] = $this->edit;
            $this->butirStandar = AkreditasiStandar::where('id_jenis_standar', $this->edit)
                ->get()
                ->sortBy('kode_standar', SORT_NATURAL)
                ->values()
                ->map(function ($item) {
                    return [
                        'kode_butir' => $item->kode_standar,
                        'nama_butir' => $item->nama_standar,
                    ];
                })->toArray();
        }
    }

    protected function defineFormFields()
    {
        return [
            ['field' => 'kode_jenis_standar', 'label' => 'Kode Standar', 'maxlength' => 5, 'disabled' => $this->viewOnly],
            ['field' => 'nama_jenis_standar', 'label' => 'Nama Jenis Standar', 'disabled' => $this->viewOnly],
            ['field' => 'apakah_data_default', 'type' => 'hidden'],
        ];
    }

    public function addButir()
    {
        if ($this->viewOnly) return;

        $this->validate([
            'kode_butir' => 'required|max:5',
            'nama_butir' => 'required|max:255',
        ]);

        // Cek duplikat kode_butir
        foreach ($this->butirStandar as $butir) {
            if ($butir['kode_butir'] === $this->kode_butir) {
                throw ValidationException::withMessages([
                    'kode_butir' => 'Kode Butir Standar sudah digunakan.',
                ]);
            }
        }

        $this->butirStandar[] = [
            'kode_butir' => $this->kode_butir,
            'nama_butir' => $this->nama_butir,
        ];

        $this->resetInput();
    }

    // Inline edit
    public function startInlineEdit($index)
    {
        if ($this->viewOnly) return;

        $this->resetValidation();

        $this->editIndex = $index;
        $this->editKodeButir = $this->butirStandar[$index]['kode_butir'];
        $this->editNamaButir = $this->butirStandar[$index]['nama_butir'];
    }

    public function saveInlineEdit()
    {
        if ($this->viewOnly) return;

        $this->validate([
            'editKodeButir' => 'required',
            'editNamaButir' => 'required',
        ]);

        // Cek duplikat kode_butir selain index yang sedang diedit
        foreach ($this->butirStandar as $i => $butir) {
            if ($i !== $this->editIndex && $butir['kode_butir'] === $this->editKodeButir) {
                throw ValidationException::withMessages([
                    'editKodeButir' => 'Kode Butir Standar sudah digunakan.',
                ]);
            }
        }

        $this->butirStandar[$this->editIndex] = [
            'kode_butir' => $this->editKodeButir,
            'nama_butir' => $this->editNamaButir,
        ];

        $this->cancelInlineEdit();
    }

    public function cancelInlineEdit()
    {
        if ($this->viewOnly) return;

        $this->editIndex = null;
        $this->editKodeButir = '';
        $this->editNamaButir = '';
    }

    public function deleteButir($index)
    {
        if ($this->viewOnly) return;

        unset($this->butirStandar[$index]);
        $this->butirStandar = array_values($this->butirStandar);
        $this->cancelInlineEdit();
    }

    public function resetInput()
    {
        $this->kode_butir = '';
        $this->nama_butir = '';
    }

    public function save()
    {
        if ($this->viewOnly) return;

        $response = $this->service->storeWithStandart($this->record, $this->butirStandar, $this->edit ? false : true);
        if ($response instanceof Error) {
            return redirect()->back()->with('error', $response->message);
        }

        return redirect()->route('spmi.jenis-standar.index')->with('success', 'Jenis Standar dan Butir Standar berhasil disimpan.');
    }
}
