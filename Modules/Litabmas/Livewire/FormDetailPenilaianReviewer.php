<?php

namespace Modules\Litabmas\Livewire;

use Livewire\WithFileUploads;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Error as HelpersError;
use Modules\Core\Helpers\Page;
use Modules\Core\Livewire\MainComponent;
use Modules\Core\Models\Biodata;
use Modules\Litabmas\Helpers\Menu;
use Modules\Litabmas\Models\AgendaKegiatan;
use Modules\Litabmas\Models\AspekPenilaianOutputPertanyaan;
use Modules\Litabmas\Models\PengajuanPendanaanJadwalPresentasi;
use Modules\Litabmas\Models\PengajuanPendanaanLaporanProgres;
use Modules\Litabmas\Services\PengajuanPendanaanLaporanProgresService;
use Modules\Litabmas\Services\PengajuanPendanaanReviewerService;
use Modules\Litabmas\Services\PengajuanPendanaanService;
use Modules\Litabmas\Services\PenilaianIsianProposalService;
use Modules\Litabmas\Services\PenilaianPresentasiManagementService;
use Modules\Litabmas\Services\PenilaianReviewerManagementService;

class FormDetailPenilaianReviewer extends MainComponent
{
    use LitabmasViewData, WithFileUploads;

    // main state
    public $title, $subtitle;
    public $isEdit = false;
    public $permission;
    public $idProposalPendanaan, $subResource;
    public $alert, $fields, $records, $menu, $data, $currentData,
        $currentReviewer, $timelineFeedback, $timelinePresentasi, $timelineLaporanAntara, $timelineLuaran,
        $timelines, $timelineActive, $isBypassDisabled, $isCanFeedback, $isCanPresentasi, $isCanLaporanAntara, $isCanLuaran;
    public $view = 'litabmas::livewire.penilaian-reviewer.form-detail-penilaian-reviewer';

    // overview page
    public $dataIsianPenilaianProposal, $dataFeedbackPenilaianProposal, $fieldErrors;

    // penilaian aspek page
    public $dataPenilaianAspekProposal;

    // penilaian presentasi page
    public $dataPenilaianAspekPresentasiProposal, $dataJadwalProposal;

    // penilaian outpout page
    public $isHasOutputBersama, $dataOutputLuaran, $dataReviewerLuaranOnly, $dataReviewerOutputLuaran;

    // progreport page
    public $dataLaporanAntara;

