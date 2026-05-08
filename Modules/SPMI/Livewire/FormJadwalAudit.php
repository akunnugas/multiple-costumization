<?php

namespace Modules\SPMI\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Core\Livewire\CreateEditComponent;
use Modules\Core\Models\UnitKerja;
use Modules\Gate\Models\Modul;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Models\JadwalAuditUnit;
use Modules\SPMI\Models\MappingPanduan;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Services\JadwalAuditManagementService;

class FormJadwalAudit extends CreateEditComponent
{
    use SpmiViewData;

    public $auditPeriodId;

    public $units, $listPanduanPengisian, $listPanduanPenilaian, $listMappingPanduan;
    public $selectedPengisian = [];
    public $listPenilaianOptions = [];
    public $selectedPenilaian = [];
    public $alertCustom = [];
    public $additionalRecords = [
        ['field' => 'selectedPengisian'],
        ['field' => 'selectedPenilaian']
    ];

    public function mount()
    {
        parent::mount();

        $this->viewData['title'] = 'Buat Jadwal AMI';

        $this->record['unit'] = [];

        $this->units = UnitKerja::optionByType([UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI], isOnlyActive: true, isHaveJenjang: true, isWithCheckUserUnit: false);

        $this->listPanduanPengisian = PengisianPanduan::where('apakah_aktif', true)
            ->where('tipe_edisi', AkreditasiBuku::PERFORMANCE_REPORT)
            ->pluck('nama_singkat', 'id')->toArray();
        $this->listPanduanPenilaian = PenilaianPanduan::where('apakah_aktif', true)
            ->pluck('nama_singkat', 'id')
            ->toArray();

        $mappingPanduan = DB::table('spmi.mapping_panduan')
            ->select('spmi.mapping_panduan.id_pengisian_panduan', 'spmi.mapping_panduan.id_penilaian_panduan')
            ->join('spmi.pengisian_panduan', 'spmi.mapping_panduan.id_pengisian_panduan', '=', 'spmi.pengisian_panduan.id')
            ->join('spmi.penilaian_panduan', 'spmi.mapping_panduan.id_penilaian_panduan', '=', 'spmi.penilaian_panduan.id')
            ->whereNull('spmi.pengisian_panduan.waktu_dihapus')
            ->whereNull('spmi.penilaian_panduan.waktu_dihapus')
            ->get();
        $listMapping = [];
        foreach ($mappingPanduan as $row) {
            $listMapping[$row->id_pengisian_panduan][] = $row->id_penilaian_panduan;
        }

        $this->listMappingPanduan = $listMapping;


        $temp = [];
        foreach ($this->listMappingPanduan as $key => $arr) {
            $arr = array_flip($arr);
            foreach ($arr as $idPanduanPenilaian => $index) {
                $temp[$key][$idPanduanPenilaian] = $this->listPanduanPenilaian[$idPanduanPenilaian];
            }
        }
        $this->listMappingPanduan = $temp;
        unset($temp);

        if ($this->edit) {
            $this->viewData['title'] = 'Edit Jadwal AMI';

            $item = $this->service->show($this->edit);

            $this->auditPeriodId = $item->id_audit_periode;

            $dataMapping = JadwalAuditUnit::where('id_jadwal_audit', $this->edit)
                ->get(['id_unit', 'id_pengisian_panduan', 'id_penilaian_panduan']);
            $this->selectedPengisian = $dataMapping->pluck('id_pengisian_panduan', 'id_unit')->toArray();
            $this->selectedPenilaian = $dataMapping->pluck('id_penilaian_panduan', 'id_unit')->toArray();

            foreach ($dataMapping as $item) {
                $this->updatedSelectedPengisian($item->id_pengisian_panduan, 'selectedPengisian.' . $item->id_unit);
                $this->updatePenilaian($item->id_penilaian_panduan, $item->id_unit);
            }
        }

        $this->getUnitTables();
    }

    public function updatedSelectedPengisian($value, $name)
    {
        $idUnit = str()->after($name, 'selectedPengisian.');

        $idPengisian = is_array($value)
            ? ($value['value'] ?? $value['id'] ?? array_key_first($value) ?? null)
            : $value;

        if (!is_string($idPengisian) && !is_int($idPengisian) && !ctype_digit((string)$idPengisian)) {
            $this->listPenilaianOptions[$idUnit] = [];
            return;
        }

        if (isset($this->listMappingPanduan[$idPengisian])) {
            $this->listPenilaianOptions[$idUnit] = $this->listMappingPanduan[$idPengisian];
        } else {
            $this->listPenilaianOptions[$idUnit] = [];
        }

        if (isset($this->selectedPenilaian[$idUnit]) && !array_key_exists($this->selectedPenilaian[$idUnit], $this->listPenilaianOptions[$idUnit])) {
            unset($this->selectedPenilaian[$idUnit]);
        }
    }

    public function updatePenilaian($value, $idunit)
    {
        $this->selectedPenilaian[$idunit] = $value;
    }

    public function showAlertCustom($type, $message)
    {
        $this->alertCustom = [
            'type' => $type,
            'message' => $message,
        ];

        $this->dispatch('scroll-to-alert-custom');
    }

