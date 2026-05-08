<?php

namespace Modules\SPMI\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Core\Livewire\CreateEditComponent;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\MappingPenilaianMatriks;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\TargetIndikator;
use Modules\SPMI\Services\MappingPenilaianMatriksManagementService;

class FormMappingMatriksPenilaian extends CreateEditComponent
{
    use SpmiViewData;

    public $auditPeriodId;
    public $unitId;
    public $penilaianPanduanId;
    public $pengisianPanduanId;
    public $isProdi;
    public $isProdiDisabled = false;
    public $listMatriksPenilaian = [];
    public $checkedButir = [];
    public $infoLeftRight = [];
    public $listProdiFinalisasi = [];
    public $listUnits = [];
    private $listPenilaianPanduan;
    private $unitKerjaService;

    public $showModal = false;

    public $additionalRecords = [
        ['field' => 'checkedButir'],
        ['field' => 'unitId'],
        ['field' => 'auditPeriodId'],
    ];

    public function mount()
    {
        parent::mount();

        $this->unitId = request()->route('unit_id');
        $this->auditPeriodId = request()->route('period_id');
        if (empty($this->unitId) || empty($this->auditPeriodId)) {
            return abort(404);
        }

        $this->viewData['title'] = 'Set Mapping Matriks Penilaian';

        // $this->penilaianPanduanId = request()->get('id_pengisian_panduan');
        // if (!empty($this->penilaianPanduanId)) {
        //     $this->loadButir($this->penilaianPanduanId);
        // }

        $item = $this->service->show($this->unitId);

        $this->isProdi = in_array($item->jenis_unit, [UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI]);
        $this->infoLeftRight = [
            'info_left' => $item->info_left,
            'info_right' => $item->info_right,
        ];

        $auditPeriode = AuditPeriode::find($this->auditPeriodId);

        $this->record['audit_periode'] = $auditPeriode->tahun_audit;

        $this->listUnits = ['' => 'Pilih Unit Kerja'] + $this->unitKerjaService->getUnitAncestors($this->unitId);

        if ($this->isProdi) {
            $this->record['unit_kerja'] = $item->kode_jenjang . ' - ' . $item->nama_unit;
        } else {
            $this->record['unit_kerja'] = $item->nama_unit;
        }
    }

    public function loadButir($idPenilaianPanduan)
    {
        $this->listMatriksPenilaian = PenilaianMatriks::where('id_penilaian_panduan', $idPenilaianPanduan)
            ->where('apakah_aktif', true)
            ->orderBy('apakah_data_default', 'desc')
            ->orderBy('info_left')
            ->get()
            ->toArray();

        if ($this->isProdi) {
            $this->checkedButir = MappingPenilaianMatriks::join('spmi.penilaian_matriks as pm', 'pm.id', '=', 'spmi.mapping_penilaian_matriks.id_penilaian_matriks')
                ->where('pm.id_penilaian_panduan', $idPenilaianPanduan)
                ->where('id_audit_periode', $this->auditPeriodId)
                ->where('id_unit', $this->unitId)
                ->pluck('id_penilaian_matriks', 'id_penilaian_matriks')
                ->toArray();
            $this->isProdiDisabled = TargetIndikator::where('id_penilaian_panduan', $idPenilaianPanduan)
                ->where('id_audit_periode', $this->auditPeriodId)
                ->where('id_unit', $this->unitId)
                ->where('apakah_terfinalisasi', true)
                ->exists();
        } else {
            $ids = array_keys($this->listUnits);
            $ids = array_filter($ids, function ($value) {
                return !empty($value);
            });
            $this->listProdiFinalisasi = DB::table('spmi.target_indikator as ti')
                ->join('core.unit_kerja as uk', 'uk.id', '=', 'ti.id_unit')
                ->join('core.jenjang_pendidikan as jp', 'jp.id', '=', 'uk.id_jenjang_pendidikan')
                ->select('uk.id', DB::raw("CONCAT(jp.kode_jenjang, ' - ', uk.nama_unit) as nama_unit"))
                ->where('ti.id_penilaian_panduan', $idPenilaianPanduan)
                ->where('ti.id_audit_periode', $this->auditPeriodId)
                ->where('ti.apakah_terfinalisasi', true)
                ->whereIn('uk.id', $ids)
                ->distinct()
                ->get()
                ->toArray();
        }
    }

    public function loadService()
    {
        $this->service = new MappingPenilaianMatriksManagementService();

        $this->unitKerjaService = new UnitKerjaManagementService();
    }

    public function render()
    {
        $this->loadModel();

        $this->loadData();

        return $this->buildView($this->view);
    }

    public function loadModel()
    {
        $this->model = UnitKerja::class;
    }

