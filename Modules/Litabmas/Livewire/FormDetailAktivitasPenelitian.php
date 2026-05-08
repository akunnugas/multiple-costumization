<?php

namespace Modules\Litabmas\Livewire;

use Modules\Core\Helpers\Page;
use Modules\Core\Livewire\MainComponent;
use Modules\Litabmas\Helpers\Menu;
use Illuminate\Support\Facades\View;
use Livewire\WithFileUploads;
use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Error as HelpersError;
use Modules\Core\Models\Biodata;
use Modules\Litabmas\Models\PengajuanPendanaanAnggota;
use Modules\Litabmas\Models\PengajuanPendanaanStatus2;
use Modules\Litabmas\Services\PengajuanPendanaanAktivitasPenelitianService;
use Modules\Litabmas\Services\PengajuanPendanaanService;
use Modules\Litabmas\Models\PenilaianPembimbingAktivitasPenelitian;

class FormDetailAktivitasPenelitian extends MainComponent
{
    use LitabmasViewData, WithFileUploads;

    public $title, $subtitle;
    public $idProposalPendanaan;
    public $permission;
    public $disableSubmit = false;
    public $isEdit = false;
    public $dataPenelitiDosen, $dataPenelitiMahasiswa;
    public $menu, $data, $currentData, $fields, $records, $timelines, $timelineActive, $isBypassDisabled, $isLolosPendanaan;

    public $dataAktivitasPenelitian;

    public $view = 'litabmas::livewire.bimbingan.form-detail-aktivitas-penelitian';

    public function boot()
    {
        parent::boot();

        $this->disableSubmit = true;
    }

    public function mount($id_pengajuan_pendanaan = null)
    {
        $this->loadService();

        $this->idProposalPendanaan = $id_pengajuan_pendanaan;

        $check = $this->service->show($this->idProposalPendanaan);

        if (HelpersError::isError($check)) {
            abort(404);
        }

        $this->currentData = $check->toArray();

        $myBiodata = Biodata::where('id_user', auth()->user()->id)->first();
        if ($myBiodata->id != $this->currentData['id_pembimbing']) {
            abort(404);
        }

        $this->menu = Menu::sidebar('bimbingan');

        $this->permission = request()->permission;
        $this->urlInfo = Page::showURLInfo();
        $this->viewData = $this->defineViewData();

        if (PengajuanPendanaanStatus2::where('status', PengajuanPendanaanStatus2::LEVEL10_LOLOS_PENDANAAN)
            ->where('id_pengajuan_pendanaan', $this->idProposalPendanaan)->exists()
        ) {
            $this->isLolosPendanaan = true;
        } else if (PengajuanPendanaanStatus2::where('status', PengajuanPendanaanStatus2::LEVEL10_TIDAK_LOLOS_PENDANAAN)
            ->where('id_pengajuan_pendanaan', $this->idProposalPendanaan)->exists()
        ) {
            $this->isLolosPendanaan = false;
        } else {
            $this->isLolosPendanaan = null;
        }

        $this->timelines = $this->service->getTimelineStatusAgendaKegiatanByIdPengajuanPendanaan($this->idProposalPendanaan);

        list($this->timelineActive, $this->isBypassDisabled) = $this->service->getTimelineActive($this->timelines);

        $this->getTitlePage();

        $this->loadPageData();

        $this->data = $this->defineFormFields();
    }

    private function getTitlePage()
    {
        $this->title = 'Detail Bimbingan PPM';
        $this->subtitle = 'Bimbingan';
    }

    private function validateForm()
    {
        foreach ($this->fields as $index => $field) {
            if (isset($field['required']) && $field['required']) {
                if (!isset($this->records[$field['field']]) || empty($this->records[$field['field']])) {
                    $this->throwValidationError($field['field'], $field['label'] . ' tidak boleh kosong');
                }
            }

            if (isset($field['type']) && $field['type'] == 'url') {
                if (!filter_var($this->records[$field['field']], FILTER_VALIDATE_URL)) {
                    $this->throwValidationError($field['field'], $field['label'] . ' harus berupa URL yang valid');
                }
            }
        }
    }

    private function throwValidationError($column, $message)
    {
        $error = ValidationException::withMessages([
            $column => $message,
        ]);

        throw $error;
    }

    public function showModal($id)
    {
        $this->dispatch('show-form-modal', [
            'id' => $id,
            'fields' => $this->fields,
        ]);
    }

    public function hideModal($id)
    {
        $this->dispatch('hide-form-modal', [
            'id' => $id,
        ]);
    }


