<?php

namespace Modules\SPMI\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Core\Livewire\CreateEditComponent;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\Gate\Models\Modul;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\MappingLED;
use Modules\SPMI\Models\MappingLK;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Services\MappingLKLEDManagementService;

class FormMappingIndikatorButir extends CreateEditComponent
{
    use SpmiViewData;

    public $auditPeriodId;
    public $unitId;
    public $pengisianPanduanId;
    public $jenisEdisi;
    public $isProdi;
    public $listButirIndikator = [];
    public $checkedButir = [];
    public $infoLeftRight = [];
    private $unitKerjaService;

    public $additionalRecords = [
        ['field' => 'checkedButir'],
        ['field' => 'unitId'],
        ['field' => 'auditPeriodId'],
        ['field' => 'jenisEdisi'],
    ];

    public function mount()
    {
        parent::mount();

        $this->unitId = request()->route('unit_id');
        $this->auditPeriodId = request()->route('period_id');
        $this->jenisEdisi = request()->get('jenis_edisi');
        if (empty($this->unitId) || empty($this->auditPeriodId) || empty($this->jenisEdisi) || !isset(AkreditasiBuku::TYPE[$this->jenisEdisi])) {
            return abort(404);
        }

        $this->viewData['title'] = 'Set Mapping Butir ' . AkreditasiBuku::TYPE[$this->jenisEdisi];

        // $this->pengisianPanduanId = request()->get('id_pengisian_panduan');
        // if (!empty($this->pengisianPanduanId)) {
        //     $this->loadButir($this->pengisianPanduanId);
        // }

        $item = $this->service->show($this->unitId);

        $this->isProdi = in_array($item->jenis_unit, [UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI]);
        $this->infoLeftRight = [
            'info_left' => $item->info_left,
            'info_right' => $item->info_right,
        ];

        $auditPeriode = AuditPeriode::find($this->auditPeriodId);

        $this->record['audit_periode'] = $auditPeriode->tahun_audit;

        if ($this->isProdi) {
            $this->record['unit_kerja'] = $item->kode_jenjang . ' - ' . $item->nama_unit;
        } else {
            $this->record['unit_kerja'] = $item->nama_unit;
        }
    }

    public function loadButir($idPengisianPanduan)
    {
        if ($this->jenisEdisi == AkreditasiBuku::PERFORMANCE_REPORT) {
            $this->listButirIndikator = IndikatorLaporanKinerja::where('id_pengisian_panduan', $idPengisianPanduan)
                ->orderBy('info_left')
                ->get()
                ->toArray();

            if ($this->isProdi) {
                $this->checkedButir = MappingLK::where('id_audit_periode', $this->auditPeriodId)
                    ->where('id_unit', $this->unitId)
                    ->pluck('id_indikator_laporan_kinerja', 'id_indikator_laporan_kinerja')
                    ->toArray();
            }
        } else {
            $this->listButirIndikator = IndikatorEvaluasiDiri::where('id_pengisian_panduan', $idPengisianPanduan)
                ->orderBy('nomor_indikator', 'asc')
                ->orderBy('info_left', 'asc')
                ->get()
                ->toArray();
            if ($this->isProdi) {
                $this->checkedButir = MappingLED::where('id_audit_periode', $this->auditPeriodId)
                    ->where('id_unit', $this->unitId)
                    ->pluck('id_indikator_evaluasi_diri', 'id_indikator_evaluasi_diri')
                    ->toArray();
            }
        }
    }

    public function loadService()
    {
        $this->service = new MappingLKLEDManagementService();

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
        if (empty($this->pengisianPanduanId) || (!$this->isProdi && empty($this->record['program_studi']))) {
            throw ValidationException::withMessages([
                'id_pengisian_panduan' => 'Panduan Pengisian harus dipilih.',
                'program_studi' => 'Program Studi harus dipilih.',
            ]);
        }

        $this->record['checkedButir'] = $this->checkedButir;
        $this->record['unitId'] = $this->unitId;
        $this->record['auditPeriodId'] = $this->auditPeriodId;
        $this->record['jenisEdisi'] = $this->jenisEdisi;

        parent::save();
    }

    protected function successUrlAfterSave()
    {
        return route('spmi.mapping-indikator-butir.show', ['unit_id' => $this->unitId, 'period_id' => $this->auditPeriodId, 'tab' => $this->jenisEdisi]);
    }

    protected function defineFormFields()
    {
        $units = ['' => 'Pilih Unit Kerja'] + $this->unitKerjaService->getUnitAncestors($this->unitId);

        $listPanduanPengisian = PengisianPanduan::where('tipe_edisi', $this->jenisEdisi)
            ->where('apakah_aktif', true)
            ->get()
            ->pluck('nama_singkat', 'id')
            ->toArray();

        $fields = [
            ['field' => 'audit_periode', 'disabled' => true, 'label' => 'Periode AMI', 'helper' => 'Periode aktif tidak dapat diubah. Semua mapping akan diterapkan pada periode ini.'],
            ['field' => 'unit_kerja', 'disabled' => true, 'label' => 'Unit Kerja']
        ];

        if (!$this->isProdi) {
            $fields[] = ['field' => 'program_studi', 'label' => 'Diterapkan pada Unit Kerja', 'required' => true, 'control' => 'select-multiple-v2', 'options' => $units, 'helper' => 'Pilih satu atau lebih program studi yang berada di bawah unit kerja.'];
        }

        $fields[] = [
            'field' => 'id_pengisian_panduan',
            'wire:change' => 'updatePanduanPengisian($event.target.value)',
            'options' => $listPanduanPengisian,
            'label' => 'Panduan Pengisian',
            'variant' => 'search',
            'selected' => $this->pengisianPanduanId,
            'required' => true,
            'helper' => 'Gunakan panduan sesuai instrumen akreditasi yang berlaku (contoh: IAPS Versi 4.0).'
        ];

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

    public function updatePanduanPengisian($value)
    {
        $this->pengisianPanduanId = $value;
        if (empty($value)) {
            $this->listButirIndikator = [];
            $this->checkedButir = [];
            $this->pengisianPanduanId = null;
            return;
        }

        $this->loadButir($value);
    }
}
