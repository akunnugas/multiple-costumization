<?php

namespace Modules\SPMI\Livewire;

use Illuminate\Support\Facades\View;
use Modules\Core\Livewire\MainComponent;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\HasilAkhirAudit;
use Modules\SPMI\Services\DashboardService;

class Dashboard extends MainComponent
{
    use SpmiViewData;

    public $filterStudyProgram;
    public $filterPeriod;
    public $filterOptions = [];
    public $recentPeriod = null;
    public $nowPeriod = null;
    public $isActivePeriod = false;
    public $activePeriod = null;
    public $idHasilAkhir = null;

    // Data
    public $rankSection = [];
    public $scheduleSection = [];
    public $auditFindingSection = [
        'total_observation_increase' => 0,
        'total_kts_mayor_increase' => 0,
        'total_kts_minor_increase' => 0,
    ];
    public $auditorPersonSection = [];
    public $criteriaChartSection = [];
    public $PenilaianAuditorSection = [];
    public $recapFinalScoreSection = null;
    public $auditTemuanData = null;

    // State
    public $scheduleState = [
        'isScheduled' => false,
        'sort' => 'asc'
    ];
    public $auditorPersonState = [
        'sort' => 'asc'
    ];
    public $PenilaianAuditorState = [
        'sort' => 'asc'
    ];
    public $recapFinalScoreState = [
        'page' => 1,
        'perPage' => 10
    ];
    public $auditTemuanState = [
        'page' => 1,
        'perPage' => 10
    ];

    protected $listeners = [
        'getRankSection',
        'getCriteriaChartSection',
        'getScheduleSection',
        'getAuditorPersonSection',
        'getPenilaianAuditorSection',
        'getAuditTemuanSection',
        'getRecapFinalScoreSection',
        'getAuditTemuanDataSection',
    ];

    public function loadService()
    {
        $this->service = new DashboardService();
    }

    public function mount()
    {
        parent::mount();

        $this->filterOptions = [
            'audit_periode' => AuditPeriode::options(),
            'study_programs' => UnitKerja::optionByType([UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI], isOnlyActive: true),
        ];
        $defaultYear = array_key_first($this->filterOptions['audit_periode']);
        $defaultProdStudyProgram = array_key_first($this->filterOptions['study_programs']);

        $this->filterStudyProgram = session('dashboard-filter-study-program') ?? $defaultProdStudyProgram;
        $this->filterPeriod = session('dashboard-filter-period') ?? $defaultYear;
        $this->activePeriod = AuditPeriode::find($defaultYear);
        $this->nowPeriod = AuditPeriode::find($this->filterPeriod);
        $this->recentPeriod = AuditPeriode::findByYear($this->nowPeriod?->tahun_audit - 1) ?? $this->nowPeriod;
    }

    public function render()
    {
        // Pengecekan periode aktif
        if ($this->activePeriod?->id == $this->filterPeriod) {
            $this->isActivePeriod = true;
        } else {
            $this->isActivePeriod = false;
        }

        View::share($this->viewData + [
            'isLivewire' => true,
            'permission' => $this->permission,
            'urlInfo' => $this->urlInfo,
            'alert' => $this->alert,
            'headerClass' => 'header_position-static',
        ]);

        return $this->buildView('spmi::pages.dashboard.index')
            ->layout('core::components.layouts.main', [
                'withContainer' => false
            ]);
    }

    public function updatedFilterPeriod($value)
    {
        $this->filterPeriod = $this->selectValue($value);
        $this->nowPeriod = AuditPeriode::find($this->filterPeriod);
        $this->recentPeriod = AuditPeriode::findByYear($this->nowPeriod?->tahun_audit - 1) ?? $this->nowPeriod;

        session(['dashboard-filter-period' => $this->filterPeriod]);

        $this->dispatch('updatedFilter');
    }

    public function updatedFilterStudyProgram($value)
    {
        $value = $this->selectValue($value);

        if ($value === 'all') {
            $value = null;
        }

        $this->filterStudyProgram = $this->selectValue($value);

        if ($value != null) {
            $hasilakhir = HasilAkhirAudit::where([
                'id_audit_periode' => $this->filterPeriod,
                'id_unit' => $this->filterStudyProgram
            ])->first();
            $this->idHasilAkhir = $hasilakhir ? $hasilakhir->id : null;
        }

        session(['dashboard-filter-study-program' => $this->filterStudyProgram]);

        $this->dispatch('updatedFilter');
    }

