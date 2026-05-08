<?php

namespace Modules\Core\Livewire;

use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\WebRequest;

abstract class ListComponent extends MainComponent
{
    use WithPagination;

    #[Url]
    public $filter = null;
    #[Url]
    public $perPage = null;
    #[Url]
    public $search = '';
    #[Url]
    public $sort = null;
    #[Url]
    public $sortDesc = null;

    public $data;
    public $header;
    public $order;
    public $selectFilter;

    protected $indexMethod = 'index';
    protected $model;

    public function destroy($id)
    {
        if ($this->permission['delete'] && WebRequest::validateId($id)) {
            $error = $this->service->destroy($id);
        } else {
            $error = new Error;
        }

        $this->alert = Error::showAlert($error, 'Penghapusan ' . $this->viewData['resourceTitle']);

        $this->afterAction();
    }

    #[On('destroy-checked')]
    public function destroySome($ids)
    {
        if ($this->permission['delete'] && WebRequest::validateId($ids)) {
            $error = $this->service->destroySome($ids);
        } else {
            $error = new Error;
        }

        $this->alert = Error::showAlert($error, 'Penghapusan ' . $this->viewData['resourceTitle']);

        $this->afterAction();
    }

    public function setSort($sort, $sortDesc = null)
    {
        $this->sort = $sort;

        if (empty($sortDesc)) {
            $this->reset('sortDesc');
        } else {
            $this->sortDesc = 1;
        }

        $this->resetList();
    }

    public function setFilter($key, $value)
    {
        if (!empty($value)) {
            $this->filter[$key] = $value;
        } else if (!empty($this->filter[$key])) {
            unset($this->filter[$key]);
        }

        $this->resetList();
    }

    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;

        $this->resetList();
    }

    public function resetSearch()
    {
        $this->reset('search');

        $this->resetList();
    }

    public function updatedPage()
    {
        $this->afterAction();
    }

    public function updatedSearch()
    {
        $this->resetList();
    }

    public function mount()
    {
        parent::mount();

        $this->loadData();
    }

    public function render()
    {
        return $this->buildView('core::livewire.templates.index');
    }

    protected function loadData()
    {
        $index = WebRequest::indexData($this->service, $this->model, $this->defineHeader(), $this->defineFilter(), $this->getPage(), $this->perPage, $this->sort, $this->sortDesc, $this->filter, $this->search, $this->indexMethod);

        $this->data = $index['data'];
        $this->selectFilter = $index['filter'];
        $this->header = $index['header'];
        $this->order = $index['order'];
    }

    protected function buildView($page)
    {
        return parent::buildView($page)->withAlert($this->alert)
            ->layout('core::components.layouts.main-outer');
    }

    protected function resetList()
    {
        $this->resetPage();
    }

    protected function afterAction()
    {
        $this->loadData();
    }

    protected function defineFilter()
    {
        return [];
    }

    protected function defineHeader()
    {
        return [];
    }
}
