<?php

namespace Modules\Litabmas\Livewire;

use Livewire\WithFileUploads;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Error as HelpersError;
use Modules\Core\Helpers\Page;
use Modules\Core\Livewire\MainComponent;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\Biodata;
use Modules\Litabmas\Helpers\Menu;
use Modules\Core\Models\Pegawai;
use Modules\Gate\Models\Role;
use Modules\Litabmas\Models\AgendaKegiatan;
use Modules\Litabmas\Models\DosenEksternal;
use Modules\Litabmas\Models\JenisOutcomePenelitian;
use Modules\Litabmas\Models\KlasterPendanaanOutcomePenelitian;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaanReviewer;
use Modules\Litabmas\Models\PengajuanPendanaanAnggota;
use Modules\Litabmas\Models\PengajuanPendanaanJadwalPresentasi;
use Modules\Litabmas\Models\PengajuanPendanaanPembimbing;
use Modules\Litabmas\Models\PengajuanPendanaanReviewerKegiatan;
use Modules\Litabmas\Models\PengajuanPendanaanStatus2;
use Modules\Litabmas\Models\PengajuanPendanaanAktivitasPenelitian;
use Modules\Litabmas\Models\SumberPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaanPublikasiArtikel;
use Modules\Litabmas\Models\PengajuanPendanaanPublikasiBuku;
use Modules\Litabmas\Services\PengajuanPendanaanAktivitasPenelitianService;
use Modules\Litabmas\Services\PengajuanPendanaanJadwalPresentasiService;
use Modules\Litabmas\Services\PengajuanPendanaanLaporanOutputService;
use Modules\Litabmas\Services\PengajuanPendanaanPembimbingService;
use Modules\Litabmas\Services\PengajuanPendanaanLaporanProgresService;
use Modules\Litabmas\Services\PengajuanPendanaanPublikasiArtikelService;
use Modules\Litabmas\Services\PengajuanPendanaanPublikasiBukuService;
use Modules\Litabmas\Services\PengajuanPendanaanReviewerService;
use Modules\Litabmas\Services\PengajuanPendanaanService;
use Modules\Litabmas\Services\PenilaianIsianProposalService;
use Modules\Litabmas\Services\PenilaianPresentasiManagementService;
use Modules\Litabmas\Services\PenilaianReviewerManagementService;

class FormDetailProposal extends MainComponent
{
    use LitabmasViewData, WithFileUploads;

    // main state
    public $title, $subtitle;
    public $isEdit = false;
    public $isEditNilai = false;
    public $permission;
    public $isDosen;
    public $removeId, $removeTitle, $removeFunction;
    public $idProposalPendanaan, $subResource;
    public $alert, $fields, $records, $menu, $data, $currentData, $currentAnggota, $timelines, $timelineActive, $isBypassDisabled;
    public $view = 'litabmas::livewire.pengajuan-pendanaan.form-detail-proposal';
    public $disableSubmit = false;

    // overview page
    public $msgConfirmation, $confirmationFunction;
    public $dataPenelitiDosen, $dataPenelitiMahasiswa, $dataNilaiKomposisiProposal, $dataNilaiPresentasiProposal, $dataReviewerProposalOnly;
    public $finalScorePresentasi, $finalScoreKomposisi;
    public $dataIsianProposal;
    public $anggaranError = '';
    public $isLolosAdministrasi = null;
    public $isLolosNominasi = false;
    public $isLolosPendanaan = false;
    public $isMenungguAnggota = false;
    // revproposal page
    public $dataPenilaianAspekProposal, $dataReviewerReviewProposal, $dataNilaiAspekReviewer,
    $dataIsianPenilaianProposal, $dataFeedbackPenilaianProposal;
    // jadwalproposal page
    public $dataJadwalProposal;
    // nilaipresensi page
    public $dataPenilaianPresentasiProposal;
    // outputpenilitian page
    public $dataOutputPenelitian, $dataAspekPenilaianOutput, $dataReviewerLuaranOnly, $dataPenilaianReviewerOutput, $dataPenilaianReviewerLuaran;
    // publikasi page
    public $activeTab = 1;
    public $dataArtikelPublikasi;
    public $dataBukuPublikasi;
    // reviewer page
    public $dataReviewer;
    // pembimbing page
    public $dataPembimbing;
    // similarity page
    public $titleSimilarity, $dataSumberPendanaan;
    // aktpenelitian page
    public $dataAktivitasPenelitian;
    // progreport page
    public $dataLaporanAntara, $dataReviewerAntaraOnly;

    public function boot()
    {
        parent::boot();

        $this->disableSubmit = true;
    }

    public function mount($pengajuan_pendanaan = null, $sub_resource = null)
    {
        $this->loadService();

        $this->isDosen = in_array(auth()->user()?->kode_role, [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL]);

        $this->idProposalPendanaan = $pengajuan_pendanaan;
        $this->subResource = $sub_resource;

        $check = $this->service->show($this->idProposalPendanaan);

        $this->currentData = $check->toArray();

        // pengecekan apabila status proposal draft
        if (
            !$this->isDosen && $this->currentData['status_agenda_kegiatan']
            === PengajuanPendanaanStatus2::LEVEL1_DRAFT
        ) {
            abort(404);
        }

        $idBiodata = auth()->user()->biodata->id ?? null;
        if ($idBiodata) {
            $this->currentAnggota = $this->service->getKeanggotaanProposal($this->idProposalPendanaan, $idBiodata);
        }

        $this->timelines = $this->service->getTimelineStatusAgendaKegiatanByIdPengajuanPendanaan($this->idProposalPendanaan);

        list($this->timelineActive, $this->isBypassDisabled) = $this->service->getTimelineActive($this->timelines);

        $this->loadStateStatus();

        $this->menu = Menu::sidebar('pengajuan-pendanaan-' . ($this->isDosen ? 'dosen' : 'admin'));

        $this->permission = request()->permission;
        $this->urlInfo = Page::showURLInfo();
        $this->viewData = $this->defineViewData();

        $this->getTitlePage();

        $this->loadPageData();

        $this->data = $this->defineFormFields();
    }

    public function render()
    {
        return $this->buildView($this->view);
    }

    public function loadStateStatus()
    {
        $statuses = $this->service->getStateStatus($this->idProposalPendanaan);

        $this->isLolosAdministrasi = null;
        $this->isLolosNominasi = null;
        $this->isLolosPendanaan = null;

        // Proses status administrasi
        // Proposal dianggap lolos administrasi jika ada status LOLOS_ADMINISTRASI atau PENINJAUAN_PROPOSAL
        if (
            in_array(PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI, $statuses) ||
            in_array(PengajuanPendanaanStatus2::LEVEL7_PENINJAUAN_PROPOSAL, $statuses)
        ) {
            $this->isLolosAdministrasi = true;
        } else if (in_array(PengajuanPendanaanStatus2::LEVEL5_TIDAK_LOLOS_ADMINISTRASI, $statuses)) {
            $this->isLolosAdministrasi = false;
        }

        // Proses status nominasi
        if (in_array(PengajuanPendanaanStatus2::LEVEL8_LOLOS_NOMINASI, $statuses)) {
            $this->isLolosNominasi = true;
        } else if (in_array(PengajuanPendanaanStatus2::LEVEL8_TIDAK_LOLOS_NOMINASI, $statuses)) {
            $this->isLolosNominasi = false;
        }

        // Proses status pendanaan
        if (in_array(PengajuanPendanaanStatus2::LEVEL10_LOLOS_PENDANAAN, $statuses)) {
            $this->isLolosPendanaan = true;
        } else if (in_array(PengajuanPendanaanStatus2::LEVEL10_TIDAK_LOLOS_PENDANAAN, $statuses)) {
            $this->isLolosPendanaan = false;
        }
    }

