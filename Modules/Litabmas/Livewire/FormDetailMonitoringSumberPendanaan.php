<?php

namespace Modules\Litabmas\Livewire;

use Modules\Core\Helpers\Page;
use Modules\Core\Livewire\MainComponent;
use Illuminate\Support\Facades\View;
use Modules\Litabmas\Helpers\Menu;
use Modules\Litabmas\Livewire\LitabmasViewData;
use Modules\Litabmas\Services\PendanaanKegiatanService;

class FormDetailMonitoringSumberPendanaan extends MainComponent
{
    use LitabmasViewData;

    public $title, $subtitle;
    public $idSumberPendanaan;
    public $permission;
    public $menu, $data, $currentData;
    public $dataProposal;
    public $filterKlaster = 'all';

    public $view = 'litabmas::livewire.pendanaan-kegiatan.form-detail-monitoring-sumber-pendanaan';

    public function mount($id_sumber_pendanaan = null)
    {
        $this->loadService();

        $this->idSumberPendanaan = $id_sumber_pendanaan;

        $this->currentData = $this->service->show($this->idSumberPendanaan)->toArray();

        $this->menu = Menu::sidebar('pendanaan-kegiatan');

        $this->permission = request()->permission;
        $this->urlInfo = Page::showURLInfo();
        $this->viewData = $this->defineViewData();
        $this->data = $this->defineFormFields();

        $this->getTitlePage();

        $this->loadPageData();
    }

    public function updatedFilterKlaster($value)
    {
        $this->filterKlaster = !empty($value['value']) ? $value['value'] : 'all';

        $this->dataProposal = $this->service->getDaftarProposal($this->idSumberPendanaan, ($this->filterKlaster != 'all' ? $this->filterKlaster : null))->toArray();
    }

    private function getTitlePage()
    {
        $this->title = 'Detail Monitoring Pendanaan';
        $this->subtitle = 'Monitoring Pendanaan';
    }

    private function loadPageData()
    {
        $this->dataProposal = $this->service->getDaftarProposal($this->idSumberPendanaan)->toArray();
    }

    protected function buildView($page)
    {
        View::share($this->viewData + [
            'isLivewire' => true,
            'permission' => $this->permission,
            'urlInfo' => $this->urlInfo,
            'data' => $this->data,
            'headerClass' => 'header_position-static',
        ]);

        return view($this->view)->layout('core::components.layouts.main-outer');
    }

    public function loadService()
    {
        $this->service = new PendanaanKegiatanService;
    }

    public function render()
    {
        return $this->buildView($this->view);
    }

    private function defineFormFields()
    {
        // define fields
        $fields = [
            'utama' => [
                'title' => 'Informasi Utama',
                'items' => [
                    ['field' => 'periode', 'label' => 'Periode Pendanaan'],
                    ['field' => 'nama_sumber_pendanaan', 'label' => 'Sumber Pendanaan'],
                    ['field' => 'pengelola_bantuan', 'label' => 'Pengelola Pendanaan'],
                    ['field' => 'total_pendanaan', 'label' => 'Total Pendanaan', 'component' => 'detail.format_currency'],
                    ['field' => 'dana_diberikan', 'label' => 'Dana Diberikan', 'component' => 'detail.format_currency'],
                    ['field' => 'dana_tersisa', 'label' => 'Dana Tersisa', 'component' => 'detail.format_currency'],
                    ['field' => 'total_proposal', 'label' => 'Total Proposal Diterima'],
                ]
            ],
        ];

        // add data to fields
        foreach ($fields as $index => $field) {
            foreach ($field['items'] as $key => $item) {
                $fields[$index]['items'][$key]['original'] = $this->currentData[$item['field']] ?? null;
                $fields[$index]['items'][$key]['text'] = $this->currentData[$item['field']] ?? null;
            }
        }

        return $fields;
    }
}