    public function mount($pengajuan_pendanaan = null, $sub_resource = null)
    {
        $this->loadService();

        $this->idProposalPendanaan = $pengajuan_pendanaan;
        $this->subResource = $sub_resource;

        $check = $this->service->show($this->idProposalPendanaan);

        $this->currentData = $check->toArray();

        $myBiodata = Biodata::where('id_user', auth()->user()->id)->first();
        $this->currentReviewer = $this->service->getCurrentReviewer($myBiodata->id, $this->idProposalPendanaan);

        $pengajuanPendanaanService = new PengajuanPendanaanService;

        $this->timelines = $pengajuanPendanaanService->getTimelineStatusAgendaKegiatanByIdPengajuanPendanaan($this->idProposalPendanaan);
        list($this->timelineActive, $this->isBypassDisabled) = $pengajuanPendanaanService->getTimelineActive($this->timelines);

        $this->menu = Menu::sidebar('penilaian-reviewer-dosen');

        $this->permission = request()->permission;
        $this->urlInfo = Page::showURLInfo();
        $this->viewData = $this->defineViewData();
        $this->data = $this->defineFormFields();

        $this->getTitlePage();

        $this->loadPageData();
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

    public function render()
    {
        return $this->buildView($this->view);
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
        $this->service = new PenilaianReviewerManagementService;
    }

    private function getTitlePage()
    {
        foreach ($this->menu['items'] as $child) {
            foreach ($child['items'] as $item) {
                if ($item['path'] == ($this->menu['parent'] . '-' . $this->urlInfo['id'])) {
                    $this->title = $item['label'];
                    $this->subtitle = $child['label'];
                }
            }
        }
    }

    public function loadPageData()
    {
        // return view
        switch ($this->urlInfo['id']) {
            case 'overview':
                $penilaianIsianProposalService = new PenilaianIsianProposalService();
                $this->dataIsianPenilaianProposal = $penilaianIsianProposalService->getDataIsianPenilaianProposal($this->idProposalPendanaan);
                $this->dataFeedbackPenilaianProposal = $penilaianIsianProposalService->getDataPenilaianReviwerIsianProposal($this->idProposalPendanaan, $this->dataIsianPenilaianProposal, $this->currentReviewer->id);

                $this->loadMainFeedback();
                break;
            case 'penilaianaspek':
                $penilaianIsianProposalService = new PenilaianIsianProposalService();

                $this->dataPenilaianAspekProposal = $penilaianIsianProposalService->getAspekBobotPenilaian($this->idProposalPendanaan);

                $this->records = [
                    'usulan_anggaran' => $this->currentReviewer['rekomendasi_anggaran'] ?? null,
                    'nilai' => $this->service->getPenilaianReviewerKomposisiProposal($this->currentReviewer->id)
                ];

                $this->loadMainFeedback();
                break;
            case 'penilaianpresentasi':
                $penilaianPresentasiService = new PenilaianPresentasiManagementService;

                $this->dataJadwalProposal = PengajuanPendanaanJadwalPresentasi::where('id_pengajuan_pendanaan', $this->idProposalPendanaan)
                    ->where('tipe_presentasi', PengajuanPendanaanJadwalPresentasi::TIPE_PRESENTASI_PROPOSAL)->get();

                $this->dataPenilaianAspekPresentasiProposal = $penilaianPresentasiService->getAspekBobotPenilaianByIdPengajuan($this->idProposalPendanaan);

                $this->records = [
                    'nilai' => $this->service->getPenilaianReviewerPresentasi($this->currentReviewer->id)->pluck('skala_nilai_presentasi_proposal', 'id_aspek_penilaian_presentasi_proposal')->toArray()
                ];

                $this->loadMainPresentasi();
                break;
            case 'progreport':
                $this->dataJadwalProposal = PengajuanPendanaanJadwalPresentasi::where('id_pengajuan_pendanaan', $this->idProposalPendanaan)
                    ->where('tipe_presentasi', PengajuanPendanaanJadwalPresentasi::TIPE_PRESENTASI_LAPORAN_ANTARA)->get();

                $this->progreport_loadData();

                $this->loadMainLaporanAntara();
                break;
            case 'penilaianoutput':
                $serviceReviewer = new PengajuanPendanaanReviewerService;

                $this->dataJadwalProposal = PengajuanPendanaanJadwalPresentasi::where('id_pengajuan_pendanaan', $this->idProposalPendanaan)
                    ->where('tipe_presentasi', PengajuanPendanaanJadwalPresentasi::TIPE_PRESENTASI_LUARAN)->get();

                $this->dataOutputLuaran = $serviceReviewer->getDataOutputLuaranReviewer($this->idProposalPendanaan);

                $this->dataReviewerOutputLuaran = $serviceReviewer->getDataReviewerOutputLuaran($this->currentReviewer->id);

                foreach ($this->dataReviewerOutputLuaran as $key => $value) {
                    $value = $value[0];
                    $this->records['status'][$value->id_pengajuan_pendanaan_output_penelitian] = $value->status_penilaian_output;
                    $this->records['feedback'][$value->id_pengajuan_pendanaan_output_penelitian] = $value->feedback_output;
                }

                $this->records['komentar_umum'] = $this->currentReviewer->komentar_umum_reviewer_output;

                $this->dataReviewerLuaranOnly = $serviceReviewer->index($this->idProposalPendanaan, [
                    'apakah_review_luaran' => true,
                ]);

                $this->initFormLuaranBersama();

                $this->loadMainLuaran();
                break;
            default:
                abort(404);
                break;
        }
    }

    private function defineFormFields()
    {
        $this->currentData['kode_jenis_pendanaan'] = ucfirst($this->currentData['kode_jenis_pendanaan']);

        // define fields
        $fields = [
            'utama' => [
                'title' => 'Informasi Utama',
                'items' => [
                    ['field' => 'nama_periode_pendanaan', 'label' => 'Periode Pendanaan'],
                    ['field' => 'judul_penelitian', 'label' => 'Judul Proposal'],
                    ['field' => 'nama_klaster', 'label' => 'Klaster Pendanaan'],
                    ['field' => 'kode_jenis_pendanaan', 'label' => 'Jenis Pendanaan'],
                    ['field' => 'status_penilaian', 'label' => 'Status Proposal', 'component' => 'detail.status_penilaian_reviewer'],
                ]
            ],
        ];

        $dataAgenda = $this->service->getWaktuMulaiSelesaiAgendaKegiatan($this->idProposalPendanaan, $this->currentReviewer);

        $temp = [];
        foreach ($dataAgenda as $item) {
            $key = null;
            if ($item->kode_agenda == AgendaKegiatan::STEP_FEEDBACK_REVIEWER) {
                $key = 'Review Proposal';
            } elseif ($item->kode_agenda == AgendaKegiatan::STEP_PENILAIAN_LUARAN) {
                $key = 'Review Luaran';
            } elseif ($item->kode_agenda == AgendaKegiatan::STEP_PENILAIAN_LAPORAN_ANTARA) {
                $key = 'Review Antara';
            }

            if ($key) {
                $temp[$key] = [
                    $item->waktu_mulai,
                    $item->waktu_selesai,
                ];
            }
        }

        // add data to fields
        foreach ($fields as $index => $field) {
            foreach ($field['items'] as $key => $item)
            {
                // custom
                if($item['field'] == 'status_penilaian')
                {
                    $fields[$index]['items'][$key]['original'] = $temp;
                    $fields[$index]['items'][$key]['text'] = $this->currentReviewer->toArray();
                } else {
                    $fields[$index]['items'][$key]['original'] = $this->currentData[$item['field']] ?? null;
                    $fields[$index]['items'][$key]['text'] = $this->currentData[$item['field']] ?? null;
                }
            }
        }

        return $fields;
    }

    public function loadMainFeedback()
    {
        // get timeline feedback
        $this->timelineFeedback = array_filter($this->timelines, function ($timeline) {
            return $timeline->kode_agenda === AgendaKegiatan::STEP_FEEDBACK_REVIEWER;
        });
        $this->timelineFeedback = array_values($this->timelineFeedback);
        $this->timelineFeedback = reset($this->timelineFeedback);

        // check is can feedback
        $this->isCanFeedback = $this->timelineActive ? ($this->timelineActive->kode_agenda === AgendaKegiatan::STEP_FEEDBACK_REVIEWER && !$this->isBypassDisabled) : false;
    }

    public function loadMainPresentasi()
    {
        // get timeline feedback
        $this->timelinePresentasi = array_filter($this->timelines, function ($timeline) {
            return $timeline->kode_agenda === AgendaKegiatan::STEP_PENILAIAN_HASIL_PRESENTASI;
        });
        $this->timelinePresentasi = array_values($this->timelinePresentasi);
        $this->timelinePresentasi = reset($this->timelinePresentasi);

        // check is can feedback
        $this->isCanPresentasi = $this->timelineActive ? ($this->timelineActive->kode_agenda === AgendaKegiatan::STEP_PENILAIAN_HASIL_PRESENTASI && !$this->isBypassDisabled) : false;
    }

    public function loadMainLaporanAntara()
    {
        // get timeline feedback
        $this->timelineLaporanAntara = array_filter($this->timelines, function ($timeline) {
            return $timeline->kode_agenda === AgendaKegiatan::STEP_PENILAIAN_LAPORAN_ANTARA;
        });
        $this->timelineLaporanAntara = array_values($this->timelineLaporanAntara);
        $this->timelineLaporanAntara = reset($this->timelineLaporanAntara);

        // check is can feedback
        $this->isCanLaporanAntara = $this->timelineLaporanAntara->active ?? false;
    }

    public function loadMainLuaran()
    {
        // get timeline feedback
        $this->timelineLuaran = array_filter($this->timelines, function ($timeline) {
            return $timeline->kode_agenda === AgendaKegiatan::STEP_PENILAIAN_LUARAN;
        });
        $this->timelineLuaran = array_values($this->timelineLuaran);
        $this->timelineLuaran = reset($this->timelineLuaran);

        // check is can feedback
        $this->isCanLuaran = $this->timelineLuaran->active ?? false;
    }

    public function initAlert($type, $message)
    {
        $this->alert['type'] = $type;
        $this->alert['message'] = $message;
    }

    /* Overview Page */
    public function overview_editFeedback()
    {
        $this->isEdit = true;

        foreach ($this->dataIsianPenilaianProposal as $key => $value) {
            $this->records[$value->id] = $this->dataFeedbackPenilaianProposal[$value->id][0]->feedback_isian_proposal ?? '';
        }

        $this->loadPageData();
    }

    public function overview_cancelFeedback()
    {
        $this->isEdit = false;

        $this->loadPageData();
    }

    public function overview_setFeedback($id, $feedback)
    {
        $this->records[$id] = $feedback;

        $this->loadPageData();
    }

    public function overview_saveFeedback()
    {
        $this->loadPageData();

        $this->fieldErrors = [];

        foreach ($this->dataIsianPenilaianProposal as $key => $value) {
            if (!isset($this->records[$value->id])) {
                $this->fieldErrors[$value->id] = $value->nama . ' harus diisi';
            } else {
                if (empty($this->records[$value->id])) {
                    $this->fieldErrors[$value->id] = $value->nama . ' harus diisi';
                }
            }
        }

        if (!empty($this->fieldErrors)) {
            return;
        }

        $this->isEdit = false;

        $check = $this->service->storeFeedback($this->idProposalPendanaan, $this->dataIsianPenilaianProposal, $this->records);

        if (HelpersError::isError($check)) {
            $this->initAlert('danger', $check->message);

            return;
        }

        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();
        $this->currentReviewer = $this->service->getCurrentReviewer(auth()->user()->biodata->id, $this->idProposalPendanaan);
        $this->data = $this->defineFormFields();

        $this->initAlert('success', 'Feedback berhasil disimpan');

        $this->loadPageData();
    }
    /* End Overview Page */

    /* Penilaian Aspek Page */
    public function penilaianaspek_setNilai($id, $nilai)
    {
        $this->records['nilai'][$id] = (int) $nilai;
    }

    public function penilaianaspek_editPenilaianAspek()
    {
        $this->isEdit = true;

        // format
        $this->records['usulan_anggaran'] = number_format($this->currentReviewer->rekomendasi_anggaran, 0, ',', '.');
    }

    public function penilaianaspek_cancelPenilaianAspek()
    {
        $this->isEdit = false;

        $this->fieldErrors = [];
    }

    public function penilaianaspek_savePenilaianAspek()
    {
        // check usulan_anggaran required
        $maksAnggaran = (float) $this->currentData['maksimal_anggaran'];

        if (!isset($this->records['usulan_anggaran']) || empty($this->records['usulan_anggaran'])) {
            $this->fieldErrors['usulan_anggaran'] = 'Rekomendasi Anggaran harus diisi';

            return;
        }

        $usulanAnggaran = str_replace('.', '', $this->records['usulan_anggaran']);
        if ($usulanAnggaran > $maksAnggaran) {
            $this->fieldErrors['usulan_anggaran'] = 'Rekomendasi Anggaran tidak boleh melebihi batas pengajuan dana yaitu Rp' . number_format($maksAnggaran, 0, ',', '.');

            return;
        }

        $this->records['nilai'] = $this->records['nilai'] ?? [];

        foreach ($this->dataPenilaianAspekProposal as $penilaianAspek) {
            if (!isset($this->records['nilai'][$penilaianAspek->id]) || empty($this->records['nilai'][$penilaianAspek->id])) {
                $this->records['nilai'][$penilaianAspek->id] = 0;
            }
        }

        $check = $this->service->storePenilaianAspekProposal($this->idProposalPendanaan, $this->records);

        if (HelpersError::isError($check)) {
            $this->initAlert('danger', $check->message);

            return;
        }

        $this->fieldErrors = [];

        $this->currentReviewer->rekomendasi_anggaran = $this->records['usulan_anggaran'];

        $this->initAlert('success', 'Penilaian berhasil disimpan');

        $this->isEdit = false;

        $this->loadPageData();
    }
    /* End Penilaian Aspek Page */

    /* Penilaian Presentasi Page */
    public function penilaianpresentasi_setNilai($id, $nilai)
    {
        $this->records['nilai'][$id] = (int) $nilai;
    }

    public function penilaianpresentasi_editPenilaianAspek()
    {
        $this->isEdit = true;
    }

    public function penilaianpresentasi_cancelPenilaianAspek()
    {
        $this->isEdit = false;
    }

    public function penilaianpresentasi_savePenilaianAspek()
    {
        $this->records['nilai'] = $this->records['nilai'] ?? [];

        foreach ($this->dataPenilaianAspekPresentasiProposal as $penilaianAspek) {
            if (!isset($this->records['nilai'][$penilaianAspek->id]) || empty($this->records['nilai'][$penilaianAspek->id])) {
                $this->records['nilai'][$penilaianAspek->id] = 0;
            }
        }

        $check = $this->service->storePenilaianAspekPresentasi($this->idProposalPendanaan, $this->records);

        if (HelpersError::isError($check)) {
            $this->initAlert('danger', $check->message);

            return;
        }

        $this->initAlert('success', 'Penilaian berhasil disimpan');

        $this->isEdit = false;

        $this->loadPageData();
    }
    /* End Penilaian Aspek Page */

    /* Penilaian Laporan Antara Page */
    public function progreport_setFeedback($key, $feedback)
    {
        $this->records['feedback'][$key] = $feedback;
    }

    public function progreport_loadData()
    {
        $pengajuanPendanaanProgressService = new PengajuanPendanaanLaporanProgresService();
        $dataLapProgress = $pengajuanPendanaanProgressService->getDataLaporanProgress($this->idProposalPendanaan, $this->currentReviewer->id);
        $dataLapKeuangan = $pengajuanPendanaanProgressService->getDataLapKeuangan($this->idProposalPendanaan, $this->currentReviewer->id);

        $this->records = [
            'komentar_umum' => $this->currentReviewer->komentar_umum_reviewer_progress_report ?? null,
        ];

        $this->records['feedback'] = [
            PengajuanPendanaanLaporanProgres::JENIS_LAP_PROGRES => $dataLapProgress->feedback ?? null,
            PengajuanPendanaanLaporanProgres::JENIS_LAP_KEUANGAN_SEMENTARA => $dataLapKeuangan->feedback ?? null,
        ];

        $this->records['status'] = [
            PengajuanPendanaanLaporanProgres::JENIS_LAP_PROGRES => $dataLapProgress->status ?? null,
            PengajuanPendanaanLaporanProgres::JENIS_LAP_KEUANGAN_SEMENTARA => $dataLapKeuangan->status ?? null,
        ];

        $this->fields = [
            [
                'jenis_laporan_progres' => PengajuanPendanaanLaporanProgres::JENIS_LAP_PROGRES,
                'id_dokumen_laporan_progress' => $dataLapProgress->id_dokumen_laporan_progres ?? null,
                'feedback_reviewer' => $dataLapProgress->feedback ?? null,
                'status' => $dataLapProgress->status ?? null,
            ],
            [
                'jenis_laporan_progres' => PengajuanPendanaanLaporanProgres::JENIS_LAP_KEUANGAN_SEMENTARA,
                'id_dokumen_laporan_progress' => $dataLapKeuangan->id_dokumen_laporan_progres ?? null,
                'feedback_reviewer' => $dataLapKeuangan->feedback ?? null,
                'status' => $dataLapKeuangan->status ?? null,
            ],
        ];
    }

    public function progreport_setStatusPenilaian($jenis, $value)
    {
        $this->records['status'][$jenis] = $value;
    }

    public function progreport_setKomentarUmum($komentar)
    {
        $this->records['komentar_umum'] = $komentar;
    }

    public function progreport_editPenilaianAntara()
    {
        $this->isEdit = true;

        $this->progreport_loadData();
    }

    public function progreport_cancelPenilaianAntara()
    {
        $this->isEdit = false;
    }

    public function progreport_savePenilaianAntara()
    {
        $this->fieldErrors = [];
        foreach ($this->fields as $key => $value) {
            if (!isset($this->records['feedback'][$value['jenis_laporan_progres']]) ||
                empty($this->records['feedback'][$value['jenis_laporan_progres']])) {
                $this->fieldErrors[$value['jenis_laporan_progres']] = 'Feedback harus diisi';
            }

            if (!isset($this->records['status'][$value['jenis_laporan_progres']]) ||
                empty($this->records['status'][$value['jenis_laporan_progres']])) {
                $this->fieldErrors[$value['jenis_laporan_progres']] = 'Status harus diisi';
            }
        }

        if (!empty($this->fieldErrors)) {
            return;
        }

        $check = $this->service->storePenilaianLaporanAntara($this->idProposalPendanaan, $this->currentReviewer->id, $this->records);

        if (HelpersError::isError($check)) {
            $this->initAlert('danger', $check->message);

            return;
        }

        $this->fieldErrors = [];

        $this->currentReviewer->komentar_umum_reviewer_progress_report = $this->records['komentar_umum'];

        $this->initAlert('success', 'Penilaian berhasil disimpan');

        $this->isEdit = false;

        $this->loadPageData();
    }
    /* End Penilaian Aspek Page */

    /* Penilaian Luaran Page */
    public function penilaianoutput_setFeedback($key, $feedback)
    {
        $this->records['feedback'][$key] = $feedback;
    }

    public function penilaianoutput_setStatusPenilaian($jenis, $value)
    {
        $this->records['status'][$jenis] = $value;
    }

    public function penilaianoutput_setKomentarUmum($komentar)
    {
        $this->records['komentar_umum'] = $komentar;
    }

    public function penilaianoutput_editLuaran()
    {
        $this->isEdit = true;
    }

    public function penilaianoutput_cancelLuaran()
    {
        $this->isEdit = false;
    }

    public function penilaianoutput_saveLuaran()
    {
        $this->fieldErrors = [];
        foreach ($this->dataOutputLuaran as $key => $value){
            if (!isset($this->records['feedback'][$value->id]) || empty($this->records['feedback'][$value->id])) {
                $this->fieldErrors['feedback_'.$value->id] = 'Feedback harus diisi';
            }

            if (!isset($this->records['status'][$value->id]) || empty($this->records['status'][$value->id])) {
                $this->fieldErrors['status_'.$value->id] = 'Status harus diisi';
            }
        }

        if (!empty($this->fieldErrors)) {
            return;
        }

        $check = $this->service->storePenilaianLuaran($this->idProposalPendanaan, $this->currentReviewer->id, $this->records);

        if (HelpersError::isError($check)) {
            $this->initAlert('danger', $check->message);

            return;
        }

        $this->currentReviewer->komentar_umum_reviewer_output = $this->records['komentar_umum'];

        $this->fieldErrors = [];

        $this->initAlert('success', 'Penilaian berhasil disimpan');

        $this->isEdit = false;

        $this->loadPageData();
    }

    public function initFormLuaranBersama()
    {
        $dataAspekOutputLuaran = AspekPenilaianOutputPertanyaan::where('id_periode_pendanaan', $this->currentData['id_periode_pendanaan'])->get();

        list($listJawabanBersama, $listJawaban) = $this->service->getDataJawabanBersamaDanAspek($this->idProposalPendanaan, $dataAspekOutputLuaran->pluck('id')->toArray());

        foreach ($dataAspekOutputLuaran as $pertanyaan) {
            $jawaban = $listJawabanBersama->where('id_aspek_penilaian_output_pertanyaan', $pertanyaan->id)->first();

            if (!$this->isHasOutputBersama && $jawaban) {
                $this->isHasOutputBersama = true;
            }

            $this->records['jawaban[' . $pertanyaan->id . ']'] = $jawaban->id_aspek_penilaian_output_jawaban ?? null;
        }

        $this->fields = [];
        foreach ($dataAspekOutputLuaran as $pertanyaan) {
            $gJawaban = $listJawaban[$pertanyaan->id] ?? [];
            $gJawaban = array_column($gJawaban, 'jawaban_penilaian_output', 'id');

            $this->fields[] = [
                'field' => 'jawaban[' . $pertanyaan->id . ']',
                'label' => $pertanyaan->pertanyaan_penilaian_output,
                'control' => 'radio',
                'inline' => false,
                'required' => true,
                'wire:model.live' => 'records.jawaban[' . $pertanyaan->id . ']',
                'options' => $gJawaban,
            ];
        }
    }

    public function penilaianoutput_editLuaranBersama()
    {
        $this->showModal('modal-output-bersama');
    }

    public function penilaianoutput_saveLuaranBersama()
    {
        $this->validateForm();

        $data = array_filter($this->records, function ($key) {
            return strpos($key, 'jawaban') !== false;
        }, ARRAY_FILTER_USE_KEY);

        $check = $this->service->storePenilaianLuaranBersama($this->idProposalPendanaan, $this->currentReviewer->id, $data);

        if (HelpersError::isError($check)) {
            $this->initAlert('danger', $check->message);

            $this->hideModal('modal-output-bersama');

            return;
        }

        $this->initAlert('success', 'Penilaian berhasil disimpan');

        $this->hideModal('modal-output-bersama');

        $this->loadPageData();
    }
    /* End Penilaian Aspek Page */
}
