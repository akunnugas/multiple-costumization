<?php

namespace Modules\SPMI\Livewire;

use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Livewire\CreateEditComponent;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\MappingLK;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Services\IndikatorLaporanKinerjaManagementService;

class FormIndikatorLaporanKinerja extends CreateEditComponent
{
    use SpmiViewData;

    public $parentId;
    public $parents;
    public $title = 'Tambah Indikator Laporan Kinerja Tambahan';

    protected function defineLastBreadcrumb()
    {
        return MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.indikator-laporan-kinerja');
    }

    public function loadService()
    {
        $this->service = new IndikatorLaporanKinerjaManagementService();
    }

    public function loadModel()
    {
        $this->model = IndikatorLaporanKinerja::class;
    }

    public function beforeRender()
    {
        $parentId = $this->selectValue($this->record['id_pengisian_panduan']);
        if (!empty($parentId)) {
            $cond[] = ['id_pengisian_panduan', '=', $parentId];

            if (empty($this->edit)) {
                $cond[] = ['apakah_data_default', '=', false];
            } else {
                $cond[] = ['apakah_data_default', '=', $this->record['apakah_data_default'] ?? false];
            }
            $cond[] = ['apakah_parent', '=', true];

            $mappingLK = MappingLK::where('id_indikator_laporan_kinerja', $this->edit)->get();
            $this->record['program_studi'] = $mappingLK->pluck('id_unit')->toArray();
            $this->record['periode_ami'] = $mappingLK->pluck('id_audit_periode')->unique()->toArray();

            $this->parents = IndikatorLaporanKinerja::getListComboTree($cond);
            $this->title = 'Edit Indikator Laporan Kinerja Tambahan';
        }

        $this->loadData();
    }

    public function updatedParentId($value)
    {
        $cond = [];
        $value = $this->selectValue($value);
        if (!empty($value)) {
            $cond[] = ['id_pengisian_panduan', '=', $value];
            $cond[] = ['apakah_data_default', '=', false];
            $this->parents = IndikatorLaporanKinerja::getListComboTree($cond);
        } else {
            $this->parents = [];
        }
        $this->loadData();
    }

    protected function defineFormFields()
    {
        $listPR = PengisianPanduan::getListIndicatorPerformanceReport();
        $listData = $this->parents ?? [];

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
            [
                'field' => 'id_pengisian_panduan', 'options' => $listPR,
                'wire:model.change' => 'parentId', 'value' => $this->parentId,
                'label' => 'Panduan Pengisian'
            ],
            ['field' => 'id_parent', 'options' => $listData, 'label' => 'Butir Parent'],
            ['field' => 'nomor_indikator', 'required' => true],
            ['field' => 'nama_indikator_laporan_kinerja', 'required' => true],
            ['field' => 'deskripsi'],
            ['field' => 'informasi'],
            ['field' => 'program_studi', 'options' => $units, 'control' => 'select-multiple-v2'],
            ['field' => 'periode_ami', 'options' => $periods, 'control' => 'select-multiple-v2'],
            // ['field' => 'jenis_form'],
            // ['field' => 'apakah_layout_fixed', 'control' => 'switch'],
            // ['field' => 'apakah_menggunakan_kategori', 'control' => 'switch'],
            // ['field' => 'apakah_memasukkan_kategori_manual', 'control' => 'switch'],
            // ['field' => 'apakah_menggunakan_ts', 'control' => 'switch'],
            // ['field' => 'apakah_subfooter', 'control' => 'switch'],
            // ['field' => 'sumber_data'],
            // ['field' => 'deskripsi_sumber_data'],
            // ['field' => 'apakah_import_excel', 'control' => 'switch'],
            ['field' => 'dapat_dilihat_pada_laporan', 'control' => 'switch'],
            ['field' => 'dapat_lihat_nama_pada_laporan', 'control' => 'switch'],
            // ['field' => 'jenis_layout'],
            ['field' => 'apakah_parent', 'control' => 'switch'],
            ['field' => 'apakah_aktif', 'control' => 'switch'],
            // ['field' => 'apakah_data_default', 'type' => 'hidden'],
        ];
    }
}
