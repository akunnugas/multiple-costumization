<?php

namespace Modules\Litabmas\Livewire;

use Modules\Core\Helpers\Page;
use Modules\Core\Livewire\MainComponent;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Url;
use Modules\Core\Helpers\SessionManager;
use Modules\Core\Models\UnitKerja;
use Modules\Gate\Models\Role;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Models\JenisOutcomePenelitian;
use Modules\Litabmas\Models\JenisOutputPenelitian;
use Modules\Litabmas\Models\PengajuanPendanaanJadwalPresentasi;
use Modules\Litabmas\Models\PengajuanPendanaanStatus;
use Modules\Litabmas\Models\PengajuanPendanaanStatus2;
use Modules\Litabmas\Models\PengumumanPendanaan;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Services\DashboardManagementService;
use Modules\Litabmas\Services\KlasterPendanaanService;
use Modules\Litabmas\Services\PengajuanPendanaanService;
use Modules\Litabmas\Services\SumberPendanaanService;

class Dashboard extends MainComponent
{
    use LitabmasViewData;

    public $title, $subtitle;
    public $permission, $isDosen, $isPimpinan, $isRoleInternal;
    public $view = 'litabmas::livewire.dashboard.dosen';

    private $klasterService;
    private $pengajuanPendanaanService;

    private $dashboardManagementService;

    // main variable
    public $dataKlaster, $dataPengumuman, $countedProposalDiajukan, $dataJadwalProposal;
    public $listIdProposalTerlibat;
    public $listIdKlasterProposalDiajukan;
    public $sumberPendanaan;
    public $periodePendanaan;
    public $sebaranStatusProposal;
    public $statusOutputProposal;
    public $statusOutcomeProposal;
    public $jenisOutput;
    public $jenisOutcome;
    public $chartData;

    // filter
    public $filterOptions = [];
    #[Url]
    public $filterPeriode = '';
    #[Url]
    public $filterJenisPendanaan = '';
    #[Url]
    public $filterUnit = '';

    // admin
    public $dataProposalDiajukan, $kodeJenisProposal;

    public function mount()
    {
        $this->loadService();

        $this->isDosen = in_array(auth()->user()?->kode_role, [Role::ROLE_DOSEN]);
        $this->isPimpinan = in_array(auth()->user()?->kode_role, [
            Role::ROLE_LITABMAS_ADMIN_LPPM,
            Role::ROLE_LITABMAS_KETUA_LPPM,
            Role::ROLE_REKTOR,
            Role::ROLE_DEKAN,
            Role::ROLE_KAPRODI,
            Role::ROLE_WAKIL_REKTOR_1,
            Role::ROLE_WAKIL_REKTOR_2,
            Role::ROLE_WAKIL_REKTOR_3,
            Role::ROLE_WAKIL_DEKAN_1,
            Role::ROLE_WAKIL_DEKAN_2,
            Role::ROLE_WAKIL_DEKAN_3,
        ]);
        $this->isRoleInternal = SessionManager::isInternalRole();

        $this->permission = request()->permission;
        $this->urlInfo = Page::showURLInfo();
        $this->viewData = $this->defineViewData();

        $this->getTitlePage();

        $this->loadPageData();

        if ($this->isPimpinan || $this->isRoleInternal) {
            $this->view = 'litabmas::livewire.dashboard.index';

            $optPeriodePendanaan = PeriodePendanaan::options();

            $this->filterOptions = [
                'periode_pendanaan' => ['' => 'Semua'] + (!empty($optPeriodePendanaan) ? $optPeriodePendanaan : []),
                'jenis_pendanaan' => ['' => 'Semua'] + JenisPendanaanEnum::CODES,
                'unit' => ['' => 'Semua'] + UnitKerja::optionByType([UnitKerja::FACULTY]),
            ];

            $this->filterPeriode = $this->filterPeriode ?? '';
            $this->filterJenisPendanaan = $this->filterJenisPendanaan ?? '';

            $this->loadPageDataPimpinan();
        }
    }

    private function getTitlePage()
    {
        $this->title = 'Dashboard';
        $this->subtitle = 'Beranda';
    }