    public function getRankSection()
    {
        $this->rankSection = $this->service->showRankSectionData($this->filterPeriod, $this->filterStudyProgram);
        $this->dispatch('finishGetRankSection', $this->rankSection);
    }

    public function getCriteriaChartSection()
    {
        $this->criteriaChartSection = $this->service->showCriteriaChartSection($this->filterPeriod, $this->filterStudyProgram);
        $this->dispatch('finishGetCriteriaChartSection', $this->criteriaChartSection);
    }

    public function getScheduleSection($isScheduled = false, $sort = null)
    {
        if (!empty($sort) && !empty($this->scheduleSection)) {
            $this->sort($sort, 'nama_unit', $this->scheduleSection);
            $this->scheduleState['sort'] = $sort;

            return;
        }

        $this->scheduleState['isScheduled'] = $isScheduled;
        $this->scheduleSection = $this->service->getScheduledStudyProgram($this->filterPeriod, $isScheduled);

        $this->sort($sort ?? $this->scheduleState['sort'], 'nama_unit', $this->scheduleSection);
        $this->dispatch('finishGetScheduleSection', $this->scheduleSection);
    }

    public function getAuditorPersonSection($sort = null)
    {
        if (!empty($sort) && !empty($this->auditorPersonSection['data'])) {
            $this->sort($sort, 'name', $this->auditorPersonSection['data']);
            $this->auditorPersonState['sort'] = $sort;

            return;
        }

        $this->auditorPersonSection = $this->service->getAuditorPerson($this->filterPeriod);
        $oldPeriodAuditorPerson = $this->service->getAuditorPerson($this->recentPeriod?->id);

        // Menghitung total auditor increase
        if ($this->auditorPersonSection['total_auditor'] > 0) {
            $totalNowPeriodAuditorPerson = $this->auditorPersonSection['total_auditor'];
            $totalAuditorIncrease = (($this->auditorPersonSection['total_auditor'] - $oldPeriodAuditorPerson['total_auditor'])
                / $totalNowPeriodAuditorPerson) * 100;
            $totalAuditorIncrease = (int) $totalAuditorIncrease;
            $this->auditorPersonSection['total_auditor_increase'] = $totalAuditorIncrease;
        }

        // Menghitung total study program increase
        if ($this->auditorPersonSection['average_study_program'] > 0) {
            $totalNowPeriodStudyProgram = $this->auditorPersonSection['average_study_program'];
            $totalStudyProgramIncrease = (($this->auditorPersonSection['average_study_program'] - $oldPeriodAuditorPerson['average_study_program'])
                / $totalNowPeriodStudyProgram) * 100;
            $totalStudyProgramIncrease = (int) $totalStudyProgramIncrease;
            $this->auditorPersonSection['total_study_program_increase'] = $totalStudyProgramIncrease;
        }

        $this->sort($sort ?? $this->auditorPersonState['sort'], 'name', $this->auditorPersonSection['data']);
        $this->dispatch('finishGetAuditorPersonSection', $this->scheduleSection);
    }

    public function getPenilaianAuditorSection($sort = null)
    {
        if (!empty($sort) && !empty($this->PenilaianAuditorSection)) {
            $this->sort($sort, 'nama_unit', $this->PenilaianAuditorSection);
            $this->PenilaianAuditorState['sort'] = $sort;

            return;
        }

        $this->PenilaianAuditorSection = $this->rankSection['raw']['auditor_assessment'];

        $this->sort($sort ?? $this->auditorPersonState['sort'], 'nama_unit', $this->PenilaianAuditorSection);
        $this->dispatch('finishGetPenilaianAuditorSection', $this->PenilaianAuditorSection);
    }