    public function customValidationBeforeSave(): bool
    {
        $this->selectedPengisian = array_filter($this->selectedPengisian);
        foreach ($this->selectedPengisian as $index => $val) {
            if (empty($val)) {
                unset($this->selectedPengisian[$index]);
            }

            if (!isset($this->selectedPenilaian[$index]) || empty($this->selectedPenilaian[$index])) {
                $this->showAlertCustom('error', 'Mohon pilih panduan penilaian untuk setiap unit kerja terpilih.');
                return false;
            }
        }

        if (empty($this->selectedPengisian)) {
            $this->showAlertCustom('error', 'Mohon pilih panduan pengisian dan penilaian untuk setiap unit kerja minimal satu.');
            return false;
        }

        return true;
    }

    public function loadService()
    {
        $this->service = new JadwalAuditManagementService();
    }

    public function render()
    {
        $this->loadModel();

        $this->loadData();

        return $this->buildView($this->view);
    }

    public function loadModel()
    {
        $this->model = JadwalAudit::class;
    }

    public function save()
    {
        if (isset($this->record['tanggal_pengisian'])) {
            unset($this->record['tanggal_pengisian']);
        }

        if (isset($this->record['tanggal_penilaian'])) {
            unset($this->record['tanggal_penilaian']);
        }

        $this->record['selectedPengisian'] = $this->selectedPengisian;
        $this->record['selectedPenilaian'] = $this->selectedPenilaian;

        return parent::save();
    }

    public function getUnitTables()
    {
        if (!empty($this->auditPeriodId)) {
            $this->units = UnitKerja::optionByType([UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI], isOnlyActive: true, isHaveJenjang: true, isWithCheckUserUnit: false);
        } else {
            $this->units = [];
        }
    }

    public function updateAuditPeriod($value)
    {
        if (!empty($value)) {
            $this->auditPeriodId = $value;
        } else {
            $this->auditPeriodId = 0;
            if (isset($this->viewData['alertStatic'])) {
                unset($this->viewData['alertStatic']);
            }
            unset($this->record['periode_akademik']);
            $this->units = [];
            return;
        }

        $dataPeriode = AuditPeriode::find($this->auditPeriodId);
        $this->record['periode_akademik'] = ($dataPeriode->tahun_audit - 1) . '/' . $dataPeriode->tahun_audit;

        $this->getUnitTables();

        if (empty($dataPeriode->tanggal_mulai) || empty($dataPeriode->tanggal_selesai)) {
            $this->viewData['alertStatic'] = [
                'title' => 'Jadwal Audit Mutu Internal',
                'message' => 'Silakan terlebih dahulu mengatur tanggal mulai dan tanggal selesai periode AMI pada <a class="a-link" href="' . route('spmi.audit-periode.index') . '">halaman Periode AMI </a>',
                'type' => 'warning',
            ];
        } else {
            $this->viewData['alertStatic'] = [
                'title' => 'Jadwal Audit Mutu Internal',
                'message' => 'Pastikan tanggal pengisian dan penilaian penjadwalan berada dalam rentang tanggal periode AMI <b>(' . Carbon::parse($dataPeriode->tanggal_mulai)->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($dataPeriode->tanggal_selesai)->translatedFormat('d M Y') . ')</b>',
            ];
        }
    }

    protected function defineFormFields()
    {
        return [
            ['field' => 'id_audit_periode', 'wire:change' => 'updateAuditPeriod($event.target.value)'],
            ['field' => 'nama_jadwal_audit', 'label' => 'Nama Kegiatan AMI'],
            ['field' => 'periode_akademik', 'disabled' => true],
            ['field' => 'tanggal_pengisian', 'label' => 'Tanggal Pengisian Laporan', 'component' => true, 'module' => Modul::CODE_SPMI, 'required' => true],
            ['field' => 'tanggal_penilaian', 'label' => 'Tanggal Penilaian Auditor', 'component' => true, 'module' => Modul::CODE_SPMI, 'required' => true],
            ['field' => 'apakah_audit_aktif', 'type' => 'select', 'options' => [0 => 'Tidak Aktif', 1 => 'Aktif'], 'selected' => ''],
            ['field' => 'apakah_penilaian_mandiri', 'label' => 'Mewajibkan Penilaian Mandiri', 'control' => 'radio', 'options' => [1 => 'Ya', 0 => 'Tidak']],

            // Hidden fields
            ['field' => 'tanggal_awal_pengisian', 'type' => 'hidden'],
            ['field' => 'tanggal_akhir_pengisian', 'type' => 'hidden'],
            ['field' => 'tanggal_awal_penilaian', 'type' => 'hidden'],
            ['field' => 'tanggal_akhir_penilaian', 'type' => 'hidden'],
        ];
    }

    /**
     * Digunakan ketika membutuhkan custom message validation.
     * Berdasarkan validation yang ada di model.
     *
     * @return array
     */
    protected function messageValidation()
    {
        return [
            'tanggal_akhir_pengisian.after_or_equal' => 'Tanggal akhir pengisian harus lebih besar atau sama dengan tanggal mulai pengisian',
            'tanggal_awal_penilaian.after' => 'Tanggal awal penilaian harus lebih besar tanggal akhir pengisian',
            'tanggal_akhir_penilaian.after_or_equal' => 'Tanggal akhir penilaian harus lebih besar atau sama dengan tanggal awal penilaian',
        ];
    }
}
