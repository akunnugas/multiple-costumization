<?php

namespace Modules\Litabmas\Livewire;

use Modules\Core\Livewire\MainComponent;
use Modules\Litabmas\Services\KlasterPendanaanService;
use Modules\Litabmas\Services\PengajuanPendanaanService;

class PengumumanKlaster extends MainComponent
{
    protected $service;
    public $klasters;
    public $limit = 99; // fix to pagination
    public $filterJenisKlaster;
    public $filterSearch;
    public $listIdProposalDiajukan;

    public function mount()
    {
        $this->loadService();

        // filter jenis klaster
        if (request()->has('jenis_pendanaan') && in_array(request()->jenis_pendanaan, ['pengabdian_masyarakat', 'penelitian'])) {
            $this->filterJenisKlaster = request()->jenis_pendanaan;
        }

        $this->loadData();

        parent::mount();
    }

    protected function loadService()
    {
        $this->service = new KlasterPendanaanService();
    }

    public function loadData()
    {
        $serviceProposal = new PengajuanPendanaanService();

        $this->listIdProposalDiajukan = $serviceProposal->getListIdKlasterProposalDiajukan(auth()->user()->biodata->id);

        $this->klasters = $this->service->getListPengumumanKlaster($this->limit, $this->filterSearch, $this->filterJenisKlaster);
    }

    public function updatedFilterJenisKlaster()
    {
        $value = $this->selectValue($this->filterJenisKlaster);

        $this->filterJenisKlaster = $value;

        $this->loadData();
    }

    public function updatedFilterSearch()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('litabmas::livewire.pengumuman-klaster.index');
    }

    protected function selectValue($value)
    {
        if (is_array($value) && array_key_exists('value', $value)) {
            return $value['value'];
        }

        return $value ?? null;
    }
}