    public function getAuditTemuanSection()
    {
        $nowPeriodData = $this->service->showAuditTemuanSection($this->filterPeriod, $this->filterStudyProgram);
        $oldPeriodData = $this->service->showAuditTemuanSection($this->recentPeriod?->id, $this->filterStudyProgram);
        $this->auditFindingSection = $nowPeriodData;

        $mostRelevantAuditTemuan = $this->service->getMostRelevantAuditTemuan($this->filterPeriod, $this->filterStudyProgram, 10);
        $this->auditFindingSection['most_findings'] = $mostRelevantAuditTemuan;

        // Menghitung total observasi increase
        if (isset($this->auditFindingSection['average']['observation'])) {
            $totalNowObservation = $this->auditFindingSection['average']['observation'];
            $totalOldObservation = $oldPeriodData['average']['observation'];
            $totalAll = ($totalNowObservation + $totalOldObservation) ?: 1;
            $totalObservationIncrease = (($totalNowObservation - $totalOldObservation) / $totalAll) * 100;
            $totalObservationIncrease = number_format($totalObservationIncrease, 2, '.', '');
            $this->auditFindingSection['total_observation_increase'] = $totalObservationIncrease;
        }

        // Menghitung total kts mayor increase
        if (isset($this->auditFindingSection['average']['kts_mayor'])) {
            $totalNowKtsMayor = $this->auditFindingSection['average']['kts_mayor'];
            $totalOldKtsMayor = $oldPeriodData['average']['kts_mayor'];
            $totalAll = ($totalNowKtsMayor + $totalOldKtsMayor) ?: 1;
            $totalKtsMayorIncrease = (($totalNowKtsMayor - $totalOldKtsMayor) / $totalAll) * 100;
            $totalKtsMayorIncrease = number_format($totalKtsMayorIncrease, 2, '.', '');
            $this->auditFindingSection['total_kts_mayor_increase'] = $totalKtsMayorIncrease;
        }

        // Menghitung total kts minor increase
        if (isset($this->auditFindingSection['average']['kts_minor'])) {
            $totalNowKtsMinor = $this->auditFindingSection['average']['kts_minor'];
            $totalOldKtsMinor = $oldPeriodData['average']['kts_minor'];
            $totalAll = ($totalNowKtsMinor + $totalOldKtsMinor) ?: 1;
            $totalKtsMinorIncrease = (($totalNowKtsMinor - $totalOldKtsMinor) / $totalAll) * 100;
            $totalKtsMinorIncrease = number_format($totalKtsMinorIncrease, 2, '.', '');
            $this->auditFindingSection['total_kts_minor_increase'] = $totalKtsMinorIncrease;
        }

        $this->dispatch('finishGetAuditTemuanSection', $this->auditFindingSection);
    }

    public function getAuditTemuanDataSection()
    {
        $page = $this->auditTemuanState['page'];
        $perPage = $this->auditTemuanState['perPage'];

        $this->auditTemuanData = $this->service->getAuditTemuanData($this->filterPeriod, $this->filterStudyProgram, $page, $perPage);
    }

    public function getRecapFinalScoreSection()
    {
        $page = $this->recapFinalScoreState['page'];
        $perPage = $this->recapFinalScoreState['perPage'];

        $this->recapFinalScoreSection = $this->service->getRecapFinalScoreData($this->filterPeriod, $this->filterStudyProgram, $page, $perPage);
    }

    public function gotoPageFinalRecap($page)
    {
        $this->recapFinalScoreState['page'] = $page;
        $this->getRecapFinalScoreSection();
    }

    public function nextPageFinalRecap()
    {
        if ($this->recapFinalScoreState['page'] < $this->recapFinalScoreSection?->lastPage) {
            $this->recapFinalScoreState['page']++;
        }

        $this->getRecapFinalScoreSection();
    }

    public function previousPageFinalRecap()
    {
        if ($this->recapFinalScoreState['page'] > 1) {
            $this->recapFinalScoreState['page']--;
        }

        $this->getRecapFinalScoreSection();
    }

    public function setPerPageFinalRecap($perPage)
    {
        $this->recapFinalScoreState['perPage'] = $perPage;
        $this->getRecapFinalScoreSection();
    }

    public function gotoPageAuditTemuan($page)
    {
        $this->auditTemuanState['page'] = $page;
        $this->getAuditTemuanDataSection();
    }

    public function nextPageAuditTemuan()
    {
        if ($this->auditTemuanState['page'] < $this->auditTemuanData?->lastPage) {
            $this->auditTemuanState['page']++;
        }

        $this->getAuditTemuanDataSection();
    }

    public function previousPageAuditTemuan()
    {
        if ($this->auditTemuanState['page'] > 1) {
            $this->auditTemuanState['page']--;
        }

        $this->getAuditTemuanDataSection();
    }

    public function setPerPageAuditTemuan($perPage)
    {
        $this->auditTemuanState['perPage'] = $perPage;
        $this->getAuditTemuanDataSection();
    }

    protected function sort($sort, $key, &$data)
    {
        usort($data, function ($a, $b) use ($sort, $key) {
            if ($sort === 'asc') {
                return $a[$key] <=> $b[$key];
            } else {
                return $b[$key] <=> $a[$key];
            }
        });
    }

    /**
     * Fungsi untuk ambil value choices.
     */
    protected function selectValue($value)
    {
        if (is_array($value) && array_key_exists('value', $value)) {
            return $value['value'];
        }

        return $value ?? null;
    }
}
