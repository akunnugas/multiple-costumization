<?php

namespace Modules\SPMI\Livewire;

use Livewire\WithFileUploads;
use Modules\Core\Livewire\CreateEditComponent;
use Modules\Core\Livewire\Traits\ChoicesMultiple;
use Modules\Core\Models\LembagaAkreditasi;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Services\PengisianPanduanManagementService;

class FormPengisianPanduan extends CreateEditComponent
{
    use SpmiViewData, ChoicesMultiple, WithFileUploads;

    public $tipeEdisi = null;
    public $idPengisianPanduan = null;
    public $idLembagaAkreditasi = null;
    private $listPanduanPengisian = null;
    private $listLembagaAkreditasi = null;
    protected $isMount = true;

    public function loadService()
    {
        $this->service = new PengisianPanduanManagementService;
    }

    public function loadModel()
    {
        $this->model = PengisianPanduan::class;
    }

    public function mount()
    {
        parent::mount();

        if ($this->edit) {
            $pengisianPanduan = PengisianPanduan::find($this->edit);
            $this->tipeEdisi = $pengisianPanduan->tipe_edisi;
            if ($this->tipeEdisi == AkreditasiBuku::PERFORMANCE_REPORT) {
                $this->record['id_pengisian_panduan'] = $pengisianPanduan->id_pengisian_panduan;
            } elseif ($this->tipeEdisi == AkreditasiBuku::SELF_EVALUATION) {
                $idPengisianPanduan = PengisianPanduan::where('id_pengisian_panduan', $pengisianPanduan->id)->first();
                if ($idPengisianPanduan) {
                    $this->record['id_pengisian_panduan'] = $idPengisianPanduan->id;
                }
            }
            $this->idLembagaAkreditasi = $pengisianPanduan->id_lembaga_akreditasi;
            $this->record['id_lembaga_akreditasi'] = $pengisianPanduan->id_lembaga_akreditasi;
        }
        $this->listLembagaAkreditasi = LembagaAkreditasi::options();
    }

    public function beforeRender()
    {
        $this->isMount = false;

        $this->loadData();
    }

    public function save()
    {
        $this->record['id_lembaga_akreditasi'] = $this->idLembagaAkreditasi;
        $this->record['tipe_edisi'] = $this->tipeEdisi;
        $this->record['id_pengisian_panduan'] = $this->idPengisianPanduan;

        if ($this->record['tipe_edisi'] == AkreditasiBuku::SELF_EVALUATION && $this->idPengisianPanduan) {
            $check = PengisianPanduan::where('id', $this->idPengisianPanduan)->first();
            if ($check->id_pengisian_panduan && $check->id_pengisian_panduan != $this->edit) {
                $this->alert('error', 'Panduan Laporan Kinerja sudah terhubung dengan Panduan Evaluasi Diri lain.');
                return;
            }
        } else if ($this->record['tipe_edisi'] == AkreditasiBuku::PERFORMANCE_REPORT && $this->idPengisianPanduan) {
            $check = PengisianPanduan::where('id_pengisian_panduan', $this->idPengisianPanduan)->first();
            if ($check && $check->id != $this->edit) {
                $this->alert('error', 'Panduan Evaluasi Diri sudah terhubung dengan Panduan Laporan Kinerja lain.');
                return;
            }
        }

        parent::save();
    }

    public function updatedTipeEdisi($value)
    {
        $value = $this->selectValue($value);

        $this->tipeEdisi = !empty($value) ? $value : null;

        if (in_array($value, AkreditasiBuku::TYPE)) {
            $this->record['tipe_edisi'] = $value;
            $this->record['id_pengisian_panduan'] = null;
        }
    }

    public function updatedIdPengisianPanduan($value)
    {
        $value = $this->selectValue($value);

        $this->idPengisianPanduan = !empty($value) ? $value : null;

        if (isset($this->listPanduanPengisian[$value])) {
            $this->record['id_pengisian_panduan'] = !empty($value) ? $value : null;
        }
    }

    public function updatedIdLembagaAkreditasi($value)
    {
        $value = $this->selectValue($value);

        $this->idLembagaAkreditasi = !empty($value) ? $value : null;

        if (isset($this->listLembagaAkreditasi[$value])) {
            $this->record['id_lembaga_akreditasi'] = !empty($value) ? $value : null;
        }
    }

    protected function defineFormFields()
    {
        $fields = $this->getFields();

        if ($this->isMount) {
            return $fields;
        }

        return array_values($fields);
    }

    protected function getFields()
    {
        $payload = [
            ['field' => 'kode_pengisian_panduan'],
            ['field' => 'nama_pengisian_panduan'],
            ['field' => 'nama_singkat'],
            ['field' => 'id_lembaga_akreditasi', 'wire:model.change' => 'idLembagaAkreditasi'],
            ['field' => 'tipe_edisi', 'wire:model.change' => 'tipeEdisi']
        ];

        if ($this->tipeEdisi == AkreditasiBuku::SELF_EVALUATION) {
            $this->listPanduanPengisian = PengisianPanduan::getListIndicatorPerformanceReport();
            if ($this->edit) {
                unset($this->listPanduanPengisian[$this->edit]);
            }
            $payload[] = ['field' => 'id_pengisian_panduan', 'options' => $this->listPanduanPengisian, 'label' => 'Panduan Laporan Kinerja', 'wire:model.change' => 'idPengisianPanduan'];
        } elseif ($this->tipeEdisi == AkreditasiBuku::PERFORMANCE_REPORT) {
            $this->listPanduanPengisian = PengisianPanduan::getListSelfEvaluation();
            if ($this->edit) {
                unset($this->listPanduanPengisian[$this->edit]);
            }
            $payload[] = ['field' => 'id_pengisian_panduan', 'options' => $this->listPanduanPengisian, 'label' => 'Panduan Evaluasi Diri', 'wire:model.change' => 'idPengisianPanduan'];
        }

        $payload = array_merge($payload, [
            ['field' => 'id_jenjang_pendidikan', 'label' => 'Jenjang Pendidikan'],
            ['field' => 'deskripsi', 'control' => 'textarea'],
            ['field' => 'tanggal_edisi', 'type' => 'date'],
            ['field' => 'tanggal_efektif', 'type' => 'date'],
            ['field' => 'tanggal_kadaluwarsa', 'type' => 'date'],
            ['field' => 'apakah_aktif', 'control' => 'switch', 'label' => 'Status Aktif Panduan', 'helper' => 'Aktifkan jika panduan ini digunakan dalam proses SPMI saat ini.'],
            ['field' => 'id_dokumen'],
            ['field' => 'id_tipe', 'type' => 'hidden', 'selected' => request()->type_id]
        ]);

        return $payload;
    }
}
