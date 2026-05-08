<?php

namespace Modules\SPMI\Livewire;

use Livewire\WithFileUploads;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Livewire\CreateEditComponent;
use Modules\Core\Livewire\Traits\ChoicesMultiple;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\AkreditasiStandar;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksReferensi;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\MappingPenilaianMatriks;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Services\PenilaianMatriksIKTManagementService;

class FormPenilaianMatriks extends CreateEditComponent
{
    use SpmiViewData, ChoicesMultiple, WithFileUploads;

    public $referensiPenilaian = null;
    public $kategoriPenilaian = null;
    public $penilaianPanduanId = null;
    public $jenisPenilaian = PenilaianMatriks::CATEGORY_INDICATOR;
    public $apakahParent = false;
    public $apakahIndikatorSPME = false;
    public $apakahButirIndikatorIKU = false;
    public $isHiddenSPMECustom = true;

    protected $isMount = true;

    protected function defineLastBreadcrumb()
    {
        return MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.penilaian-matriks-ikt');
    }

    public function loadService()
    {
        $this->service = new PenilaianMatriksIKTManagementService;
    }

    public function loadModel()
    {
        $this->model = PenilaianMatriks::class;
    }

    public function mount()
    {
        parent::mount();

        if (!isset($this->kategoriPenilaian) && !empty($this->edit)) {
            $data = $this->service->show($this->edit);
            $parentPenilaian = PenilaianPanduan::find($data->id_penilaian_panduan);
            $this->isHiddenSPMECustom = $parentPenilaian->apakah_data_default ?? false;
            $this->updatedApakahIndikatorSPME($data->butir_indikator_spme);
            if ($data->kategori_penilaian == PenilaianMatriks::CATEGORY_ELEMENT) {
                $this->record['apakah_parent'] = true;
                $this->apakahParent = true;
            } else {
                $this->record['apakah_parent'] = false;
                $this->apakahParent = false;
            }

            $this->updatedReferensiPenilaian($data->referensi_penilaian);
        }

        if (!isset($this->penilaianPanduanId)) {
            $this->penilaianPanduanId = $this->selectValue($this->record['id_penilaian_panduan'] ?? null);
        }

        if (!isset($this->jenisPenilaian)) {
            $this->jenisPenilaian = $this->selectValue($this->record['jenis_penilaian'] ?? null);
        }

        if (!isset($this->referensiPenilaian)) {
            $this->referensiPenilaian = $this->selectValue($this->record['referensi_penilaian'] ?? null);
        }

        $mappingPenilaian = MappingPenilaianMatriks::where('id_penilaian_matriks', $this->edit)->get();
        $this->record['program_studi'] = $mappingPenilaian->pluck('id_unit')->unique()->toArray();
        $this->record['periode_ami'] = $mappingPenilaian->pluck('id_audit_periode')->unique()->toArray();

        $this->setMultipleChoiceValues('program_studi', $this->record['program_studi'] ?? []);
        $this->setMultipleChoiceValues('periode_ami', $this->record['periode_ami'] ?? []);

        $selectedMatrix = PenilaianMatriksReferensi::getSelectedItems($this->edit);
        $this->setMultipleChoiceValues('butir_akreditasi', $selectedMatrix);
    }

    public function beforeRender()
    {
        if (!empty($this->edit) && ($this->record['apakah_data_default'] ?? false)) {
            abort(403, 'Data IKU tidak bisa diubah');
        }
        $this->isMount = false;
        $this->loadData();
    }

    public function save()
    {
        if (empty($this->selectValue($this->record['jenis_penilaian'])) && $this->kategoriPenilaian == PenilaianMatriks::CATEGORY_INDICATOR) {
            $this->alert('error', 'Jenis Penilaian tidak boleh kosong');
            return;
        }

        if ((!empty($this->choices['program_studi']) && empty($this->choices['periode_ami'])) || (empty($this->choices['program_studi']) && !empty($this->choices['periode_ami']))) {
            if (empty($this->choices['program_studi'])) {
                $this->alert('error', 'Unit Kerja harus diisi jika Periode AMI dipilih');
            } else {
                $this->alert('error', 'Periode AMI harus diisi jika Unit Kerja dipilih');
            }
            return;
        }

        $this->record['apakah_aktif'] = (int) $this->record['apakah_aktif'];
        $this->record['apakah_data_default'] = $this->record['apakah_data_default'] ?? false;

        parent::save();
    }

    public function updatedKategoriPenilaian($value)
    {
        $value = $this->selectValue($value);

        $this->kategoriPenilaian = $value;

        if ($value == PenilaianMatriks::CATEGORY_ELEMENT || $value == PenilaianMatriks::CATEGORY_DIMENSION) {
            $this->jenisPenilaian = PenilaianMatriks::TYPE_FINAL_SCORE;
            $this->record['jenis_penilaian'] = $this->jenisPenilaian;
        }

        if ($value == PenilaianMatriks::CATEGORY_INDICATOR) {
            $this->jenisPenilaian = PenilaianMatriks::TYPE_QUALITATIVE;
            $this->record['jenis_penilaian'] = $this->jenisPenilaian;
        }
    }

