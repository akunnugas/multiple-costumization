<?php

namespace Modules\SPMI\Livewire;

use Modules\Core\Livewire\CreateEditComponent;
use Modules\SPMI\Models\AkreditasiPeringkat;
use Modules\SPMI\Models\AkreditasiSyarat;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Services\AkreditasiSyaratManagementService;

class FormAkreditasiSyarat extends CreateEditComponent
{
    use SpmiViewData;

    public $penilaianPanduanId;
    public $statuses;
    public $matriks;

    public function loadService()
    {
        $this->service = new AkreditasiSyaratManagementService();
    }

    public function loadModel()
    {
        $this->model = AkreditasiSyarat::class;
    }

    public function beforeRender()
    {
        if (!empty($this->edit)) {
            $data = (new $this->model)->find($this->edit);

            $this->penilaianPanduanId = $data->id_penilaian_panduan;
        }

        $this->loadData();
    }

    public function updatedPenilaianPanduanId($value)
    {
        if (empty($value)) {
            $this->statuses = [];
            $this->matriks = [];
            $this->loadData();
            return;
        }
        $this->statuses = AkreditasiPeringkat::where('id_penilaian_panduan', $value)->pluck('nama_peringkat_akreditasi', 'id')->toArray();
        $this->matriks = PenilaianMatriks::where('id_penilaian_panduan', $value)->pluck('pertanyaan_penilaian', 'id')->toArray();
        $this->loadData();
    }

    protected function defineFormFields()
    {
        $fields = $this->getFields();

        if (!empty($this->edit)) {
            $data = (new $this->model)->find($this->edit);

            $this->statuses = AkreditasiPeringkat::where('id_penilaian_panduan', $data->id_penilaian_panduan)->pluck('nama_peringkat_akreditasi', 'id')->toArray();
            $fields = array_map(fn($item) => $item['field'] == 'id_akreditasi_peringkat' ? ['value' => $data->id_akreditasi_peringkat] + $item : $item, $fields);
        }

        return $fields;
    }

    protected function getFields()
    {
        $statuses = $this->statuses ?? [];
        return [
            ['field' => 'id_penilaian_panduan', 'options' => PenilaianPanduan::class, 'wire:model.change' => 'penilaianPanduanId', 'value' => $this->penilaianPanduanId],
            ['field' => 'id_penilaian_matriks', 'options' => $this->matriks ?? []],
            ['field' => 'id_akreditasi_peringkat', 'options' => $statuses],
            ['field' => 'jenis_syarat_akreditasi', 'options' => AkreditasiSyarat::TYPES],
            ['field' => 'nilai_syarat_akreditasi', 'maxlength' => 4, 'unique' => true, 'type' => 'number'],
        ];
    }
}
