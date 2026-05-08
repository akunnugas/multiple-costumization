<?php

namespace Modules\Core\Livewire;

use Illuminate\Support\Arr;
use Livewire\Attributes\Url;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Page;
use Modules\Core\Helpers\WebRequest;

abstract class ReferenceComponent extends ListComponent
{
    #[Url]
    public $create = '';
    #[Url]
    public $edit = '';

    public $record = [];
    public $updateURLPattern = null;

    public function save()
    {
        $error = $this->saveData();

        $this->alert = Error::showAlert($error, 'Penyimpanan ' . $this->viewData['resourceTitle']);

        $this->afterAction();
    }

    public function showCreate()
    {
        if (empty($this->permission['post'])) {
            return;
        }

        $this->create = 1;
        $this->reset(['edit', 'record']);
    }

    public function showEdit($id)
    {
        if (empty($this->permission['put'])) {
            return;
        }

        $this->edit = $id;
        $this->reset('create');

        $this->loadRecord();
    }

    public function hideForm()
    {
        $this->reset(['create', 'edit', 'record']);
    }

    public function mount()
    {
        parent::mount();

        $this->updateURLPattern = Page::detailURL('__id__');

        // hanya bisa create atau edit
        if (!empty($this->create) && !empty($this->edit)) {
            $this->reset('create');
        }
    }

    public function render()
    {
        return $this->buildView('core::livewire.templates.index-reference')
            ->withUpdateURL($this->edit ? str_replace('__id__', $this->edit, $this->updateURLPattern) : null);
    }

    protected function saveData()
    {
        if (
            (empty($this->edit) && empty($this->permission['post'])) ||
            (!empty($this->edit) && empty($this->permission['put']))
        ) {
            return new Error;
        }

        // validasi data
        $fields = WebRequest::buildFields($this->model, $this->defineHeader(), flattenFields: true);
        $data = Arr::only($this->record, array_map(fn ($item) => $item['field'], $fields));
        $attributes = Page::defineLabelByField(false, $this->urlInfo);

        WebRequest::validateData($data, $this->model, $this->edit, $attributes);

        // store atau update
        if (empty($this->edit)) {
            return $this->service->store($data);
        } else {
            return $this->service->update($data, $this->edit);
        }
    }

    protected function loadData()
    {
        parent::loadData();

        $this->loadRecord();
    }

    protected function loadRecord()
    {
        if (empty($this->edit)) {
            return;
        }

        foreach ($this->data->items as $row) {
            if ($row['id'] == $this->edit) {
                $this->record = $row;
                break;
            }
        }
    }

    protected function afterAction()
    {
        parent::afterAction();

        $this->hideForm();
    }
}