    public function updatedJenisPenilaian($value)
    {
        $value = $this->selectValue($value);
        $this->jenisPenilaian = $value;
    }

    public function updatedPenilaianPanduanId($value)
    {
        $value = $this->selectValue($value);
        $this->penilaianPanduanId = $value;

        if ($value) {
            $checkApakahDefault = PenilaianPanduan::where('id', $value)->value('apakah_data_default');
            if (!$checkApakahDefault) {
                $this->isHiddenSPMECustom = false;
            } else {
                $this->isHiddenSPMECustom = true;
            }
        } else {
            $this->isHiddenSPMECustom = true;
        }
    }

    public function updatedReferensiPenilaian($value)
    {
        $value = $this->selectValue($value);
        $this->referensiPenilaian = $value;
        $this->choices['butir_akreditasi'] = [];
    }

    public function updatedApakahParent($value)
    {
        $this->apakahParent = $value;
    }

    public function updatedApakahButirIndikatorIKU($value)
    {
        $this->apakahButirIndikatorIKU = $value;

        if ($value) {
            $this->apakahIndikatorSPME = true;
            $this->record['butir_indikator_spme'] = true;
        }
    }

    public function updatedApakahIndikatorSPME($value)
    {
        $this->apakahIndikatorSPME = $value;

        if (!$value) {
            $this->apakahButirIndikatorIKU = false;
            $this->record['butir_indikator_iku'] = false;
        }
    }

    protected function defineFormFields()
    {
        $fields = $this->getFields();

        if ($this->isMount) {
            return $fields;
        }

        if (!$this->penilaianPanduanId) {
            $this->penilaianPanduanId = $this->selectValue($this->record['id_penilaian_panduan'] ?? null);
        }

        $panduan = null;
        if ($this->penilaianPanduanId) {
            $panduan = PenilaianPanduan::find($this->penilaianPanduanId);
        } else {
            $fields = array_filter($fields, fn($item) => !in_array($item['field'], ['id_parent', 'apakah_parent']));
        }

        if ($panduan && $panduan->id_jenis_standar) {
            $akreditasi = AkreditasiStandar::where('id_jenis_standar', $panduan->id_jenis_standar)
                ->orderByRaw(AkreditasiStandar::OPTION_ORDER)
                ->pluck(AkreditasiStandar::OPTION_COLUMN, 'id')
                ->toArray();
            $fields = array_map(fn($item) => $item['field'] == 'id_akreditasi_standar' ? ['options' => $akreditasi] + $item : $item, $fields);
        }

        if ($panduan && $panduan->apakah_data_default) {
            $fields = array_map(fn($item) => $item['field'] == 'kategori_penilaian' ? ['options' => $this->getKategoriPenilaian($panduan->apakah_data_default)] + $item : $item, $fields);
            $fields = array_filter($fields, fn($item) => !in_array($item['field'], ['id_parent', 'apakah_parent']));
        }

        if ($panduan && !$panduan->apakah_data_default) {
            $fields = array_map(fn($item) => $item['field'] == 'id_parent' ? ['options' => $this->getParents()] + $item : $item, $fields);
        }

        if (isset($this->referensiPenilaian) && isset($this->jenisPenilaian) && isset($this->penilaianPanduanId)) {
            $accreditationItems = $this->getAccreditationItems($this->referensiPenilaian);
            if (!empty($accreditationItems)) {
                $fields = array_map(fn($item) => $item['field'] == 'butir_akreditasi' ? ['options' => $accreditationItems] + $item : $item, $fields);
            }
        }

        if (empty($this->referensiPenilaian)) {
            $fields = array_filter($fields, fn($item) => $item['field'] != 'butir_akreditasi');
        }

        if ($this->apakahParent) {
            $this->kategoriPenilaian = PenilaianMatriks::CATEGORY_ELEMENT;
            $this->record['kategori_penilaian'] = PenilaianMatriks::CATEGORY_ELEMENT;
        } else {
            $this->kategoriPenilaian = PenilaianMatriks::CATEGORY_INDICATOR;
            $this->record['kategori_penilaian'] = PenilaianMatriks::CATEGORY_INDICATOR;
        }

        if ($this->kategoriPenilaian == PenilaianMatriks::CATEGORY_INDICATOR) {
            $fields = array_map(fn($item) => $item['field'] == 'jenis_penilaian' ? ['value' => PenilaianMatriks::TYPE_QUALITATIVE] + $item : $item, $fields);
            $this->record['jenis_penilaian'] = PenilaianMatriks::TYPE_QUALITATIVE;
        }

        if ($this->kategoriPenilaian == PenilaianMatriks::CATEGORY_ELEMENT) {
            $removedFields = ['bobot_penilaian', 'referensi_penilaian', 'butir_akreditasi'];
            $fields = array_filter($fields, fn($item) => !in_array($item['field'], $removedFields));
            $fields = array_map(fn($item) => $item['field'] == 'jenis_penilaian' ? ['value' => PenilaianMatriks::TYPE_FINAL_SCORE] + $item : $item, $fields);
            $this->record['jenis_penilaian'] = PenilaianMatriks::TYPE_FINAL_SCORE;
        }

        return array_values($fields);
    }

