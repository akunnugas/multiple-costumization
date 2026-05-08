<?php

namespace Modules\Litabmas\Livewire;

use Modules\Core\Helpers\Page;
use Illuminate\Support\Facades\View;
use Modules\Core\Livewire\MainComponent;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Models\SumberPendanaan;

class FilterLaporanPendanaanKegiatan extends MainComponent
{
    use LitabmasViewData;

    public $permission, $fields, $records;

    public $view = 'litabmas::livewire.laporan-pendanaan-kegiatan.filter-laporan-pendanaan-kegiatan';

    public function mount()
    {
        $this->loadService();

        $this->permission = request()->permission;
        $this->urlInfo = Page::showURLInfo();
        $this->viewData = $this->defineViewData();

        $this->records = [
            'id_periode_pendanaan' => null,
            'id_sumber_pendanaan' => null,
            'id_klaster_pendanaan' => null,
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
            'field' => 'id_sumber_pendanaan',
            'label' => 'Sumber Pendanaan',
            'selected' => $this->records['id_sumber_pendanaan'],
            'options' => SumberPendanaan::where('id_periode_pendanaan', $this->records['id_periode_pendanaan'])->pluck('nama_sumber_pendanaan', 'id')->toArray(),
            'required' => true,
            'variant' => 'search',
            'wire:change' => 'changeSumberPendanaan($event.target.value)',
        ];

        $this->fields[] = [
            'field' => 'id_klaster_pendanaan',
            'label' => 'Klaster Pendanaan',
            'selected' => $this->records['id_klaster_pendanaan'],
            'options' => KlasterPendanaan::where('id_sumber_pendanaan', $this->records['id_sumber_pendanaan'])->pluck('nama_klaster', 'id')->toArray(),
            'required' => true,
            'variant' => 'search',
            'wire:change' => 'changeKlasterPendanaan($event.target.value)',
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
        $this->records['id_sumber_pendanaan'] = null;
        $this->records['id_klaster_pendanaan'] = null;

        $this->fields = [];

        $this->initFilter();
    }

    public function changeSumberPendanaan($idSumberPendanaan)
    {
        $this->records['id_sumber_pendanaan'] = !empty($idSumberPendanaan) ? $idSumberPendanaan : null;
        $this->records['id_klaster_pendanaan'] = null;

        $this->fields = [];

        $this->initFilter();
    }

    public function changeKlasterPendanaan($idKlaster)
    {
        $this->records['id_klaster_pendanaan'] = !empty($idKlaster) ? $idKlaster : null;

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
