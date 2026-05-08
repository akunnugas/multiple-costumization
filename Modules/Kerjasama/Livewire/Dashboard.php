<?php

namespace Modules\Kerjasama\Livewire;

use Modules\Core\Livewire\MainComponent;
use Modules\Core\Models\UnitKerja;
use Modules\Kerjasama\Livewire\ViewData;
use Modules\Kerjasama\Services\KerjasamaManagementService;
use Modules\SPMI\Models\AuditPeriode;
use Modules\Kerjasama\Services\DashboardService;
use Illuminate\Support\Facades\View;
use Modules\Kerjasama\Models\StatusKerjasama;

class Dashboard extends MainComponent
{
    use ViewData;

    /** @var array[] */
    public array $widgetCount = [
        [
            'label' => '',
            'sublabel' => '',
            'icon' => '',
            'color' => '',
            'value' => 0
        ]
    ];

    public $jenisDokumenCount = [];

    public $bentukKegiatanCount = [];

    public $unitKerjaCount = [];

    public $jenisMitra = [];

    public $lingkupMitra = [];

    public $kriteriaMitra = [];

    public $provinsi = [];

    public $implementasiKegiatan = [];

    public $implementasiKerjasama = [];


    protected $service;

    public $view = 'kerjasama::livewire.dashboard.index';
    public $title, $subtitle;

    public function loadService()
    {
        $this->service = new DashboardService();
    }

    /**
     * Create KerjasamaManagementService when needed
     * Instead of storing it as a property
     */
    private function getKerjasamaService()
    {
        return new KerjasamaManagementService();
    }

    public function mount()
    {
        $this->loadService();
        $this->widgetCount = $this->getDashboardStat();
        $this->jenisDokumenCount = $this->service->countJenisDokumen();
        $this->bentukKegiatanCount = $this->service->countGroupedByJenisAndBentukKegiatan();
        $this->unitKerjaCount = $this->service->mostUnitKerja();
        $this->jenisMitra = $this->service->pieProfilMitraKerjasama();
        $this->lingkupMitra = $this->service->pieRuangLingkupMitra();
        $this->kriteriaMitra = $this->service->mostKriteriaMitra();
        $this->provinsi = $this->service->provinsiSebaranMitra();
        $this->implementasiKegiatan = $this->service->implementasiKegiatan();
        $this->implementasiKegiatan = [
            ['label' => 'Dengan Hasil Pelaksanaan', 'jumlah' =>  $this->implementasiKegiatan['with_hasil_pelaksanaan'] ?? 0],
            ['label' => 'Tanpa Hasil Pelaksanaan', 'jumlah' =>  $this->implementasiKegiatan['without_hasil_pelaksanaan'] ?? 0],
        ];

        $this->implementasiKerjasama = $this->service->implementasiKerjasama();
         $this->implementasiKerjasama = [
            ['label' => 'Dengan Hasil Pelaksanaan', 'jumlah' =>  $this->implementasiKerjasama['with_hasil_pelaksanaan'] ?? 0],
            ['label' => 'Tanpa Hasil Pelaksanaan', 'jumlah' =>  $this->implementasiKerjasama['without_hasil_pelaksanaan'] ?? 0],
        ];
        parent::mount();
    }

    public function getDashboardStat()
    {
        $countData = $this->getKerjasamaService()->getCountByStatus();

        $widgetCount = [
            [
                'label' => StatusKerjasama::AKTIF,
                'sublabel' => "Kerjasama sedang berjalan dan masih berlaku.",
                'icon' => 'check-square-broken',
                'color' => 'success',
                'value' => $countData[StatusKerjasama::AKTIF] ?? 0
            ],
            [
                'label' => StatusKerjasama::PERPANJANG,
                'sublabel' => "Kerjasama sedang dalam proses perpanjangan.",
                'icon' => 'clock-refresh',
                'color' => 'primary',
                'value' => $countData[StatusKerjasama::PERPANJANG] ?? 0
            ],
            [
                'label' => StatusKerjasama::KADALUWARSA,
                'sublabel' => "Kerjasama telah melewati masa berlaku dan belum diperpanjang.",
                'icon' => 'alert-triangle',
                'color' => 'warning',
                'value' => $countData[StatusKerjasama::KADALUWARSA] ?? 0
            ],
            [
                'label' => StatusKerjasama::TIDAK_AKTIF,
                'sublabel' => "Kerjasama sudah tidak berlaku atau dihentikan.",
                'icon' => 'x-circle',
                'color' => 'danger',
                'value' => $countData[StatusKerjasama::TIDAK_AKTIF] ?? 0
            ],
        ];

        return $widgetCount;
    }

    protected function buildView($page)
    {
        View::share($this->viewData + [
            'isLivewire' => true,
            'permission' => $this->permission,
            'urlInfo' => $this->urlInfo,
            'headerClass' => 'header_position-static',
        ]);

        return view($this->view)->layout('core::components.quantum-3.layouts.main-outer');
    }

    public function render()
    {
        return $this->buildView($this->view);
    }
}
