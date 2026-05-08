<?php

namespace Modules\SPMI\Livewire;

use Modules\Core\Livewire\CreateEditComponent;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\MappingLED;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Services\IndikatorEvaluasiDiriManagementService;

class FormIndikatorEvaluasiDiri extends CreateEditComponent
{
    use SpmiViewData;

    public $parentId;
    public $parents;
    public $title = 'Tambah Indikator Laporan Evaluasi Diri Tambahan';

    public function loadService()
    {
        $this->service = new IndikatorEvaluasiDiriManagementService();
    }

    public function loadModel()
    {
        $this->model = IndikatorEvaluasiDiri::class;
    }

    public function beforeRender()
    {
        if (!empty($this->edit)) {
            $data = (new $this->model)->find($this->edit);

            if ($data->apakah_data_default) {
                abort(403, 'Data IKU tidak bisa diubah');
            }
        }

        $parentId = $this->selectValue($this->record['id_pengisian_panduan']);
        if (!empty($parentId)) {
            $cond[] = ['id_pengisian_panduan', '=', $parentId];

            if (empty($this->edit)) {
                $cond[] = ['apakah_data_default', '=', false];
            } else {
                $cond[] = ['apakah_data_default', '=', $this->record['apakah_data_default'] ?? false];
            }
            $cond[] = ['apakah_parent', '=', true];

            $mappingLK = MappingLED::where('id_indikator_evaluasi_diri', $this->edit)->get();
            $this->record['program_studi'] = $mappingLK->pluck('id_unit')->toArray();
            $this->record['periode_ami'] = $mappingLK->pluck('id_audit_periode')->unique()->toArray();
            $this->parents = IndikatorEvaluasiDiri::getListComboTree($cond);
            $this->title = 'Edit Indikator Laporan Evaluasi Diri Tambahan';
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
            $this->parents = IndikatorEvaluasiDiri::getListComboTree($cond);
        } else {
            $this->parents = [];
        }
        $this->loadData();
    }

    protected function defineFormFields()
    {
        $listPR = PengisianPanduan::getListSelfEvaluation();
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
                'label' => 'Panduan Pengisian',
                'wire:model.change' => 'parentId', 'value' => $this->parentId
            ],
            ['field' => 'id_parent', 'options' => $listData, 'label' => 'Butir Parent'],
            ['field' => 'nomor_indikator'],
            ['field' => 'nama_indikator_evaluasi_diri'],
            ['field' => 'deskripsi', 'control' => 'textarea'],
            ['field' => 'program_studi', 'options' => $units, 'control' => 'select-multiple-v2'],
            ['field' => 'periode_ami', 'options' => $periods, 'control' => 'select-multiple-v2'],
            // ['field' => 'apakah_komentar', 'control' => 'switch'],
            // ['field' => 'apakah_key_point', 'control' => 'switch'],
            ['field' => 'apakah_parent', 'control' => 'switch'],
            ['field' => 'apakah_aktif', 'control' => 'switch']
        ];
    }
}