    public function getAccreditationItems($value)
    {
        $penilaianPanduan = PenilaianPanduan::find($this->penilaianPanduanId);
        $pengisianPanduan = PengisianPanduan::where('id', $penilaianPanduan->id_laporan_kinerja)->first();
        if ($value == PenilaianMatriks::REFERENCE_SELF_EVALUATION) {
            return IndikatorEvaluasiDiri::optionsByPengisianPanduan($pengisianPanduan->id_pengisian_panduan);
        }

        if ($value == PenilaianMatriks::REFERENCE_PERFORMANCE_REPORT) {
            return IndikatorLaporanKinerja::optionsByPengisianPanduan($pengisianPanduan->id);
        }

        return [];
    }

    protected function getFields()
    {
        $units = UnitKerja::select('id', 'nama_unit', 'id_jenjang_pendidikan')
            ->with('jenjang')
            ->whereIn('jenis_unit', [UnitKerja::UNIT_NON_PRODI, UnitKerja::STUDY_PROGRAM])
            ->get();
        $units = $units->map(function ($item) {
            $jenjang = '';
            if (!empty($item->jenjang)) {
                $jenjang = $item->jenjang->kode_jenjang . ' - ';
            }

            return [
                'id' => $item->id,
                'nama_unit' => $jenjang . $item->nama_unit
            ];
        })->toArray();
        $units = array_column($units, 'nama_unit', 'id');

        $periods = AuditPeriode::select('id', 'tahun_audit')
            ->orderBy('tahun_audit', 'desc')
            ->pluck('tahun_audit', 'id')
            ->toArray();

        return [
            ['field' => 'id_penilaian_panduan', 'wire:model.change' => 'penilaianPanduanId'],
            ['field' => 'apakah_data_default', 'type' => 'hidden'],
            ['field' => 'nomor_penilaian'],
            ['field' => 'kategori_penilaian', 'type' => 'hidden'],
            ['field' => 'pertanyaan_penilaian', 'wire:ignore' => true, 'wire:key' => 'pertanyaan_penilaian'],
            ['field' => 'id_parent', 'options' => $this->getParents(), 'variant' => 'search'],
            [
                'field' => 'jenis_penilaian',
                'wire:model.change' => 'jenisPenilaian',
                'options' => PenilaianMatriks::TYPES,
                'required' => true,
                'disabled' => true,
            ],
            ['field' => 'apakah_parent', 'control' => 'switch', 'wire:model.change' => 'apakahParent', 'boolean' => true],
            ['field' => 'id_akreditasi_standar', 'required' => true],
            ['field' => 'apakah_aktif', 'control' => 'radio'],

            // Kualitatif dan Skor Akhir
            ['field' => 'bobot_penilaian', 'type' => 'number'],
            ['field' => 'referensi_penilaian', 'wire:model.change' => 'referensiPenilaian', 'options' => PenilaianMatriks::REFERENCES_WITHOUT_MIXED_EVALUATION],
            ['field' => 'butir_akreditasi', 'control' => 'select-multiple'],
            ['field' => 'deskripsi', 'wire:ignore' => true],
            ['field' => 'apakah_nilai_ditampilkan', 'control' => 'switch'],
            ['field' => 'butir_indikator_spme', 'control' => 'switch', 'wire:model.change' => 'apakahIndikatorSPME', 'type' => ($this->isHiddenSPMECustom ? 'hidden' : '')],
            ['field' => 'butir_indikator_iku', 'control' => 'switch', 'label' => 'Hitung dalam Peringkat SPME / IKU', 'wire:model.change' => 'apakahButirIndikatorIKU', 'type' => ($this->isHiddenSPMECustom ? 'hidden' : ''), 'helper' => 'Aktifkan jika butir ini digunakan dalam perhitungan Indikator Kinerja Utama (IKU) atau akreditasi SPME'],

            ['field' => 'program_studi', 'options' => $units, 'label' => 'Unit Kerja', 'control' => 'select-multiple'],
            ['field' => 'periode_ami', 'options' => $periods, 'control' => 'select-multiple'],
        ];
    }

    protected function getParents()
    {
        if (!empty($this->penilaianPanduanId)) {
            $parent = PenilaianMatriks::optionsByPenilaianPanduan($this->penilaianPanduanId, false);

            // remove self from parent options
            if (!empty($this->edit) && isset($parent[$this->edit])) {
                unset($parent[$this->edit]);
            }

            return $parent;
        }

        return [];
    }

    protected function getKategoriPenilaian($isDefaultData = false)
    {
        $kategoriPenilaian = PenilaianMatriks::CATEGORIES;

        if ($isDefaultData) {
            unset($kategoriPenilaian[PenilaianMatriks::CATEGORY_ELEMENT]);
        }

        unset($kategoriPenilaian[PenilaianMatriks::CATEGORY_DIMENSION]);

        return $kategoriPenilaian;
    }
}
