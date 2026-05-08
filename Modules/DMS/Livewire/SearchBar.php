<?php

namespace Modules\DMS\Livewire;

use Livewire\Attributes\Url;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Livewire\MainComponent;
use Modules\DMS\Models\Dokumen;
use Modules\DMS\Services\DokumenManagementService;

class SearchBar extends MainComponent
{
    #[Url]
    public $search = '';
    public $data;
    public $folderId = null;

    protected $model = Dokumen::class;

    public function render()
    {
        return view('dms::livewire.search-bar');
    }

    protected function loadService()
    {
        $this->service = new DokumenManagementService();
    }

    public function mount()
    {
        parent::mount();

        $this->loadData();
    }

    public function updatedSearch()
    {
        $this->reset('data');
        $this->loadData();
    }

    public function resetSearch()
    {
        $this->reset('search');
        $this->reset('data');
    }

    protected function loadData() {
        if (empty($this->search) || strlen($this->search) < 3) {
            return;
        }

        $this->data = $this->service->indexSearch($this->search, 1, 5, [], [], $this->folderId);
    }
}