    public function loadPageData()
    {
        // cek keanggotaan
        if (
            $this->isDosen && (!$this->currentAnggota || ($this->currentAnggota['apakah_ketua'] === false
                && $this->currentData['status_agenda_kegiatan'] === PengajuanPendanaanStatus2::LEVEL1_DRAFT))
        ) {
            abort(404);
        }

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

        $serviceReviewer = new PengajuanPendanaanReviewerService;
        $this->dataReviewerReviewProposal = $serviceReviewer->index($this->idProposalPendanaan);
        $this->dataReviewerProposalOnly = $serviceReviewer->index($this->idProposalPendanaan, [
            'apakah_review_proposal' => true,
            'apakah_admin' => false,
        ]);

        $serviceAspek = new PenilaianReviewerManagementService;
        $this->dataNilaiPresentasiProposal = $serviceAspek->getPenilaianReviewerPresentasiProposal();
        $this->dataNilaiKomposisiProposal = $serviceAspek->getPenilaianReviewerKomposisiProposal();

        $penilaianPresentasiService = new PenilaianPresentasiManagementService;
        $this->dataPenilaianPresentasiProposal = $penilaianPresentasiService->getAspekBobotPenilaianByIdPengajuan($this->idProposalPendanaan);

        $penilaianIsianProposalService = new PenilaianIsianProposalService();
        $this->dataPenilaianAspekProposal = $penilaianIsianProposalService->getAspekBobotPenilaian($this->idProposalPendanaan);

        // Hitung total nilai presentasi
        $totalNilaiPresentasi = $this->service->hitungTotalNilai(
            $this->dataPenilaianPresentasiProposal,
            $this->dataReviewerProposalOnly,
            $this->dataNilaiPresentasiProposal,
            'bobot_pertanyaan_presentasi_proposal'
        );

        // Hitung total nilai komposisi
        $totalNilaiKomposisi = $this->service->hitungTotalNilai(
            $this->dataPenilaianAspekProposal,
            $this->dataReviewerProposalOnly,
            $this->dataNilaiKomposisiProposal,
            'bobot_komposisi_proposal'
        );

        $this->finalScorePresentasi = $this->service->getFinalScore($totalNilaiPresentasi, $this->dataReviewerProposalOnly);
        $this->finalScoreKomposisi = $this->service->getFinalScore($totalNilaiKomposisi, $this->dataReviewerProposalOnly);

        $this->records = [
            'nominal_anggaran_disetujui' => $this->currentData['nominal_anggaran_disetujui'] ?? null,
        ];

        // handle page
        switch ($this->urlInfo['id']) {
            case 'overview':
            case 'summary':
                $this->dataSumberPendanaan = SumberPendanaan::find($this->currentData['id_sumber_pendanaan']);
                $this->initFormDokumenSK();

                $this->dataIsianProposal = $this->service->getDataIsianProposal($this->idProposalPendanaan);

                if ($this->urlInfo['id'] === 'summary') {
                    $aktifitasService = new PengajuanPendanaanAktivitasPenelitianService;
                    $artikelService = new PengajuanPendanaanPublikasiArtikelService;
                    $bukuService = new PengajuanPendanaanPublikasiBukuService;

                    $this->dataAktivitasPenelitian = $aktifitasService->getDaftarByIdPengajuanPendanaan($this->idProposalPendanaan);

                    $this->dataReviewerAntaraOnly = $serviceReviewer->index($this->idProposalPendanaan, [
                        'apakah_review_antara' => true,
                        'apakah_admin' => false,
                    ]);

                    $this->dataPenilaianReviewerOutput = $serviceReviewer->getPenilaianReviewerOutput($this->idProposalPendanaan);

                    $this->progreport_loadData();

                    $this->dataReviewerLuaranOnly = $serviceReviewer->index($this->idProposalPendanaan, [
                        'apakah_review_luaran' => true,
                        'apakah_admin' => false,
                    ]);
                    $this->dataPenilaianReviewerLuaran = $serviceReviewer->getPenilaianReviewerOutput($this->idProposalPendanaan);

                    $this->outputpenilaian_loadData();

                    $this->dataArtikelPublikasi = $artikelService->indexPerPengajuanPendanaan(idPengajuanPendanaan: $this->idProposalPendanaan);
                    $this->dataBukuPublikasi = $bukuService->indexPerPengajuanPendanaan(idPengajuanPendanaan: $this->idProposalPendanaan);
                }
                break;
            case 'similarity':
                $this->dataSumberPendanaan = SumberPendanaan::find($this->currentData['id_sumber_pendanaan']);
                $this->similarity_defaultRecord();
                $this->initFormSimilarity('penilaian_similarity');
                break;
            case 'reviewer':
                $service = new PengajuanPendanaanReviewerService;
                $this->dataReviewer = $service->index($this->idProposalPendanaan);
                $this->reviewer_defaultRecord();
                $this->initFormReviewer();
                break;
            case 'revproposal':
                $penilaianIsianProposalService = new PenilaianIsianProposalService();
                $this->dataPenilaianAspekProposal = $penilaianIsianProposalService->getAspekBobotPenilaian($this->idProposalPendanaan);
                $this->dataIsianPenilaianProposal = $penilaianIsianProposalService->getDataIsianPenilaianProposal($this->idProposalPendanaan);
                $this->dataFeedbackPenilaianProposal = $penilaianIsianProposalService->getDataPenilaianReviwerIsianProposal($this->idProposalPendanaan, $this->dataIsianPenilaianProposal);

                $this->dataReviewerReviewProposal = $serviceReviewer->index($this->idProposalPendanaan, [
                    'apakah_review_proposal' => true,
                    'apakah_admin' => false,
                ]);

                $this->dataNilaiAspekReviewer = $serviceAspek->getPenilaianReviewerKomposisiProposal();
                break;
            case 'jadwalproposal':
                $this->dataJadwalProposal = PengajuanPendanaanJadwalPresentasi::where('id_pengajuan_pendanaan', $this->idProposalPendanaan)->get();
                $this->jadwalProposal_defaultRecord();
                $this->initFormJadwalProposal();
                break;
            case 'nilaipresensi':
                $penilaianPresentasiService = new PenilaianPresentasiManagementService;
                $this->dataPenilaianPresentasiProposal = $penilaianPresentasiService->getAspekBobotPenilaianByIdPengajuan($this->idProposalPendanaan);
                $this->dataReviewerReviewProposal = $serviceReviewer->index($this->idProposalPendanaan, [
                    'apakah_review_proposal' => true,
                    'apakah_admin' => false,
                ]);
                $this->dataNilaiAspekReviewer = $serviceAspek->getPenilaianReviewerPresentasiProposal();
                break;
            case 'pembimbing':
                $pembimbingService = new PengajuanPendanaanPembimbingService;
                $aktifitasService = new PengajuanPendanaanAktivitasPenelitianService;
                $this->dataAktivitasPenelitian = $aktifitasService->getDaftarByIdPengajuanPendanaan($this->idProposalPendanaan);
                $this->dataPembimbing = $pembimbingService->getDaftarPembimbing($this->idProposalPendanaan);
                $this->pembimbing_defaultRecord();
                $this->initFormPembimbing();
                break;
            case 'progreport':
                $this->dataReviewerAntaraOnly = $serviceReviewer->index($this->idProposalPendanaan, [
                    'apakah_review_antara' => true,
                    'apakah_admin' => false,
                ]);
                $this->progreport_loadData();
                $this->progreport_defaultRecord();
                $this->initFormProgreport();
                break;
            case 'outputpenelitian':
                $this->dataAspekPenilaianOutput = $this->service->getAspekOutputPertanyaan($this->idProposalPendanaan);
                $this->dataReviewerLuaranOnly = $serviceReviewer->index($this->idProposalPendanaan, [
                    'apakah_review_luaran' => true,
                    'apakah_admin' => false,
                ]);
                $this->dataPenilaianReviewerOutput = $serviceReviewer->getPenilaianReviewerOutput($this->idProposalPendanaan);
                $this->outputpenilaian_loadData();
                $this->outputpenilaian_defaultRecord();
                $this->initFormOutputPenilaian();
                break;
            case 'publikasi':
                $artikelService = $this->activeTab == 1 ? new PengajuanPendanaanPublikasiArtikelService : new PengajuanPendanaanPublikasiBukuService;
                $this->dataArtikelPublikasi = $artikelService->indexPerPengajuanPendanaan(idPengajuanPendanaan: $this->idProposalPendanaan);

                $this->dataReviewerLuaranOnly = $serviceReviewer->index($this->idProposalPendanaan, [
                    'apakah_review_luaran' => true,
                    'apakah_admin' => false,
                ]);

                $this->dataPenilaianReviewerOutput = $serviceReviewer->getPenilaianReviewerOutput($this->idProposalPendanaan);
                $this->outputpenilaian_loadData();

                $this->publikasi_defaultRecord();
                $this->initFormPublikasi();
                break;
            case 'aktpenelitian':
                $aktifitasService = new PengajuanPendanaanAktivitasPenelitianService;
                $this->dataAktivitasPenelitian = $aktifitasService->getDaftarByIdPengajuanPendanaan($this->idProposalPendanaan);
                $this->aktpenelitian_defaultRecord();
                $this->initFormAktivitasPenelitian();
                break;
            default:
                abort(404);
                break;
        }
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

    private function throwValidationError($column, $message)
    {
        $error = ValidationException::withMessages([
            $column => $message,
        ]);

        throw $error;
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

    public function removeForm($id, $title, $function)
    {
        $this->removeId = $id;

        $this->removeTitle = $title;

        $functionName = $function . '_removeForm';

        $this->removeFunction = $functionName;

        $this->showModal('modal-remove');
    }

    public function submitRemoveForm()
    {
        $function = $this->removeFunction;

        $this->$function();
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

    public function initAlert($type, $message)
    {
        $this->alert['type'] = $type;
        $this->alert['message'] = $message;
    }

    private function defineFormFields()
    {
        list($txtKetua, $txtAnggota) = $this->service->getTxtDataPeneliti($this->dataPenelitiDosen, $this->dataPenelitiMahasiswa);

        $this->currentData['nama_ketua'] = $txtKetua;
        $this->currentData['nama_anggota'] = $txtAnggota;
        $this->currentData['cabang_bank'] = $this->currentData['cabang_bank'] ?? '-';

        // define fields
        $fields = [
            'utama' => [
                'title' => 'Overview Proposal',
                'items' => [
                    ['field' => 'nama_periode_pendanaan', 'label' => 'Periode Pendanaan'],
                    ['field' => 'judul_penelitian', 'label' => 'Judul ' . ($this->currentData['kode_jenis_pendanaan'] === 'penelitian' ? 'Penelitian' : 'Pengabdian')],
                    ['field' => 'nama_klaster', 'label' => 'Klaster Pendanaan'],
                    ['field' => 'nama_ketua', 'label' => 'Ketua Proposal'],
                    ['field' => 'nama_anggota', 'label' => 'Anggota Proposal'],
                    ['field' => 'status_agenda_kegiatan', 'label' => 'Status Proposal', 'component' => 'detail.status_agenda_kegiatan'],
                ]
            ],
            'pertanyaan-penelitian' => [
                'title' => 'Pernyataan Proposal',
                'items' => [
                    ['field' => 'judul_penelitian', 'label' => 'Judul Proposal'],
                    ['field' => 'nama_klaster', 'label' => 'Klaster Pendanaan'],
                    ['field' => 'kode_jenis_pendanaan', 'label' => 'Jenis Pendanaan'],
                    ['field' => 'nama_sumber_pendanaan', 'label' => 'Sumber Pendanaan'],
                    ['field' => 'nama_pengelola_bantuan', 'label' => 'Pengelola Pendanaan'],
                    ['field' => 'nama_bidang_ilmu', 'label' => 'Bidang Ilmu'],
                    ['field' => 'nama_tema', 'label' => 'Tema'],
                    ['field' => 'jenis_output_penelitian', 'label' => 'Luaran Kegiatan'],
                    ['field' => 'nominal_anggaran_disetujui', 'label' => 'Anggaran Disetujui', 'component' => 'detail.format_currency'],
                ],
                'item_dosen' => [
                    ['field' => 'nama', 'label' => 'Nama Lengkap'],
                    ['field' => 'kustom_kode', 'label' => 'NIP'],
                    ['field' => 'nama_pt', 'label' => 'Asal Institusi'],
                    ['field' => 'unit_kerja', 'label' => 'Program Studi']
                ],
                'item_mahasiswa' => [
                    ['field' => 'nama', 'label' => 'Nama Lengkap'],
                    ['field' => 'kustom_kode', 'label' => 'NIM'],
                ]
            ],
            'detail-pendanaan' => [
                'title' => 'Detail Pendanaan',
                'items' => [
                    ['field' => 'nominal_anggaran_diajukan', 'label' => 'Usulan Biaya', 'currency_field' => 'mata_uang'],
                    ['field' => 'nama_pemilik_rekening'],
                    ['field' => 'nama_bank'],
                    ['field' => 'nomor_rekening'],
                    ['field' => 'cabang_bank'],
                    ['field' => 'id_foto_tabungan', 'label' => 'Foto Halaman depan buku tabungan', 'component' => 'detail.view_dokumen'],
                    ['field' => 'id_dokumen_rab', 'label' => 'Dokumen RAB', 'component' => 'detail.view_dokumen'],
                ]
            ],
        ];

        $this->currentData['kode_jenis_pendanaan'] = ucwords(str_replace('_', ' ', $this->currentData['kode_jenis_pendanaan']));

        // add data to fields
        foreach ($fields as $index => $field) {
            foreach ($field['items'] as $key => $item) {
                $fields[$index]['items'][$key]['original'] = $this->currentData[$item['field']] ?? null;
                $fields[$index]['items'][$key]['text'] = $this->currentData[$item['field']] ?? null;
            }
        }

        return $fields;
    }

    public function continueFunction()
    {
        $this->{$this->confirmationFunction}(false);

        $this->hideModal('modal-confirmation');

        $this->confirmationFunction = null;

        $this->msgConfirmation = null;
    }

    /* Overview Page */
    // admin
    public function overview_batalkanAdministrasi($isConfirmation = true)
    {
        if ($isConfirmation) {
            $this->confirmationFunction = 'overview_batalkanAdministrasi';
            $this->msgConfirmation = 'Apakah Anda yakin ingin membatalkan penilaian?';
            $this->showModal('modal-confirmation');

            return;
        }

        $agendaKegiatan = AgendaKegiatan::where('kode_agenda', $this->timelineActive->kode_agenda)->first();

        $this->service->updateStatusAgendaKegiatan($this->idProposalPendanaan, $agendaKegiatan->id, PengajuanPendanaanStatus2::LEVEL5_PROSES_SELEKSI_ADMINISTRASI);
        $this->service->clearSimilarityReviewer($this->idProposalPendanaan);
        $this->isLolosAdministrasi = null;
        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();
        $this->data = $this->defineFormFields();

        $this->initAlert('success', 'Penilaian berhasil dibatalkan');
    }

    public function overview_tolakAdministrasi($isConfirmation = true)
    {
        if ($isConfirmation) {
            $this->confirmationFunction = 'overview_tolakAdministrasi';
            $this->msgConfirmation = 'Apakah Anda yakin ingin menandai dokumen tidak lengkap?';
            $this->showModal('modal-confirmation');

            return;
        }

        if ($this->isMenungguAnggota) {
            $this->initAlert('danger', 'Anda tidak bisa menentukan hasil Seleksi Administrasi karena status proposal masih menunggu konfirmasi anggota');

            return;
        }

        $agendaKegiatan = AgendaKegiatan::where('kode_agenda', $this->timelineActive->kode_agenda)->first();

        $this->service->updateStatusAgendaKegiatan($this->idProposalPendanaan, $agendaKegiatan->id, PengajuanPendanaanStatus2::LEVEL5_TIDAK_LOLOS_ADMINISTRASI);
        $this->isLolosAdministrasi = false;
        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();
        $this->data = $this->defineFormFields();

        $this->initAlert('success', 'Proposal ditandai tidak lolos seleksi administrasi');
    }

    public function overview_terimaAdministrasi($isConfirmation = true)
    {
        if ($isConfirmation) {
            $this->confirmationFunction = 'overview_terimaAdministrasi';
            $this->msgConfirmation = 'Apakah Anda yakin ingin menandai dokumen lengkap?';
            $this->showModal('modal-confirmation');

            return;
        }

        if ($this->isMenungguAnggota) {
            $this->initAlert('danger', 'Anda tidak bisa menentukan hasil Seleksi Administrasi karena status proposal masih menunggu konfirmasi anggota');

            return;
        }

        $agendaKegiatan = AgendaKegiatan::where('kode_agenda', $this->timelineActive->kode_agenda)->first();

        $check = $this->service->updateStatusAgendaKegiatan($this->idProposalPendanaan, $agendaKegiatan->id, PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI);

        if (HelpersError::isError($check)) {
            $this->initAlert('danger', $check->message);

            return;
        }

        $this->isLolosAdministrasi = true;
        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();
        $this->data = $this->defineFormFields();

        $this->initAlert('success', 'Proposal berhasil ditandai lolos seleksi administrasi');
    }

    public function overview_tolakNominasi($isConfirmation = true)
    {
        if ($isConfirmation) {
            $this->confirmationFunction = 'overview_tolakNominasi';
            $this->msgConfirmation = 'Apakah Anda yakin ingin menyatakan proposal tidak lolos nominasi?';
            $this->showModal('modal-confirmation');

            return;
        }

        $agendaKegiatan = AgendaKegiatan::where('kode_agenda', $this->timelineActive->kode_agenda)->first();

        $this->service->updateStatusAgendaKegiatan($this->idProposalPendanaan, $agendaKegiatan->id, PengajuanPendanaanStatus2::LEVEL8_TIDAK_LOLOS_NOMINASI);
        $this->isLolosNominasi = false;
        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();
        $this->data = $this->defineFormFields();

        $this->initAlert('success', 'Proposal dinyatakan tidak lolos nominasi');
    }

    public function overview_terimaNominasi($isConfirmation = true)
    {
        if ($isConfirmation) {
            $this->confirmationFunction = 'overview_terimaNominasi';
            $this->msgConfirmation = 'Apakah Anda yakin ingin menyatakan proposal ini lolos nominasi?';
            $this->showModal('modal-confirmation');

            return;
        }

        $agendaKegiatan = AgendaKegiatan::where('kode_agenda', $this->timelineActive->kode_agenda)->first();

        $this->service->updateStatusAgendaKegiatan($this->idProposalPendanaan, $agendaKegiatan->id, PengajuanPendanaanStatus2::LEVEL8_LOLOS_NOMINASI);
        $this->isLolosNominasi = true;
        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();
        $this->data = $this->defineFormFields();

        $this->initAlert('success', 'Proposal dinyatakan lolos nominasi');
    }

    public function overview_batalkanNominasi($isConfirmation = true)
    {
        if ($isConfirmation) {
            $this->confirmationFunction = 'overview_batalkanNominasi';
            $this->msgConfirmation = 'Apakah Anda yakin ingin membatalkan hasil nominasi?';
            $this->showModal('modal-confirmation');

            return;
        }

        $agendaKegiatan = AgendaKegiatan::where('kode_agenda', $this->timelineActive->kode_agenda)->first();

        $this->service->updateStatusAgendaKegiatan($this->idProposalPendanaan, $agendaKegiatan->id, PengajuanPendanaanStatus2::LEVEL8_NOMINASI_BELUM_DITINJAU);
        $this->isLolosNominasi = null;
        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();
        $this->data = $this->defineFormFields();

        $this->initAlert('success', 'Hasil nominasi berhasil dibatalkan');
    }

    public function overview_tolakPendanaan($isConfirmation = true)
    {
        if ($isConfirmation) {
            $this->confirmationFunction = 'overview_tolakPendanaan';
            $this->msgConfirmation = 'Apakah Anda yakin ingin menyatakan proposal tidak lolos pendanaan?';
            $this->showModal('modal-confirmation');

            return;
        }

        $agendaKegiatan = AgendaKegiatan::where('kode_agenda', $this->timelineActive->kode_agenda)->first();

        $this->service->updateStatusAgendaKegiatan($this->idProposalPendanaan, $agendaKegiatan->id, PengajuanPendanaanStatus2::LEVEL10_TIDAK_LOLOS_PENDANAAN);
        $this->isLolosPendanaan = false;
        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();
        $this->data = $this->defineFormFields();

        $this->initAlert('success', 'Proposal dinyatakan tidak lolos pendanaan');
    }

    public function overview_terimaPendanaan($isConfirmation = true)
    {
        if ($isConfirmation) {
            $this->records = [
                'nominal_anggaran_disetujui' => $this->currentData['nominal_anggaran_disetujui'] ?? null,
            ];

            $this->showModal('modal-confirmation-pendanaan');

            return;
        }

        if (!isset($this->records['is_afirmasi'])) {
            $this->records['is_afirmasi'] = false;
        }

        $data = $this->records;

        $data['nominal_anggaran_disetujui'] = str_replace('.', '', $this->records['nominal_anggaran_disetujui']);

        $agendaKegiatan = AgendaKegiatan::where('kode_agenda', $this->timelineActive->kode_agenda)->first();

        $check = $this->service->updateStatusAgendaKegiatan($this->idProposalPendanaan, $agendaKegiatan->id, PengajuanPendanaanStatus2::LEVEL10_LOLOS_PENDANAAN, $data);

        if (HelpersError::isError($check)) {
            $this->anggaranError = $check->message;

            return;
        }

        $this->isLolosPendanaan = true;
        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();
        $this->data = $this->defineFormFields();

        $this->initAlert('success', 'Proposal dinyatakan lolos pendanaan');

        $this->hideModal('modal-confirmation-pendanaan');
    }

    public function overview_batalkanPendanaan($isConfirmation = true)
    {
        if ($isConfirmation) {
            $this->confirmationFunction = 'overview_batalkanPendanaan';
            $this->msgConfirmation = 'Apakah Anda yakin ingin membatalkan hasil pendanaan?';
            $this->showModal('modal-confirmation');

            return;
        }

        $agendaKegiatan = AgendaKegiatan::where('kode_agenda', $this->timelineActive->kode_agenda)->first();

        $this->service->updateStatusAgendaKegiatan($this->idProposalPendanaan, $agendaKegiatan->id, PengajuanPendanaanStatus2::LEVEL10_BELUM_PENENTUAN_PENDANAAN);
        $this->isLolosPendanaan = null;
        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();
        $this->data = $this->defineFormFields();

        $this->initAlert('success', 'Hasil pendanaan berhasil dibatalkan');
    }

    public function initFormDokumenSK()
    {
        $this->fields = [
            [
                'field' => 'id_dokumen_sk_peneliti',
                'label' => 'Dokumen SK',
                'required' => true,
                'file_type' => ['pdf'],
                'max_size' => (1024 * 5),
                'wire:model.live' => 'records.id_dokumen_sk_peneliti',
            ]
        ];
    }

    public function updatedRecordsIdDokumenSkPeneliti($value)
    {
        $this->disableSubmit = false;
    }

    public function overview_addDokumenSK()
    {
        $this->records = [
            'id_dokumen_sk_peneliti' => null,
        ];

        $this->initFormDokumenSK();

        $this->showModal('modal-dokumen-sk');

        $this->disableSubmit = false;
    }

    public function overview_submitDokumenSK()
    {
        $this->validateForm();

        $this->service->updateDokumenSK($this->idProposalPendanaan, $this->records['id_dokumen_sk_peneliti']);

        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();
        $this->data = $this->defineFormFields();

        $this->initAlert('success', 'Dokumen SK berhasil diunggah');

        $this->hideModal('modal-dokumen-sk');
    }

    public function overview_removeForm()
    {
        $this->service->updateDokumenSK($this->idProposalPendanaan, null);

        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();
        $this->data = $this->defineFormFields();

        $this->initAlert('success', 'Dokumen SK berhasil dihapus');

        $this->hideModal('modal-remove');
    }

    // dosen
    public function overview_ajukanProposal($isConfirmation = true)
    {
        if ($isConfirmation) {
            $this->confirmationFunction = 'overview_ajukanProposal';
            $this->msgConfirmation = 'Apakah Anda yakin ingin mengajukan proposal ini?';
            $this->showModal('modal-confirmation');

            return;
        }

        $check = $this->service->ajukanProposal($this->idProposalPendanaan);

        if (HelpersError::isError($check)) {
            $this->initAlert('danger', $check->message);

            return;
        }

        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();
        $this->data = $this->defineFormFields();

        $this->initAlert('success', 'Proposal berhasil diajukan');

        $this->hideModal('modal-remove');
    }

    public function overview_batalkanProposal($isConfirmation = true)
    {
        if ($isConfirmation) {
            $this->confirmationFunction = 'overview_batalkanProposal';
            $this->msgConfirmation = 'Apakah Anda yakin ingin membatalkan proposal ini?';
            $this->showModal('modal-confirmation');

            return;
        }

        $this->service->batalkanProposal($this->idProposalPendanaan);

        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();
        $this->data = $this->defineFormFields();

        $this->initAlert('success', 'Pengajuan proposal berhasil dibatalkan');

        $this->hideModal('modal-remove');
    }

    public function overview_terimaUndanganAnggota($isConfirmation = true)
    {
        if ($isConfirmation) {
            $this->confirmationFunction = 'overview_terimaUndanganAnggota';
            $this->msgConfirmation = 'Apakah Anda yakin ingin menerima undangan menjadi anggota proposal ini?';
            $this->showModal('modal-confirmation');

            return;
        }

        $this->service->terimaUndanganAnggota($this->idProposalPendanaan, auth()->user()->biodata->id, (!$this->isBypassDisabled ? $this->timelineActive : null));

        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();
        $this->currentAnggota = $this->service->getKeanggotaanProposal($this->idProposalPendanaan, auth()->user()->biodata->id);
        $this->data = $this->defineFormFields();

        $peneliti = $this->service->getDetailPenelitiByIdPengajuanPendanaan($this->idProposalPendanaan);
        $this->dataPenelitiDosen = $peneliti->whereIn(
            'jenis_anggota',
            [
                PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL,
                PengajuanPendanaanAnggota::JENIS_DOSEN_EKSTERNAL
            ]
        )->toArray();

        $this->initAlert('success', 'Undangan anggota berhasil diterima');

        $this->hideModal('modal-remove');
    }

    public function overview_tolakUndanganAnggota($isConfirmation = true)
    {
        if ($isConfirmation) {
            $this->confirmationFunction = 'overview_tolakUndanganAnggota';
            $this->msgConfirmation = 'Apakah Anda yakin ingin menolak undangan menjadi anggota proposal ini?';
            $this->showModal('modal-confirmation');

            return;
        }

        $this->service->tolakUndanganAnggota($this->idProposalPendanaan, auth()->user()->biodata->id);

        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();
        $this->currentAnggota = $this->service->getKeanggotaanProposal($this->idProposalPendanaan, auth()->user()->biodata->id, (!$this->isBypassDisabled ? $this->timelineActive : null));
        $this->data = $this->defineFormFields();

        $peneliti = $this->service->getDetailPenelitiByIdPengajuanPendanaan($this->idProposalPendanaan);
        $this->dataPenelitiDosen = $peneliti->whereIn(
            'jenis_anggota',
            [
                PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL,
                PengajuanPendanaanAnggota::JENIS_DOSEN_EKSTERNAL
            ]
        )->toArray();

        $this->initAlert('success', 'Undangan anggota berhasil ditolak');

        $this->hideModal('modal-remove');
    }

    public function overview_batalkanUndanganAnggota($isConfirmation = true)
    {
        if ($isConfirmation) {
            $this->confirmationFunction = 'overview_batalkanUndanganAnggota';
            $this->msgConfirmation = 'Apakah Anda yakin ingin membatalkan undangan menjadi anggota proposal ini?';
            $this->showModal('modal-confirmation');

            return;
        }

        $this->service->batalkanUndanganAnggota($this->idProposalPendanaan, auth()->user()->biodata->id, (!$this->isBypassDisabled ? $this->timelineActive : null));

        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();
        $this->currentAnggota = $this->service->getKeanggotaanProposal($this->idProposalPendanaan, auth()->user()->biodata->id);
        $this->data = $this->defineFormFields();

        $peneliti = $this->service->getDetailPenelitiByIdPengajuanPendanaan($this->idProposalPendanaan);
        $this->dataPenelitiDosen = $peneliti->whereIn(
            'jenis_anggota',
            [
                PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL,
                PengajuanPendanaanAnggota::JENIS_DOSEN_EKSTERNAL
            ]
        )->toArray();

        $this->initAlert('success', 'Undangan anggota berhasil dibatalkan');

        $this->hideModal('modal-remove');
    }

    public function updatedRecordsNominalAnggaranDisetujui($value)
    {
        $this->disableSubmit = false;
    }
    /* End Overview Page */

    /* Jadwal Presentasi Page */
    public function initFormJadwalProposal()
    {
        $this->fields = [
            [
                'field' => 'tipe_presentasi',
                'label' => 'Nama Kegiatan',
                'selected' => $this->records['tipe_presentasi'],
                'options' => PengajuanPendanaanJadwalPresentasi::TIPE_PRESENTASI_OPTIONS,
                'required' => true,
                'wire:change' => 'jadwalProposal_changeTipePresentasi($event.target.value)'
            ],
            [
                'field' => 'tipe_kegiatan',
                'label' => 'Tipe Kegiatan',
                'control' => 'radio',
                'inline' => true,
                'selected' => $this->records['tipe_kegiatan'],
                'required' => true,
                'wire:model.live' => 'records.tipe_kegiatan',
                'options' => PengajuanPendanaanJadwalPresentasi::TIPE_KEGIATAN_OPTIONS,
            ],
            ['field' => 'waktu_pelaksanaan', 'label' => 'Tanggal Dilaksanakan', 'control' => 'datetime', 'required' => true, 'wire:model.live' => 'records.waktu_pelaksanaan'],
        ];

        if ($this->records['tipe_kegiatan'] == PengajuanPendanaanJadwalPresentasi::TIPE_KEGIATAN_OFFLINE) {
            $this->fields[] = ['field' => 'tempat_pelaksanaan', 'label' => 'Tempat Kegiatan', 'required' => true, 'wire:model.live' => 'records.tempat_pelaksanaan'];
        } else {
            $this->fields[] = ['field' => 'link_presentasi_kegiatan', 'label' => 'Link Kegiatan', 'type' => 'url', 'required' => true, 'wire:model.live' => 'records.link_presentasi_kegiatan'];
        }
    }

    public function updatedRecordsTipeKegiatan($value)
    {
        $this->records['tipe_kegiatan'] = $value;

        $this->initFormJadwalProposal();
    }

    public function jadwalProposal_defaultRecord()
    {
        $this->records = [
            'tipe_presentasi' => null,
            'tipe_kegiatan' => PengajuanPendanaanJadwalPresentasi::TIPE_KEGIATAN_OFFLINE,
            'waktu_pelaksanaan' => null,
            'tempat_pelaksanaan' => null,
            'link_presentasi_kegiatan' => null,
        ];
    }

    public function jadwalProposal_changeTipePresentasi($value)
    {
        $this->records['tipe_presentasi'] = $value;

        $this->initFormJadwalProposal();
    }

    public function jadwalProposal_addForm()
    {
        $this->isEdit = false;

        $this->jadwalProposal_defaultRecord();

        $this->initFormJadwalProposal();

        $this->showModal('modal-jadwal-presentasi');
    }

    public function jadwalProposal_removeForm()
    {
        $jadwalService = new PengajuanPendanaanJadwalPresentasiService;

        $jadwalService->destroy($this->removeId);

        $this->dataJadwalProposal = PengajuanPendanaanJadwalPresentasi::where('id_pengajuan_pendanaan', $this->idProposalPendanaan)->get();

        $this->initAlert('success', 'Jadwal presentasi berhasil dihapus');

        $this->hideModal('modal-remove');
    }

    public function jadwalProposal_editForm($id)
    {
        $this->isEdit = true;

        $record = PengajuanPendanaanJadwalPresentasi::find($id);

        $this->records = [
            'id' => $record->id,
            'tipe_presentasi' => $record->tipe_presentasi,
            'tipe_kegiatan' => $record->tipe_kegiatan,
            'waktu_pelaksanaan' => Carbon::parse($record->waktu_pelaksanaan)->format('Y-m-d\TH:i'),
            'tempat_pelaksanaan' => $record->tempat_pelaksanaan,
            'link_presentasi_kegiatan' => $record->link_presentasi_kegiatan,
        ];

        $this->initFormJadwalProposal();

        $this->showModal('modal-jadwal-presentasi');
    }

    public function jadwalProposal_submitForm()
    {
        $this->validateForm();

        $this->records['nama_kegiatan'] = PengajuanPendanaanJadwalPresentasi::TIPE_PRESENTASI_OPTIONS[$this->records['tipe_presentasi']];
        $this->records['id_pengajuan_pendanaan'] = $this->idProposalPendanaan;

        if ($this->records['tipe_kegiatan'] == PengajuanPendanaanJadwalPresentasi::TIPE_KEGIATAN_OFFLINE) {
            $this->records['link_presentasi_kegiatan'] = null;
        } else {
            $this->records['tempat_pelaksanaan'] = null;
        }

        $service = new PengajuanPendanaanJadwalPresentasiService;

        if (!$this->isEdit) {
            $store = $service->store($this->records, $this->idProposalPendanaan);
        } else {
            $store = $service->update($this->records, $this->idProposalPendanaan, $this->records['id']);
        }

        if (HelpersError::isError($store)) {
            $this->throwValidationError('tipe_presentasi', $store->message);
        }

        $this->dataJadwalProposal = PengajuanPendanaanJadwalPresentasi::where('id_pengajuan_pendanaan', $this->idProposalPendanaan)->get();

        $this->jadwalProposal_defaultRecord();

        $this->initAlert('success', 'Jadwal presentasi berhasil ' . ($this->isEdit ? 'diperbarui' : 'ditambahkan'));

        $this->hideModal('modal-jadwal-presentasi');
    }
    /* End Jadwal Presentasi Page */

    /* Reviewer Page */
    public function initFormReviewer()
    {
        $jenisAnggota = PengajuanPendanaanAnggota::JENIS_ANGGOTA;
        array_pop($jenisAnggota);

        $this->fields = [
            [
                'field' => 'jenis_anggota',
                'label' => 'Jenis Anggota',
                'control' => 'radio',
                'inline' => true,
                'value' => $this->records['jenis_anggota'],
                'required' => true,
                'wire:model.live' => 'records.jenis_anggota',
                'options' => $jenisAnggota,
            ],
        ];

        if ($this->records['jenis_anggota'] == PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL) {
            $this->fields[] = [
                'field' => 'id_unit_kerja',
                'label' => 'Program Studi',
                'selected' => $this->records['id_unit_kerja'],
                'options' => UnitKerja::optionByType([UnitKerja::STUDY_PROGRAM]),
                'required' => true,
                'variant' => 'search',
                'wire:change' => 'reviewer_changeUnitKerja($event.target.value)',
            ];
        }

        // ignore id biodata
        $exceptReviewer = [];
        foreach ($this->dataReviewer as $item) {
            $exceptReviewer[] = $item->id_biodata;
        }

        $dosenProposal = PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $this->idProposalPendanaan)
            ->pluck('id_biodata')
            ->toArray();

        $dosenProposal = array_merge($dosenProposal, $exceptReviewer);
        $dosenProposal = array_unique($dosenProposal);

        if ($this->isEdit) {
            unset($dosenProposal[array_search($this->records['id_biodata'], $dosenProposal)]);
        }

        $this->fields[] = [
            'field' => 'id_biodata',
            'label' => 'Reviewer',
            'selected' => $this->records['id_biodata'],
            'options' => $this->records['jenis_anggota'] == 1 ?
                ($this->records['id_unit_kerja'] ? Biodata::getDosenInternal($this->records['id_unit_kerja'], true, $dosenProposal) : []) :
                Biodata::getDosenEksternal(true, $dosenProposal),
            'required' => true,
            'variant' => 'search',
            'wire:change' => 'reviewer_changeReviewer($event.target.value)',
        ];

        $tipeReviewer = PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_OPTIONS;

        // cek agenda antara
        $agendaLaporanAntara = array_filter($this->timelines, function ($item) {
            return $item->kode_agenda === \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENILAIAN_LAPORAN_ANTARA;
        });
        if (empty($agendaLaporanAntara)) {
            unset($tipeReviewer[PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_ANTARA]);
        }

        $this->fields[] = [
            'field' => 'tipe_reviewer',
            'label' => 'Bertugas Sebagai',
            'value' => $this->records['tipe_reviewer'],
            'options' => $tipeReviewer,
            'control' => 'checkbox',
            'class' => 'choices__input',
            'required' => true,
            'inline' => false,
            'wire:change' => 'reviewer_changeTipeReviewer($event.target.value)',
        ];

        $this->fields[] = [
            'field' => 'id_dokumen_sk',
            'label' => 'Upload Dokumen Surat Keterangan (SK)',
            'file_type' => ['pdf'],
            'max_size' => (1024 * 5),
            'wire:model.live' => 'records.id_dokumen_sk',
        ];

        $this->disableSubmit = false;
    }

    public function updatedRecordsIdDokumenSk($value)
    {
        $this->disableSubmit = false;
    }

    public function updatedRecordsJenisAnggota($value)
    {
        $this->records['jenis_anggota'] = $value;

        $this->initFormReviewer();
    }

    public function reviewer_defaultRecord()
    {
        $this->records = [
            'jenis_anggota' => 1,
            'id_dokumen_sk' => null,
            'id_unit_kerja' => null,
            'id_biodata' => null,
            'tipe_reviewer' => [],
        ];
    }

    public function reviewer_changeReviewer($value)
    {
        $this->records['id_biodata'] = $value;

        $this->initFormReviewer();
    }

    public function reviewer_changeTipeReviewer($value)
    {
        if (isset($this->records['tipe_reviewer'][$value])) {
            unset($this->records['tipe_reviewer'][$value]);
        } else {
            $this->records['tipe_reviewer'][$value] = 1;
        }

        $this->initFormReviewer();
    }

    public function reviewer_changeUnitKerja($value)
    {
        $this->records['id_unit_kerja'] = $value;

        $this->records['id_biodata'] = null;

        $this->initFormReviewer();
    }

    public function reviewer_addForm()
    {
        $this->isEdit = false;

        $this->reviewer_defaultRecord();

        $this->initFormReviewer();

        $this->showModal('modal-reviewer');
    }

    public function reviewer_editForm($id)
    {
        $this->isEdit = true;

        $record = PengajuanPendanaanReviewer::find($id);

        $biodata = Biodata::where('id', $record->id_biodata)->first();
        $pegawai = Pegawai::where('ref_key_siakad', $biodata->ref_key_siakad)->first();

        if (!$pegawai) {
            $pegawai = DosenEksternal::where('id_biodata', $record->id_biodata)->first();
        }

        $tempReviewer = [];
        if ($record->apakah_review_proposal) {
            $tempReviewer[] = PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_PROPOSAL;
        }
        if ($record->apakah_review_luaran) {
            $tempReviewer[] = PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_LUARAN;
        }
        if ($record->apakah_review_antara) {
            $tempReviewer[] = PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_ANTARA;
        }

        $this->records = [
            'id' => $record->id,
            'jenis_anggota' => $record->apakah_internal ? PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL : PengajuanPendanaanAnggota::JENIS_DOSEN_EKSTERNAL,
            'id_unit_kerja' => $pegawai->id_unit_kerja ?? null,
            'id_biodata' => $record->id_biodata,
            'tipe_reviewer' => $tempReviewer,
            'id_dokumen_sk' => $record->id_dokumen_sk,
        ];

        $this->initFormReviewer();

        $tipe_reviewer = [];
        if ($record->apakah_review_proposal) {
            $tipe_reviewer[PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_PROPOSAL] = 1;
        }
        if ($record->apakah_review_luaran) {
            $tipe_reviewer[PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_LUARAN] = 1;
        }
        if ($record->apakah_review_antara) {
            $tipe_reviewer[PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_ANTARA] = 1;
        }

        $this->showModal('modal-reviewer');

        $this->records['tipe_reviewer'] = $tipe_reviewer;
    }

    public function reviewer_removeForm()
    {
        $service = new PengajuanPendanaanReviewerService;

        $service->destroy($this->removeId);

        $this->dataReviewer = $service->index($this->idProposalPendanaan);

        $this->initAlert('success', 'Reviewer berhasil dihapus');

        $this->hideModal('modal-remove');
    }

    public function reviewer_submitForm()
    {
        $this->validateForm();

        $this->records['id_pengajuan_pendanaan'] = $this->idProposalPendanaan;

        $payload = $this->records;

        foreach ($this->records['tipe_reviewer'] as $tipe => $t) {
            if ($tipe == PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_PROPOSAL) {
                $payload['apakah_review_proposal'] = true;
            } else if ($tipe == PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_LUARAN) {
                $payload['apakah_review_luaran'] = true;
            } else if ($tipe == PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_ANTARA) {
                $payload['apakah_review_antara'] = true;
            }
        }

        if (!isset($payload['apakah_review_proposal'])) {
            $payload['apakah_review_proposal'] = false;
        }

        if (!isset($payload['apakah_review_luaran'])) {
            $payload['apakah_review_luaran'] = false;
        }

        if (!isset($payload['apakah_review_antara'])) {
            $payload['apakah_review_antara'] = false;
        }

        // check if reviewer is internal
        $payload['apakah_internal'] = $this->records['jenis_anggota'] == PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL;

        unset($payload['jenis_anggota']);
        unset($payload['id_unit_kerja']);
        unset($payload['tipe_reviewer']);

        $service = new PengajuanPendanaanReviewerService;

        if ($this->isEdit) {
            $id = $this->records['id'];
            unset($payload['id']);
            $data = $service->update($payload, $id);
        } else {
            $data = $service->store($payload);
        }

        if (HelpersError::isError($data)) {
            $err = explode(':', $data->message);
            $this->throwValidationError($err[0], $err[1]);
        }

        $this->dataReviewer = $service->index($this->idProposalPendanaan);

        $this->initAlert('success', 'Reviewer berhasil ' . ($this->isEdit ? 'diperbarui' : 'ditambahkan'));

        $this->hideModal('modal-reviewer');
    }
    /* End Reviewer Page */

    /* Pembimbing Page */
    public function initFormPembimbing()
    {
        $this->fields = [
            [
                'field' => 'id_unit_kerja',
                'label' => 'Program Studi',
                'selected' => $this->records['id_unit_kerja'],
                'options' => UnitKerja::optionByType([UnitKerja::STUDY_PROGRAM]),
                'required' => true,
                'variant' => 'search',
                'wire:change' => 'pembimbing_changeUnitKerja($event.target.value)',
            ]
        ];

        // ignore id biodata
        $exceptPembimbing = [];
        foreach ($this->dataPembimbing as $item) {
            $exceptPembimbing[] = $item->id_biodata;
        }

        $dosenProposal = PengajuanPendanaanAnggota::where('id_pengajuan_pendanaan', $this->idProposalPendanaan)
            ->pluck('id_biodata')
            ->toArray();

        $dosenProposal = array_merge($dosenProposal, $exceptPembimbing);
        $dosenProposal = array_unique($dosenProposal);

        if ($this->isEdit) {
            unset($dosenProposal[array_search($this->records['id_biodata'], $dosenProposal)]);
        }

        $this->fields[] = [
            'field' => 'id_biodata',
            'label' => 'Pembimbing',
            'selected' => $this->records['id_biodata'],
            'options' => $this->records['id_unit_kerja'] ? Biodata::getDosenInternal($this->records['id_unit_kerja'], true, $dosenProposal) : [],
            'required' => true,
            'variant' => 'search',
            'wire:change' => 'pembimbing_changePembimbing($event.target.value)',
        ];

        $this->fields[] = [
            'field' => 'id_dokumen_sk',
            'label' => 'Upload Dokumen Surat Keterangan (SK)',
            'file_type' => ['pdf'],
            'max_size' => (1024 * 5),
            'wire:model.live' => 'records.id_dokumen_sk',
        ];

        $this->disableSubmit = false;
    }

    public function pembimbing_defaultRecord()
    {
        $this->records = [
            'id_unit_kerja' => null,
            'id_biodata' => null,
            'id_dokumen_sk' => null
        ];
    }

    public function pembimbing_changeUnitKerja($value)
    {
        $this->records['id_unit_kerja'] = $value;

        $this->records['id_biodata'] = null;

        $this->initFormPembimbing();
    }

    public function pembimbing_changePembimbing($value)
    {
        $this->records['id_biodata'] = $value;

        $this->initFormPembimbing();
    }

    public function pembimbing_addForm()
    {
        $this->isEdit = false;

        $this->pembimbing_defaultRecord();

        $this->initFormPembimbing();

        $this->showModal('modal-pembimbing');
    }

    public function pembimbing_editForm($id)
    {
        $this->isEdit = true;

        $record = PengajuanPendanaanPembimbing::find($id);
        $biodata = Biodata::where('id', $record->id_biodata)->first();
        $pegawai = Pegawai::where('ref_key_siakad', $biodata->ref_key_siakad)->first();

        $this->records = [
            'id' => $record->id,
            'id_unit_kerja' => $pegawai->id_unit_kerja,
            'id_biodata' => $record->id_biodata,
            'id_dokumen_sk' => $record->id_dokumen_sk,
        ];

        $this->initFormPembimbing();

        $this->showModal('modal-pembimbing');
    }

    public function pembimbing_removeForm()
    {
        $service = new PengajuanPendanaanPembimbingService;

        $check = $service->destroy($this->removeId);

        if (HelpersError::isError($check)) {
            $this->hideModal('modal-remove');

            $this->initAlert('danger', $check->message);

            return;
        }

        $this->dataPembimbing = $service->getDaftarPembimbing($this->idProposalPendanaan);

        $this->initAlert('success', 'Pembimbing berhasil dihapus');

        $this->hideModal('modal-remove');
    }

    public function pembimbing_submitForm()
    {
        $this->validateForm();

        $this->records['id_pengajuan_pendanaan'] = $this->idProposalPendanaan;

        $service = new PengajuanPendanaanPembimbingService;

        if (!$this->isEdit) {
            $store = $service->store($this->records);
        } else {
            $store = $service->update($this->records, $this->records['id']);
        }

        if (HelpersError::isError($store)) {
            $err = explode(':', $store->message);
            $this->throwValidationError($err[0], $err[1]);
        }

        $this->dataPembimbing = $service->getDaftarPembimbing($this->idProposalPendanaan);

        $this->pembimbing_defaultRecord();

        $this->initAlert('success', 'Pembimbing berhasil ' . ($this->isEdit ? 'diperbarui' : 'ditambahkan'));

        $this->hideModal('modal-pembimbing');
    }
    /* End Pembimbing Page */

    /* Output Penilaian Page */
    public function outputpenilaian_editNote()
    {
        $this->isEdit = true;
    }

    public function outputpenilaian_editNilai()
    {
        $this->isEditNilai = true;

        $this->records['status'] = [];

        foreach ($this->dataPenilaianReviewerOutput as $item) {
            $this->records['status'][$item->id_pengajuan_pendanaan_output_penelitian] = $item->status_penilaian_output;
        }
    }

    public function outputpenilaian_cancelNilai()
    {
        $this->isEditNilai = false;

        $this->records['status'] = null;
    }

    public function outputpenilaian_setPenilaian($jenis, $value)
    {
        $this->records['status'][$jenis] = $value;
    }

    public function outputpenilaian_saveNilai()
    {
        if (!isset($this->records['status']) || empty($this->records['status'])) {
            $this->initAlert('danger', 'Status penilaian luaran harus dipilih');

            return;
        }

        $dataReviewer = PengajuanPendanaanReviewer::where('id_biodata', auth()->user()->biodata->id)
            ->where('id_pengajuan_pendanaan', $this->idProposalPendanaan)
            ->first();

        $serviceReviewer = new PengajuanPendanaanReviewerService;

        if (!$dataReviewer) {
            $dataReviewer = $serviceReviewer->store([
                'id_pengajuan_pendanaan' => $this->idProposalPendanaan,
                'id_biodata' => auth()->user()->biodata->id,
                'jenis_anggota' => PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL,
                'id_dokumen_sk' => null,
                'reviewer_ke' => 1,
                'apakah_admin' => true,
                'apakah_review_proposal' => false,
                'apakah_review_luaran' => true,
                'apakah_review_antara' => false,
            ]);
        }

        $penilaianService = new PenilaianReviewerManagementService;

        $check = $penilaianService->storePenilaianLuaran($this->idProposalPendanaan, $dataReviewer->id, $this->records);

        if (HelpersError::isError($check)) {
            $this->initAlert('danger', $check->message);

            return;
        }

        $this->dataPenilaianReviewerOutput = $serviceReviewer->getPenilaianReviewerOutput($this->idProposalPendanaan);

        $this->outputpenilaian_loadData();

        $this->initAlert('success', 'Penilaian berhasil disimpan');

        $this->isEditNilai = false;
    }

    public function outputpenilaian_cancelEditNote()
    {
        $this->isEdit = false;
    }

    public function outputpenilaian_setNote($value)
    {
        $this->records['note'] = $value;
    }

    public function outputpenilaian_saveNote()
    {
        $this->isEdit = false;

        $pengajuanPendanaan = PengajuanPendanaan::find($this->idProposalPendanaan);

        $pengajuanPendanaan->kesimpulan_output_bersama = !empty($this->records['note']) ? $this->records['note'] : null;

        $pengajuanPendanaan->update();

        $this->currentData['kesimpulan_output_bersama'] = $pengajuanPendanaan->kesimpulan_output_bersama;
    }

    public function initFormOutputPenilaian()
    {
        $this->fields = [
            [
                'field' => 'id_dokumen_report',
                'label' => 'Upload Dokumen Laporan Luaran',
                'file_type' => ['pdf'],
                'max_size' => (1024 * 5),
                'required' => true,
                'wire:model.live' => 'records.id_dokumen_report',
            ],
        ];
    }

    public function outputpenilaian_loadData()
    {
        $this->dataOutputPenelitian = $this->service->getDataOutputPenelitian($this->idProposalPendanaan);
    }

    public function outputpenilaian_defaultRecord($id = null)
    {
        $this->records = [
            'id_dokumen_report' => null,
            'id_pengajuan_pendanaan_output_penelitian' => $id,
        ];
    }

    public function outputpenilaian_addForm($jenis)
    {
        $this->isEdit = false;

        $this->outputpenilaian_defaultRecord($jenis);

        $this->showModal('modal-laporan-luaran');
    }

    public function outputpenilaian_submitForm()
    {
        $this->validateForm();

        $service = new PengajuanPendanaanLaporanOutputService;

        $check = $service->storeDokumen($this->records, $this->idProposalPendanaan);

        if (HelpersError::isError($check)) {
            $err = explode(':', $check->message);
            $this->throwValidationError($err[0], $err[1]);
        }

        $this->outputpenilaian_loadData();

        $this->outputpenilaian_defaultRecord();

        $this->initAlert('success', 'Berhasil mengunggah dokumen laporan antara');

        $this->hideModal('modal-laporan-luaran');
    }
    /* End Output Penilaian Page */

    /* Similiarity Page */
    public function initFormSimilarity($type)
    {
        $key = $type == 'similarity_proposal' ? 'penilaian_index_similarity' : 'penilaian_index_ai';

        if ($type == 'similarity_proposal') {
            $this->titleSimilarity = 'Similarity Proposal';
        } else {
            $this->titleSimilarity = 'Artificial Intelligence';
        }

        $this->fields = [
            [
                'field' => $key,
                'label' => 'Masukan Nilai',
                'type' => 'number',
                'value' => $this->records[$key],
                'placeholder' => 'Contoh Nilai Similarity: 20%',
                'required' => true,
                'wire:model.live' => 'records.' . $key,
            ]
        ];

        $keyDocument = $type == 'similarity_proposal' ? 'id_dokumen_penilaian_similarity' : 'id_dokumen_penilaian_ai';
        $this->fields[] = [
            'field' => $keyDocument,
            'label' => 'Upload Dokumen Hasil ' . $this->titleSimilarity,
            'file_type' => ['pdf'],
            'max_size' => (1024 * 5),
            'wire:model.live' => 'records.' . $keyDocument,
        ];
    }

    public function updatedRecordsPenilaianIndexSimilarity($value)
    {
        $this->disableSubmit = false;
    }

    public function updatedRecordsPenilaianIndexAi($value)
    {
        $this->disableSubmit = false;
    }

    public function similarity_defaultRecord()
    {
        $this->records = [
            'id_unit_kerja' => null,
            'id_biodata' => null,
            'id_dokumen_sk' => null,
            'penilaian_index_similarity' => null,
            'id_dokumen_penilaian_similarity' => null,
            'penilaian_index_ai' => null,
            'id_dokumen_penilaian_ai' => null,
        ];
    }

    public function similarity_editForm($type)
    {
        $this->isEdit = true;

        if ($type == 'similarity_proposal') {
            $this->records['penilaian_index_similarity'] = $this->currentData['penilaian_index_similarity'];
            $this->records['id_dokumen_penilaian_similarity'] = $this->currentData['id_dokumen_penilaian_similarity'];
        } else {
            $this->records['penilaian_index_ai'] = $this->currentData['penilaian_index_ai'];
            $this->records['id_dokumen_penilaian_ai'] = $this->currentData['id_dokumen_penilaian_ai'];
        }

        $this->initFormSimilarity($type);

        $this->showModal('modal-similarity');

        $this->disableSubmit = false;
    }

    public function updatedRecordsIdDokumenPenilaianSimilarity($value)
    {
        $this->disableSubmit = false;
    }

    public function updatedRecordsIdDokumenPenilaianAi($value)
    {
        $this->disableSubmit = false;
    }

    public function similarity_submitForm()
    {
        $this->validateForm();

        $type = $this->titleSimilarity == 'Similarity Proposal' ? 'similarity_proposal' : 'ai';

        $key = $type == 'similarity_proposal' ? 'penilaian_index_similarity' : 'penilaian_index_ai';
        $keyDocument = $type == 'similarity_proposal' ? 'id_dokumen_penilaian_similarity' : 'id_dokumen_penilaian_ai';

        if ($this->records[$key] < 0) {
            $this->throwValidationError($key, 'Nilai tidak boleh kurang dari 0');
        }

        if ($this->records[$key] > 100) {
            $this->throwValidationError($key, 'Nilai tidak boleh lebih dari 100');
        }

        $data = $this->service->updateSimilarity([
            $key => $this->records[$key],
            $keyDocument => $this->records[$keyDocument] ?? null,
        ], $this->idProposalPendanaan);

        if (HelpersError::isError($data)) {
            $err = explode(':', $data->message);
            $this->throwValidationError($err[0], $err[1]);
        }

        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();

        $this->data = $this->defineFormFields();

        $this->initAlert('success', 'Data berhasil ' . ($this->isEdit ? 'diperbarui' : 'ditambahkan'));

        $this->hideModal('modal-similarity');
    }
    /* End Similiarity Page */

    /* Aktivitas Peneliti Page */
    public function initFormAktivitasPenelitian()
    {
        $this->fields = [
            [
                'field' => 'tanggal_aktivitas_penelitian',
                'label' => 'Tanggal Aktivitas',
                'control' => 'date',
                'inline' => true,
                'value' => $this->records['tanggal_aktivitas_penelitian'],
                'required' => true,
                'wire:model.live' => 'records.tanggal_aktivitas_penelitian',
            ],
            [
                'field' => 'nama_aktivitas_penelitian',
                'label' => 'Nama Aktivitas',
                'inline' => true,
                'value' => $this->records['nama_aktivitas_penelitian'],
                'required' => true,
                'wire:model.live' => 'records.nama_aktivitas_penelitian',
            ],
            [
                'field' => 'jenis_aktivitas',
                'label' => 'Jenis Aktivitas',
                'inline' => true,
                'value' => $this->records['jenis_aktivitas'],
                'required' => true,
                'wire:model.live' => 'records.jenis_aktivitas',
            ],
            [
                'field' => 'lokasi_aktivitas_penelitian',
                'label' => 'Tempat Aktivitas',
                'inline' => true,
                'value' => $this->records['lokasi_aktivitas_penelitian'],
                'required' => true,
                'wire:model.live' => 'records.lokasi_aktivitas_penelitian',
            ],
            [
                'field' => 'id_dokumen_logbook',
                'label' => 'Upload Dokumen Pendukung',
                'file_type' => PengajuanPendanaanAktivitasPenelitian::ALLOWED_EXTENSION,
                'required' => $this->isEdit ? false : true,
                'max_size' => (1024 * 5),
                'wire:model.live' => 'records.id_dokumen_logbook',
            ]
        ];

        $this->disableSubmit = false;
    }

    public function updatedRecordsJenisAktivitas($value)
    {
        $this->disableSubmit = false;
    }

    public function updatedRecordsNamaAktivitasPenelitian($value)
    {
        $this->disableSubmit = false;
    }

    public function updatedRecordsTanggalAktivitasPenelitian($value)
    {
        $this->disableSubmit = false;
    }

    public function updatedRecordsLokasiAktivitasPenelitian($value)
    {
        $this->disableSubmit = false;
    }

    public function updatedRecordsIdDokumenLogbook($value)
    {
        $this->disableSubmit = false;
    }

    public function aktpenelitian_defaultRecord()
    {
        $this->records = [
            'tanggal_aktivitas_penelitian' => null,
            'nama_aktivitas_penelitian' => null,
            'jenis_aktivitas' => null,
            'lokasi_aktivitas_penelitian' => null,
            'id_dokumen_logbook' => null,
        ];
    }

    public function aktpenelitian_addForm()
    {
        $this->isEdit = false;

        $this->aktpenelitian_defaultRecord();

        $this->initFormAktivitasPenelitian();

        $this->showModal('modal-aktivitas-penelitian');
    }

    public function aktpenelitian_removeForm()
    {
        PengajuanPendanaanAktivitasPenelitian::find($this->removeId)->delete();

        $this->dataAktivitasPenelitian = PengajuanPendanaanAktivitasPenelitian::where('id_pengajuan_pendanaan', $this->idProposalPendanaan)->get();

        $this->initAlert('success', 'Aktivitas peneliti berhasil dihapus');

        $this->hideModal('modal-remove');
    }

    public function aktpenelitian_editForm($id)
    {
        $this->isEdit = true;

        $record = PengajuanPendanaanAktivitasPenelitian::find($id);

        $this->records = [
            'id' => $record->id,
            'tanggal_aktivitas_penelitian' => $record->tanggal_aktivitas_penelitian,
            'nama_aktivitas_penelitian' => $record->nama_aktivitas_penelitian,
            'jenis_aktivitas' => $record->jenis_aktivitas,
            'lokasi_aktivitas_penelitian' => $record->lokasi_aktivitas_penelitian,
            'id_dokumen_logbook' => null,
        ];

        $this->initFormAktivitasPenelitian();

        $this->showModal('modal-aktivitas-penelitian');
    }

    public function aktpenelitian_submitForm()
    {
        $this->validateForm();

        $service = new PengajuanPendanaanAktivitasPenelitianService;

        if (!$this->isEdit) {
            $store = $service->store($this->records, $this->idProposalPendanaan);
        } else {
            $data = $this->records;
            unset($data['id']);
            $store = $service->update($data, $this->idProposalPendanaan, $this->records['id']);
        }

        if (HelpersError::isError($store)) {
            $err = explode(':', $store->message);
            $this->throwValidationError($err[0], $err[1]);
        }

        $this->dataAktivitasPenelitian = $service->getDaftarByIdPengajuanPendanaan($this->idProposalPendanaan);

        $this->aktpenelitian_defaultRecord();

        $this->initAlert('success', 'Aktivitas peneliti berhasil ' . ($this->isEdit ? 'diperbarui' : 'ditambahkan'));

        $this->hideModal('modal-aktivitas-penelitian');
    }
    /* End Aktivitas Peneliti Page */

    /* Laporan Progres Report Page */
    public function initFormProgreport()
    {
        $this->fields = [
            [
                'field' => 'id_dokumen_report',
                'label' => 'Upload Dokumen Laporan Antara',
                'file_type' => ['pdf'],
                'max_size' => (1024 * 5),
                'required' => true,
                'wire:model.live' => 'records.id_dokumen_report',
            ],
        ];

        $this->disableSubmit = false;
    }

    public function updatedRecordsIdDokumenReport($value)
    {
        $this->disableSubmit = false;
    }

    public function progreport_loadData()
    {
        list($this->dataLaporanAntara, $this->dataPenilaianReviewerOutput) = $this->service->getMappingLaporanProgresAntara($this->idProposalPendanaan);
    }

    public function progreport_defaultRecord($jenis = null)
    {
        $this->records = [
            'id_dokumen_report' => null,
            'jenis_laporan_progres' => $jenis,
        ];
    }

    public function progreport_addForm($jenis)
    {
        $this->isEdit = false;

        $this->progreport_defaultRecord($jenis);

        $this->showModal('modal-laporan-antara');
    }

    public function progreport_submitForm()
    {
        $this->validateForm();

        $service = new PengajuanPendanaanLaporanProgresService;

        $check = $service->storeDokumen($this->records, $this->idProposalPendanaan);

        if (HelpersError::isError($check)) {
            $err = explode(':', $check->message);
            $this->throwValidationError($err[0], $err[1]);
        }

        $this->progreport_loadData();

        $this->progreport_defaultRecord();

        $this->initAlert('success', 'Berhasil mengunggah dokumen laporan antara');

        $this->hideModal('modal-laporan-antara');
    }
    /* End Laporan Progres Report Page */

    /* Publikasi Page */
    public function initFormPublikasi()
    {
        $listIdJenisOutcome = KlasterPendanaanOutcomePenelitian::where('id_klaster_pendanaan', $this->currentData['id_klaster_pendanaan'])
            ->where('apakah_wajib', true)
            ->pluck('id_jenis_outcome_penelitian')
            ->toArray();

        $this->fields = [
            [
                'field' => 'id_jenis_outcome_penelitian',
                'label' => 'Outcome',
                'selected' => $this->records['id_jenis_outcome_penelitian'],
                'options' => JenisOutcomePenelitian::whereIn('id', $listIdJenisOutcome)->pluck('nama_outcome', 'id')->toArray(),
                'required' => true,
                'variant' => 'search',
                'wire:change' => 'publikasi_changeJenisOutcome($event.target.value)',
            ],
            [
                'field' => $this->activeTab == 1 ? 'judul_artikel' : 'judul_buku',
                'label' => 'Judul ' . ($this->activeTab == 1 ? 'Artikel' : 'Buku'),
                'required' => true,
                'wire:model.live' => 'records.' . ($this->activeTab == 1 ? 'judul_artikel' : 'judul_buku'),
            ],
        ];

        if ($this->activeTab == 1) {
            $this->fields[] = [
                'field' => 'situs_publikasi_jurnal',
                'label' => 'Tempat Jurnal',
                'required' => true,
                'wire:model.live' => 'records.situs_publikasi_jurnal',
            ];
        }

        $this->fields[] = [
            'field' => $this->activeTab == 1 ? 'volume_dan_nomor_terbitan' : 'penerbit_buku',
            'label' => $this->activeTab == 1 ? 'Volume & Nomor Terbitan' : 'Penerbit Buku',
            'required' => true,
            'wire:model.live' => 'records.' . ($this->activeTab == 1 ? 'volume_dan_nomor_terbitan' : 'penerbit_buku'),
        ];

        $this->fields[] = [
            'field' => $this->activeTab == 1 ? 'url_artikel' : 'isbn',
            'label' => $this->activeTab == 1 ? 'URL Artikel' : 'ISBN',
            'type' => $this->activeTab == 1 ? 'url' : 'text',
            'required' => true,
            'wire:model.live' => 'records.' . ($this->activeTab == 1 ? 'url_artikel' : 'isbn'),
        ];

        if ($this->activeTab == 2) {
            $this->fields[] = [
                'field' => 'tahun_terbit_buku',
                'label' => 'Tahun Terbit',
                'required' => true,
                'type' => 'number',
                'wire:model.live' => 'records.tahun_terbit_buku',
            ];
        }
    }

    public function publikasi_ChangeTab($index)
    {
        $this->activeTab = $index;

        $this->publikasi_defaultRecord();

        $this->initFormPublikasi();

        if ($this->activeTab == 1) {
            $service = new PengajuanPendanaanPublikasiArtikelService;
        } else {
            $service = new PengajuanPendanaanPublikasiBukuService;
        }

        $this->dataArtikelPublikasi = $service->indexPerPengajuanPendanaan(idPengajuanPendanaan: $this->idProposalPendanaan);
    }

    public function publikasi_changeJenisOutcome($value)
    {
        $this->records['id_jenis_outcome_penelitian'] = $value;

        $this->initFormPublikasi();
    }

    public function publikasi_defaultRecord()
    {
        if ($this->activeTab == 1) {
            $this->records = [
                'id_jenis_outcome_penelitian' => null,
                'judul_artikel' => null,
                'situs_publikasi_jurnal' => null,
                'volume_dan_nomor_terbitan' => null,
                'url_artikel' => null,
            ];
        } else {
            $this->records = [
                'id_jenis_outcome_penelitian' => null,
                'judul_buku' => null,
                'penerbit_buku' => null,
                'isbn' => null,
                'tahun_terbit_buku' => null,
            ];
        }
    }

    public function publikasi_addForm()
    {
        $this->isEdit = false;

        $this->publikasi_defaultRecord();

        $this->initFormPublikasi();

        $this->showModal('modal-publikasi');
    }

    public function publikasi_editForm($id)
    {
        $this->isEdit = true;

        if ($this->activeTab == 1) {
            $record = PengajuanPendanaanPublikasiArtikel::find($id);

            $this->records = [
                'id' => $record->id,
                'judul_artikel' => $record->judul_artikel,
                'situs_publikasi_jurnal' => $record->situs_publikasi_jurnal,
                'volume_dan_nomor_terbitan' => $record->volume_dan_nomor_terbitan,
                'url_artikel' => $record->url_artikel,
            ];
        } else {
            $record = PengajuanPendanaanPublikasiBuku::find($id);

            $this->records = [
                'id' => $record->id,
                'judul_buku' => $record->judul_buku,
                'penerbit_buku' => $record->penerbit_buku,
                'isbn' => $record->isbn,
                'tahun_terbit_buku' => $record->tahun_terbit_buku,
            ];
        }

        $this->initFormPublikasi();

        $this->showModal('modal-publikasi');
    }

    public function publikasi_removeForm()
    {
        if ($this->activeTab == 1) {
            $service = new PengajuanPendanaanPublikasiArtikelService;
        } else {
            $service = new PengajuanPendanaanPublikasiBukuService;
        }

        $service->destroy($this->removeId);

        $this->dataArtikelPublikasi = $service->indexPerPengajuanPendanaan(idPengajuanPendanaan: $this->idProposalPendanaan);

        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();

        $this->data = $this->defineFormFields();

        $this->initAlert('success', 'Publikasi berhasil dihapus');

        $this->hideModal('modal-remove');
    }

    public function publikasi_submitForm()
    {
        $this->validateForm();

        if ($this->activeTab == 1) {
            $service = new PengajuanPendanaanPublikasiArtikelService;
        } else {
            $service = new PengajuanPendanaanPublikasiBukuService;
        }

        if (!$this->isEdit) {
            $store = $service->store($this->records, $this->idProposalPendanaan);
        } else {
            $data = $this->records;
            unset($data['id']);
            $store = $service->update($data, $this->records['id']);
        }

        if (HelpersError::isError($store)) {
            $this->initAlert('danger', $store->message);

            $this->hideModal('modal-publikasi');

            return;
        }

        $this->dataArtikelPublikasi = $service->indexPerPengajuanPendanaan(idPengajuanPendanaan: $this->idProposalPendanaan);

        $this->currentData = $this->service->show($this->idProposalPendanaan)->toArray();

        $this->data = $this->defineFormFields();

        $this->publikasi_defaultRecord();

        $this->initAlert('success', 'Publikasi berhasil ' . ($this->isEdit ? 'diperbarui' : 'ditambahkan'));

        $this->hideModal('modal-publikasi');
    }
    /* End Publikasi Page */
}