    private function loadPageDataPimpinan()
    {
        $filter = [
            'jenis_pendanaan' => $this->selectValue($this->filterJenisPendanaan),
            'periode_pendanaan' => $this->selectValue($this->filterPeriode),
            'unit' => $this->selectValue($this->filterUnit),
        ];

        // Logika Tanggal untuk Filter 'Semua Periode'
        if (!empty($filter['periode_pendanaan'])) {
            // Jika memilih periode spesifik
            $this->periodePendanaan = PeriodePendanaan::find($filter['periode_pendanaan'], ['id', 'tahun', 'tanggal_mulai', 'tanggal_akhir']);
            if (!empty($this->periodePendanaan)) {
                $this->periodePendanaan = $this->periodePendanaan->toArray();
            } else {
                $this->periodePendanaan = [];
            }
        } else {
            // Jika 'Semua Periode' (filter kosong), ambil range dari tanggal paling awal s/d paling akhir di database
            $minDate = PeriodePendanaan::min('tanggal_mulai');
            $maxDate = PeriodePendanaan::max('tanggal_akhir');

            if ($minDate && $maxDate) {
                $this->periodePendanaan = [
                    'id' => null,
                    'tahun' => 'Semua',
                    'tanggal_mulai' => $minDate,
                    'tanggal_akhir' => $maxDate,
                ];
            } else {
                $this->periodePendanaan = [];
            }
        }

        $this->sumberPendanaan = $this->dashboardManagementService->getSumberPendanaanDashboard($filter);

        $this->sebaranStatusProposal = $this->dashboardManagementService->getSebaranStatusProposal($filter);

        $this->statusOutputProposal = $this->dashboardManagementService->getStatusOutputProposal($filter);

        $this->statusOutcomeProposal = $this->dashboardManagementService->getStatusOutcomeProposal($filter);

        $this->jenisOutput = JenisOutputPenelitian::options();
        $this->jenisOutcome = JenisOutcomePenelitian::options();

        $this->chartData = [
            'doughnut' => ($this->sumberPendanaan['total']['nominal_anggaran_disetujui_persentase'] ?? 0),
            'line' => [
                ($this->sebaranStatusProposal[PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN]['jumlah'] ?? 0),
                ($this->sebaranStatusProposal[PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI]['jumlah'] ?? 0),
                ($this->statusOutputProposal['proposal_belum_pengumpulkan_luaran'] ?? 0),
                ($this->statusOutputProposal['proposal_dengan_luaran_belum_selesai'] ?? 0),
                ($this->sebaranStatusProposal[PengajuanPendanaanStatus2::LEVEL12_PENGUMPULAN_HASIL]['jumlah'] ?? 0)
            ]
        ];

        $this->dispatch('finishLoadDataPimpinan', $this->chartData);
    }

    private function loadPageData()
    {
        $this->dataKlaster = $this->klasterService->getListPengumumanKlaster(4);

        $this->dataPengumuman = PengumumanPendanaan::where('status_pengumuman', PengumumanPendanaan::STATUS_TERPUBLIKASI)->limit(3)->get();

        $this->countedProposalDiajukan = $this->pengajuanPendanaanService->countProposalDiajukan();

        $this->listIdKlasterProposalDiajukan = [];
        if ($this->isDosen) {
            $this->listIdKlasterProposalDiajukan = $this->pengajuanPendanaanService->getListIdKlasterProposalDiajukan(auth()->user()->biodata->id);
        } else {
            $this->kodeJenisProposal = JenisPendanaanEnum::CODE_PENELITIAN;

            $this->dataProposalDiajukan = $this->pengajuanPendanaanService->index(kodeJenisProposal: $this->kodeJenisProposal)->items;
        }

        $this->loadCalendar(date('m Y'));
    }

    public function loadCalendar($month)
    {
        if ($this->isDosen) {
            $idBiodata = auth()->user()->biodata->id;

            $this->listIdProposalTerlibat = $this->pengajuanPendanaanService->getListIdProposalTerlibat($idBiodata, $month);

            $this->dataJadwalProposal = PengajuanPendanaanJadwalPresentasi::whereIn('id_pengajuan_pendanaan', $this->listIdProposalTerlibat)->get()->toArray();

            $mappingTugas = $this->service->loadMappingTugas($idBiodata, $this->listIdProposalTerlibat);

            $dataAnggota = $this->service->loadDataAnggota($idBiodata, $this->dataJadwalProposal);

            foreach ($this->dataJadwalProposal as $key => $jadwal) {
                if (
                    !isset($mappingTugas[$jadwal['id_pengajuan_pendanaan']][$jadwal['tipe_presentasi']])
                    && !isset($dataAnggota[$jadwal['id_pengajuan_pendanaan']])
                ) {
                    unset($this->dataJadwalProposal[$key]);
                }
            }
        } else {
            $this->listIdProposalTerlibat = $this->pengajuanPendanaanService->getListIdProposalTerlibat(month: $month);

            $this->dataJadwalProposal = PengajuanPendanaanJadwalPresentasi::whereIn('id_pengajuan_pendanaan', $this->listIdProposalTerlibat)->get()->toArray();
        }
    }

    public function fetchJadwalProposal($y, $m)
    {
        $this->loadCalendar("$m $y");

        $formatedData = $this->service->formatCalendarData($this->dataJadwalProposal);

        $this->dispatch('render-calendar', [
            'data' => $formatedData,
            'month' => $m,
            'year' => $y,
        ]);
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

    public function loadService()
    {
        $this->service = new DashboardManagementService;

        $this->pengajuanPendanaanService = new PengajuanPendanaanService;

        $this->klasterService = new KlasterPendanaanService;

        //role pimpinan
        $this->dashboardManagementService = new DashboardManagementService;
    }

    public function render()
    {
        return $this->buildView($this->view);
    }

    public function changeTabProposal($kodeJenisProposal)
    {
        $this->kodeJenisProposal = $kodeJenisProposal;

        $this->dataProposalDiajukan = $this->pengajuanPendanaanService->index(kodeJenisProposal: $this->kodeJenisProposal)->items;
    }

    protected function selectValue($value)
    {
        if (is_array($value) && array_key_exists('value', $value)) {
            return $value['value'];
        }

        return $value ?? null;
    }

    public function updatedFilterPeriode($value)
    {
        $this->filterPeriode = $this->selectValue($value);
        $this->loadPageDataPimpinan();
    }
    public function updatedFilterJenisPendanaan($value)
    {
        $this->filterJenisPendanaan = $this->selectValue($value);
        $this->loadPageDataPimpinan();
    }
    public function updatedFilterUnit($value)
    {
        $this->filterUnit = $this->selectValue($value);
        $this->loadPageDataPimpinan();
    }
}