    public function save()
    {
        if (empty($this->penilaianPanduanId) || (!$this->isProdi && empty($this->record['program_studi']))) {
            throw ValidationException::withMessages([
                'id_penilaian_panduan' => 'Panduan Penilaian harus dipilih.',
                'program_studi' => 'Program Studi harus dipilih.',
            ]);
        }

        if (!$this->isProdi && !empty($this->listProdiFinalisasi)) {
            $this->record['program_studi'] = array_filter($this->record['program_studi'], function ($prodiId) {
                return !in_array($prodiId, array_column($this->listProdiFinalisasi, 'id'));
            });
            $this->record['program_studi'] = array_values($this->record['program_studi']);
        }

        $this->record['checkedButir'] = $this->checkedButir;
        $this->record['unitId'] = $this->unitId;
        $this->record['auditPeriodId'] = $this->auditPeriodId;

        list($isValid, $message) = $this->service->syncMatriksPengisian(
            matriks: $this->record['checkedButir'],
            penilaianPanduanId: $this->penilaianPanduanId,
            auditPeriodId: $this->auditPeriodId,
            unitId: $this->unitId,
            withSave: false
        );

        if (!$isValid) {
            $this->toggleModal();
            return;
        }

        parent::save();
    }

    protected function successUrlAfterSave()
    {
        return route('spmi.mapping-matriks-penilaian.show', ['unit_id' => $this->unitId, 'period_id' => $this->auditPeriodId]);
    }

    protected function defineFormFields()
    {
        $this->listPenilaianPanduan = DB::table('spmi.mapping_panduan')
            ->join('spmi.penilaian_panduan', 'spmi.mapping_panduan.id_penilaian_panduan', '=', 'spmi.penilaian_panduan.id')
            ->where('spmi.mapping_panduan.id_pengisian_panduan', $this->pengisianPanduanId)
            ->where('spmi.penilaian_panduan.apakah_aktif', true)
            ->distinct()
            ->get(['spmi.penilaian_panduan.id', 'spmi.penilaian_panduan.nama_singkat'])
            ->pluck('nama_singkat', 'id')
            ->toArray();

        $fields = [
            ['field' => 'audit_periode', 'disabled' => true, 'label' => 'Periode AMI', 'helper' => 'Periode aktif tidak dapat diubah. Semua mapping akan diterapkan pada periode ini.'],
            ['field' => 'unit_kerja', 'disabled' => true, 'label' => 'Unit Kerja']
        ];

        $fields[] = [
            'field' => 'id_pengisian_panduan',
            'wire:change' => 'updatePengisianPanduan($event.target.value)',
            'options' => PengisianPanduan::getListIndicatorPerformanceReport(),
            'label' => 'Panduan Pengisian',
            'selected' => $this->pengisianPanduanId,
            'variant' => 'search',
            'required' => true,
        ];

        $fields[] = [
            'field' => 'id_penilaian_panduan',
            'wire:change' => 'updatePenilaianPanduan($event.target.value)',
            'options' => $this->listPenilaianPanduan,
            'label' => 'Panduan Penilaian',
            'selected' => $this->penilaianPanduanId,
            'variant' => 'search',
            'required' => true,
            'helper' => (empty($this->listPenilaianPanduan) && !empty($this->pengisianPanduanId)) ? 'Panduan penilaian tidak dapat ditampilkan karena panduan pengisian yang dipilih belum di-mapping pada halaman Mapping Panduan. <a style="text-decoration: underline; color: #007BE5;" href="' . route('spmi.mapping-panduan.index', ['mp.id_pengisian_panduan' => $this->pengisianPanduanId]) . '">Lakukan Mapping Sekarang</a>' : null
        ];

        if (!$this->isProdi) {
            $fields[] = ['field' => 'program_studi', 'label' => 'Diterapkan pada Unit Kerja', 'required' => true, 'control' => 'select-multiple-v2', 'options' => $this->listUnits, 'helper' => 'Pilih satu atau lebih program studi yang berada di bawah unit kerja.'];
        }

        return $fields;
    }

    public function updateMapping($value, $idButir)
    {
        if ($value) {
            $this->checkedButir[$idButir] = $idButir;
        } else {
            unset($this->checkedButir[$idButir]);
        }
    }

    public function updateBulkMapping($changedData)
    {
        foreach ($changedData as $butirId => $isChecked) {
            if ($isChecked) {
                $this->checkedButir[$butirId] = $butirId;
            } else {
                unset($this->checkedButir[$butirId]);
            }
        }
    }

    public function updatePenilaianPanduan($value)
    {
        $this->penilaianPanduanId = $value;

        if (empty($value)) {
            $this->listMatriksPenilaian = [];
            $this->checkedButir = [];
            $this->penilaianPanduanId = null;
            return;
        }

        $this->loadButir($value);
    }

    public function updatePengisianPanduan($value)
    {
        if (empty($value)) {
            $this->pengisianPanduanId = null;
            $this->listMatriksPenilaian = [];
            $this->checkedButir = [];
            $this->penilaianPanduanId = null;
            return;
        }
        $this->pengisianPanduanId = $value;
        $this->penilaianPanduanId = null;
        $this->listMatriksPenilaian = [];
        $this->checkedButir = [];
    }

    public function toggleModal()
    {
        $this->showModal = !$this->showModal;
    }

    public function forceSave()
    {
        list($isValid, $message) = $this->service->syncMatriksPengisian(
            matriks: $this->record['checkedButir'],
            penilaianPanduanId: $this->penilaianPanduanId,
            auditPeriodId: $this->auditPeriodId,
            unitId: $this->unitId,
            withSave: true
        );

        if ($isValid) {
            parent::save();
        }
    }
}