    private function defineFormFields()
    {
        list($txtKetua, $txtAnggota) = $this->service->getTxtDataPeneliti($this->dataPenelitiDosen, $this->dataPenelitiMahasiswa);

        $this->currentData['nama_ketua'] = $txtKetua;
        $this->currentData['nama_anggota'] = $txtAnggota;

        // define fields
        $fields = [
            'utama' => [
                'title' => 'Informasi Utama',
                'items' => [
                    ['field' => 'nama_periode_pendanaan', 'label' => 'Periode Pendanaan'],
                    ['field' => 'judul_penelitian', 'label' => 'Judul Proposal'],
                    ['field' => 'nama_klaster', 'label' => 'Klaster Pendanaan'],
                    ['field' => 'nama_sumber_pendanaan', 'label' => 'Sumber Pendanaan'],
                    ['field' => 'nama_ketua', 'label' => 'Ketua Proposal'],
                    ['field' => 'nama_anggota', 'label' => 'Anggota Proposal'],
                    ['field' => 'id_sk_pembimbing', 'label' => 'SK Pembimbing', 'showSimpleFile' => true],
                ]
            ],
        ];

        // add data to fields
        foreach ($fields as $index => $field) {
            foreach ($field['items'] as $key => $item) {
                $fields[$index]['items'][$key]['original'] = $this->currentData[$item['field']] ?? null;
                $fields[$index]['items'][$key]['text'] = $this->currentData[$item['field']] ?? null;
            }
        }

        return $fields;
    }

    private function loadPageData()
    {
        $aktifitasService = new PengajuanPendanaanAktivitasPenelitianService;

        $this->dataAktivitasPenelitian = $aktifitasService->getDaftarByIdPengajuanPendanaan($this->idProposalPendanaan);

        $this->fields = [];

        // get data peneliti
        $peneliti = $this->service->getDetailPenelitiByIdPengajuanPendanaan($this->idProposalPendanaan);
        $this->dataPenelitiDosen = $peneliti->whereIn(
            'jenis_anggota',
            [
                PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL,
                PengajuanPendanaanAnggota::JENIS_DOSEN_EKSTERNAL
            ]
        )->toArray();
        $this->dataPenelitiMahasiswa = $peneliti->where('jenis_anggota', PengajuanPendanaanAnggota::JENIS_MAHASISWA)->toArray();
    }

    protected function buildView($page)
    {
        View::share($this->viewData + [
            'isLivewire' => true,
            'permission' => $this->permission,
            'urlInfo' => $this->urlInfo,
            'data' => $this->data,
            'headerClass' => 'header_position-static',
        ]);

        return view($this->view)->layout('core::components.layouts.main-outer');
    }

    public function loadService()
    {
        $this->service = new PengajuanPendanaanService;
    }

    public function initAlert($type, $message)
    {
        $this->alert['type'] = $type;
        $this->alert['message'] = $message;
    }

    public function render()
    {
        return $this->buildView($this->view);
    }

    public function initFormField()
    {
        $this->fields = [
            [
                'field' => 'feedback',
                'label' => 'Feedback Pembimbing',
                'required' => true,
                'control' => 'textarea',
                'wire:model.live' => 'records.feedback',
            ]
        ];

        $this->fields[] =
            [
                'field' => 'id_dokumen',
                'label' => 'Upload Dokumen Feedback',
                'file_type' => ['pdf'],
                'max_size' => (1024 * 5),
                'wire:model.live' => 'records.id_dokumen',
            ];

        $this->disableSubmit = true;
    }

    public function updatedRecordsFeedback()
    {
        $this->disableSubmit = false;
    }

    public function updatedRecordsIdDokumen()
    {
        $this->disableSubmit = false;
    }

    public function bimbingan_addForm($id)
    {
        $this->isEdit = true;

        $aktivitasPenelitian = PenilaianPembimbingAktivitasPenelitian::where('id_pengajuan_pendanaan_aktivitas_penelitian', $id)->first();

        $this->records = [
            'id_aktivitas_penelitian' => $id,
            'feedback' => $aktivitasPenelitian ? $aktivitasPenelitian->feedback_logbook : '',
            'id_dokumen' => $aktivitasPenelitian ? $aktivitasPenelitian->id_dokumen_feedback_logbook : '',
        ];

        $this->initFormField();

        $this->showModal('modal-bimbingan');
    }

    public function bimbingan_submitForm()
    {
        $this->validateForm();

        $this->service->updateAktivitasPenelitian($this->idProposalPendanaan, $this->records);

        $this->loadPageData();

        $this->isEdit = false;

        $this->hideModal('modal-bimbingan');

        $this->initAlert('success', 'Bimbingan berhasil disimpan');
    }
}
