<?php

namespace Modules\Litabmas\Livewire;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Page;
use Illuminate\Support\Facades\View;
use Modules\Core\Livewire\MainComponent;
use Modules\Litabmas\Enums\StatusAgendaKegiatanEnum;
use Modules\Litabmas\Models\PeriodePendanaan;

class FilterLaporanPengajuanProposal extends MainComponent
{
    use LitabmasViewData;

    public $permission, $fields, $records;

    public $view = 'litabmas::livewire.laporan-pengajuan-proposal.filter-laporan-pengajuan-proposal';

    public function mount()
    {

        $this->loadService();

        $this->permission = request()->permission;
        $this->urlInfo = Page::showURLInfo();
        $this->viewData = $this->defineViewData();

        $this->records = [
            'id_periode_pendanaan' => null,
            'status_agenda' => null,
            'status_seleksi' => null,
            'format' => null,
            'is_kop' => false,
        ];

        $this->initFilter();
    }

    protected function buildView($page)
    {
        View::share($this->viewData + [
            'isLivewire' => true,
            'permission' => $this->permission,
            'urlInfo' => $this->urlInfo,
            'headerClass' => 'header_position-static',
        ]);

        return view($this->view)->layout('core::components.layouts.main-outer');
    }

    public function loadService() {}

    public function render()
    {
        return $this->buildView($this->view);
    }

    public function initFilter()
    {
        $this->fields[] = [
            'field' => 'id_periode_pendanaan',
            'label' => 'Periode Pendanaan',
            'selected' => $this->records['id_periode_pendanaan'],
            'options' => PeriodePendanaan::pluck('tahun', 'id')->toArray(),
            'required' => true,
            'variant' => 'search',
            'wire:change' => 'changePeriode($event.target.value)',
        ];

        $this->fields[] = [
            'field' => 'status_agenda',
            'label' => 'Tahapan Kegiatan Seleksi',
            'selected' => $this->records['status_agenda'],
            'options' => StatusAgendaKegiatanEnum::FILTER_AGENDA,
            'required' => true,
            'wire:change' => 'changeStatusAgenda($event.target.value)',
        ];

        $this->fields[] = [
            'field' => 'status_seleksi',
            'label' => 'Status Seleksi',
            'selected' => $this->records['status_seleksi'],
            'options' => StatusAgendaKegiatanEnum::FILTER_SELEKSI,
            'wire:change' => 'changeStatusSeleksi($event.target.value)',
        ];

        $this->fields[] = [
            'field' => 'format',
            'label' => 'Format',
            'selected' => $this->records['format'],
            'options' => ['html' => 'HTML'],
            'required' => true,
            'wire:change' => 'changeFormat($event.target.value)',
        ];

        $this->fields[] = [
            'field' => 'is_kop',
            'label' => 'Gunakan Kop',
            'selected' => $this->records['is_kop'],
            'choiceLabel' => 'Menggunakan KOP Laporan',
            'control' => 'checkbox',
            'wire:model.live' => 'records.is_kop',
        ];
    }

    public function changePeriode($idPeriode)
    {
        $this->records['id_periode_pendanaan'] = !empty($idPeriode) ? $idPeriode : null;

        $this->fields = [];

        $this->initFilter();
    }

    public function changeStatusAgenda($statusAgenda)
    {
        $this->records['status_agenda'] = !empty($statusAgenda) ? $statusAgenda : null;

        $this->fields = [];

        $this->initFilter();
    }

    public function changeStatusSeleksi($statusSeleksi)
    {
        $this->records['status_seleksi'] = !empty($statusSeleksi) ? $statusSeleksi : null;

        $this->fields = [];

        $this->initFilter();
    }

    public function changeFormat($format)
    {
        $this->records['format'] = !empty($format) ? $format : null;

        $this->fields = [];

        $this->initFilter();
    }

    public function goSubmitReport()
    {
        $this->dispatch('act-form', [
            'blank' => false,
        ]);
    }

    public function goSubmitBlankReport()
    {
        $this->dispatch('act-form', [
            'blank' => true,
        ]);
    }
}
