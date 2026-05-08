<?php

namespace Modules\SPMI\Livewire;

use Illuminate\Support\Facades\Route;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Livewire\CreateEditComponent;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\IndikatorKolom;
use Modules\SPMI\Services\IndikatorKolomManagementService;

class FormIndikatorKolom extends CreateEditComponent
{
    use SpmiViewData;

    public $formType;
    public $idParent;
    public $optParent;
    public $isShowFormType = false;

    protected function defineLastBreadcrumb()
    {
        return MenuHelper::getItem(Menu::class, "indicatorSidebar.{$this->viewData['subResourceId']}.indikator-kolom", includes: true);
    }

    public function loadService()
    {
        $this->service = new IndikatorKolomManagementService();
    }

    public function loadModel()
    {
        $this->model = IndikatorKolom::class;
    }

    public function mount()
    {
        parent::mount();

        $idparent = Route::current()->parameter('indicator');
        $this->idParent = $idparent;

        $optParent = IndikatorKolom::optionsIndicatorColumn($this->idParent);
        $this->optParent = $optParent;

        $this->record['id_indikator_laporan_kinerja'] = $this->idParent;
    }

    public function beforeRender()
    {
        $this->formType = $this->record['jenis_form'] ?? null;
        if ($this->record['jenis_form'] == IndikatorKolom::DROPDOWN) {
            $this->isShowFormType = true;
        } else {
            $this->isShowFormType = false;
        }

        $this->loadData();
    }



    public function updatedFormType()
    {
        $formType = $this->selectValue($this->formType);

        if ($formType == IndikatorKolom::DROPDOWN) {
            $this->isShowFormType = true;
        } else {
            $this->isShowFormType = false;
        }


        $this->loadData();
    }

    public function save()
    {
        $this->record['jenis_kolom'] = $this->selectValue($this->record['jenis_kolom']);
        $this->record['posisi_kolom'] = $this->selectValue($this->record['posisi_kolom']);

        if (!$this->record['apakah_terlihat']) {
            $this->record['apakah_terlihat'] = 0;
        }

        parent::save();
    }

    protected function defineFormFields()
    {
        return [
            ['field' => 'nama', 'required' => true],
            ['field' => 'id_parent', 'options' => $this->optParent],
            ['field' => 'jenis_form', 'required' => false, 'options' => IndikatorKolom::FORM_TYPE, 'wire:model.change' => 'formType', 'value' => $this->formType],
            ['field' => 'option_dropdown', 'type' => ($this->isShowFormType ? 'select' : 'hidden'), 'required' => $this->isShowFormType, 'options' => IndikatorKolom::DRPROPERTIES],
            ['field' => 'properti', 'options' => IndikatorKolom::DRPROPERTIES],
            ['field' => 'jenis_kolom', 'required' => true, 'options' => IndikatorKolom::COLUMN_TYPE],
            ['field' => 'posisi_kolom', 'required' => true, 'options' => IndikatorKolom::LAYOUT_TYPE],
            ['field' => 'colspan', 'type' => 'number'],
            ['field' => 'rowspan', 'type' => 'number'],
            ['field' => 'apakah_terlihat', 'control' => 'switch', 'boolean' => true],
            ['field' => 'id_indikator_laporan_kinerja', 'type' => 'hidden']
        ];
    }
}
