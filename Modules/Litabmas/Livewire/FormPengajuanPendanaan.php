<?php

namespace Modules\Litabmas\Livewire;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\WithFileUploads;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Page;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Livewire\CreateEditComponent;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\Pegawai;
use Modules\Gate\Models\Role;
use Modules\Gate\Models\User;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Enums\StatusAgendaKegiatanEnum;
use Modules\Litabmas\Models\AgendaKegiatan;
use Modules\Litabmas\Models\PengajuanPendanaanAnggota;
use Modules\Litabmas\Models\TemaKegiatan;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Models\SumberPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\BidangIlmu;
use Modules\Litabmas\Models\PengajuanPendanaanStatus2;
use Modules\Litabmas\Services\AspekPenilaianIsianProposalService;
use Modules\Litabmas\Services\KlasterPendanaanService;
use Modules\Litabmas\Services\PengajuanPendanaanAnggotaService;
use Modules\Litabmas\Services\PengajuanPendanaanService;
use Modules\Litabmas\Services\PengajuanPendanaanReviewerService;

class FormPengajuanPendanaan extends CreateEditComponent
{
    use LitabmasViewData, WithFileUploads;

    // protected yg dari CreateEditComponent
    protected array $customViewData = [
        'showCollapseInSection' => true
    ];

    // protected serivce
    protected PengajuanPendanaanAnggotaService $pengajuanPendanaanAnggotaService;
    protected PengajuanPendanaanReviewerService $reviewerService;
    protected AspekPenilaianIsianProposalService $aspekPenilaianIsianProposalService;
    protected KlasterPendanaanService $klasterPendanaanService;

    // custom property public
    public $idPeriodePendanaan = null;
    public $idAgendaKegiatanPendaftaran = null;
    public $kodeJenisPendanaan = null;
    public $idBidangIlmu = null;
    public $idTemaKegiatan = null;
    public $idSumberPendanaan = null;
    public $idKlasterPendanaan = null;
    public $currentIdBiodata = null;
    public $isInitEdit = false; // apakah merupakan state edit pertama kali (sblm livewire render lagi)

    public bool $forceCannotCreate = false; // memaksa button front-end tidak bisa di klik
    public array $alertGeneralInfo = []; // alert umum muncul di paling atas
    public array $alertIsianProposal = []; // alert khusus section isian proposal
    public array $alertAnggota = []; // alert khusus section anggota
    public bool $apakahAdaIsianProposal = false;
    public bool $apakahAdaKlasterPendanaan = false;
    public bool $apakahAdaSumberPendanaan = false;
    public $recordSavedAnggota;
    public array $requiredOutputs = [];
    public $oldRecordOutput = [];
    public $namaKetua = null;
    public $nipKetua = null;

    public $apakahButuhApproveSemuaAnggota = false;
    public $kategoriKlaster;
    public $minimalAnggota;
    public $maksimalAnggota;
    public $periodePendanaanOptions = null;

    public $maksimalAnggaranKlasterPendanaan;
    public $mataUangKlasterPendanaan;

    public $idDokumenProposal;
    public $idDokumenRab;
    public $idFotoTabungan;
    public $apakahSnkDisetujui = false;

    public $confirmData = [];
    public $hasUploadDokumenProposal = false;
    public $hasUploadDokumenRab = false;
    public $headerInfoKlaster;
    public $isUploading = false;

    public $steps = 1;
    public $stepsAnchor = [
        1 => 'pernyataan-proposal',
        2 => 'komponen-proposal',
        3 => 'data-peneliti',
        4 => 'detail-pendanaan',
    ];

    private string $prevUrl;
    private $dataPengajuanPendanaan;
    private $klasterPendanaanOptions;
    private $sumberPendanaanOptions;
    private $temaKegiatanOptions;
    private $bidangIlmuOptions;

    #[Renderless]
    protected function loadModel()
    {
        $this->model = PengajuanPendanaan::class;
    }

    #[Renderless]
    protected function loadService()
    {
        $this->service = new PengajuanPendanaanService;
        $this->pengajuanPendanaanAnggotaService = new PengajuanPendanaanAnggotaService;
        $this->reviewerService = new PengajuanPendanaanReviewerService;
        $this->aspekPenilaianIsianProposalService = new AspekPenilaianIsianProposalService;
        $this->klasterPendanaanService = new KlasterPendanaanService;
    }

    /**
     * Dipanggil ketika setiap awal request (sebelum mount).
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->isInitEdit = false;
    }

    /**
     * Dipanggil sekali ketika component pertama kali terbuat (setelah boot tapi hanya sekali).
     *
     * @return void|RedirectResponse
     */
    public function mount()
    {
        parent::mount();

        // cek hak akses dosen eksternal tidak bisa create
        if (auth()->user()?->kode_role === Role::ROLE_DOSEN_EKSTERNAL) {
            return redirect()->route('litabmas.pengumuman-klaster.index');
        }

        // get full current url to get klaster_id
        $queryStringCurrent = parse_url(url()->full())['query'] ?? '';
        $paramsCurrent = [];
        parse_str($queryStringCurrent, $paramsCurrent);

        $this->headerInfoKlaster['x_kjp'] = $paramsCurrent['x_kjp'] ?? null;
        $this->headerInfoKlaster['x_kis'] = $paramsCurrent['x_kis'] ?? null;
        $this->headerInfoKlaster['x_kid'] = $paramsCurrent['x_kid'] ?? null;

        // current id biodata
        $myBiodata = Biodata::where('id_user', auth()->user()->id)->first();
        $this->currentIdBiodata = $myBiodata->id;

        //check if parameter is null
        if (empty($this->edit)) {
            $draft = $this->service->getDraftPengajuanByBiodata($this->headerInfoKlaster['x_kid'], $this->currentIdBiodata);

            if ($draft) {
                return redirect()->route('litabmas.pengajuan-pendanaan.edit', $draft->id_pengajuan_pendanaan);
            }

            if ((empty($this->headerInfoKlaster['x_kjp']) || empty($this->headerInfoKlaster['x_kis']) || empty($this->headerInfoKlaster['x_kid']))) {
                return redirect()->route('litabmas.pengumuman-klaster.index')->with('error', 'Silahkan Pilih Klaster');
            }
        }

        //get data from parameter
        $header = empty($this->edit) ?
            (new PengajuanPendanaanService)->getHeaderPengajuan(idKlaster: $this->headerInfoKlaster['x_kid'], idSumber: $this->headerInfoKlaster['x_kis']) : (new PengajuanPendanaanService)->getHeaderPengajuan($this->edit);
        $this->headerInfoKlaster['nama_klaster'] = $header->nama_klaster;
        $this->headerInfoKlaster['nama_sumber'] = $header->nama_sumber_pendanaan;
        $this->headerInfoKlaster['pengelola'] = $header->nama_pengelola_bantuan;
        $this->headerInfoKlaster['jenis_pendanaan'] = ucfirst($header->kode_jenis_pendanaan);

        if (!empty($this->edit)) {
            $this->headerInfoKlaster['x_kjp'] = $header->kode_jenis_pendanaan;
            $this->headerInfoKlaster['x_kis'] = $header->id_sumber_pendanaan;
            $this->headerInfoKlaster['x_kid'] = $header->id_klaster_pendanaan;
        }

        $periodeAktif = PeriodePendanaan::periodeAktif();
        $this->idPeriodePendanaan = $periodeAktif->id ?? null;
        $agendaKegiatan = (new AgendaKegiatan())->getListCache();
        $this->idAgendaKegiatanPendaftaran = $agendaKegiatan->where('kode_agenda', AgendaKegiatan::STEP_PENDAFTARAN)
            ->first()?->id;

        if (empty($this->edit)) {
            if (!empty($this->idPeriodePendanaan)) {
                // cek sudah mendaftar sebagai ketua di periode yg sama atau belum
                $infoKetua = $this->pengajuanPendanaanAnggotaService->cekMaksimalMendaftarSebagaiKetua($this->idPeriodePendanaan, $this->currentIdBiodata);
                if (!empty($infoKetua['isFull'])) {
                    $jmlhMaksimal = $infoKetua['max'];
                    $this->alertGeneralInfo = [
                        'type' => 'warning',
                        'title' => 'Tidak Bisa Membuat Pengajuan',
                        'message' => 'Berdasarkan aturan Periode Pendanaan, Anda sudah mencapai batas maksimal dalam mengajukan proposal sebagai Ketua (' . $jmlhMaksimal . ' kali).',
                    ];
                    $this->forceCannotCreate = true;
                }
            } else {
                $this->alertGeneralInfo = [
                    'type' => 'warning',
                    'title' => 'Tidak Bisa Membuat Pengajuan',
                    'message' => 'Periode pendanaan belum aktif, silakan hubungi Administrator.',
                ];
                $this->forceCannotCreate = true;
            }
        }

        if (!empty($this->edit)) {
            // jika masuk ke form edit ini tapi statusnya sudah bukan draft, maka redirect ke show
            $this->dataPengajuanPendanaan = $this->service->show($this->edit);
            $statusAgendaKegiatan = $this->dataPengajuanPendanaan['status_agenda_kegiatan'] ?? null;

            // jika sudah ditutup pendaftaran tidak bisa edit
            $apakahDitutup = $this->service->apakahPendaftaranDitutup($this->edit);

            if ($statusAgendaKegiatan !== StatusAgendaKegiatanEnum::DRAFT || $apakahDitutup) {
                return redirect()->route('litabmas.pengajuan-pendanaan.show', $this->edit) . '/overview';
            }
        }

        $this->periodePendanaanOptions = PeriodePendanaan::options();

        $biodata = Biodata::where('id', $this->currentIdBiodata)->first();
        $pegawai = Pegawai::where('ref_key_siakad', $biodata->ref_key_pegawai)->first();
        $this->namaKetua = auth()->user()?->nama_user;
        $this->nipKetua = $pegawai->nip;

        $this->isInitEdit = true;

        // set default options
        $this->bidangIlmuOptions = $this->getBidangIlmuOptions(
            idKlasterPendanaan: $this->headerInfoKlaster['x_kid'],
            idSumberPendanaan: $this->headerInfoKlaster['x_kis'],
            kodeJenisPendanaan: $this->headerInfoKlaster['x_kjp']
        );

        $this->temaKegiatanOptions = $this->getTemaKegiatanOptions();
    }

    /**
     * Jalan setelah loadData()
     *
     * @return void
     */
    public function beforeRender()
    {
        $this->dispatch('wysiwyg'); // rerender wysiwyg

        $this->dispatch('hide-loading');

        // [START] Klaster Pendanaan
        if (empty($this->idKlasterPendanaan)) {
            $this->requiredOutputs = [];
            $this->apakahButuhApproveSemuaAnggota = false;
            $this->kategoriKlaster = $this->minimalAnggota = $this->maksimalAnggota = null;
            $this->maksimalAnggaranKlasterPendanaan = $this->mataUangKlasterPendanaan = null;

            return;
        }

        // keperluan general information section
        $result = $this->klasterPendanaanService->getOutputWajibByIdKlasterPendanaan($this->idKlasterPendanaan);
        $this->requiredOutputs = $result->isEmpty() ? [] : $result->toArray();

        // keperluan member & rekening section
        $result = $this->klasterPendanaanService->getInfoAnggotaDanAnggaran($this->idKlasterPendanaan);
        $this->setPropertySideEffectKlaster($result);
        // [END] Klaster Pendanaan

        // [START] Cek limitasi pengajuan per klaster
        if (!empty($this->idKlasterPendanaan) && empty($this->edit)) {
            $infoKlaster = $this->pengajuanPendanaanAnggotaService->cekMaksimalPengajuanPerKlaster(
                $this->idKlasterPendanaan,
                $this->currentIdBiodata
            );

            if (!empty($infoKlaster['isFull'])) {
                $this->alertGeneralInfo = [
                    'type' => 'warning',
                    'title' => 'Tidak Bisa Membuat Pengajuan',
                    'message' => $infoKlaster['message'] ?? 'Anda sudah mencapai batas maksimal pengajuan pada klaster ini.',
                ];
                $this->forceCannotCreate = true;
                $this->dispatch('scroll-to-top');
            }
        }
        // [END] Cek limitasi pengajuan per klaster

        // [START] Sumber Pendanaan
        // cek sudah jadi reviewer di sumber pendanaan ini atau belum
        $alreadyReviewer = false;
        if (!empty($this->idSumberPendanaan)) {
            // reviewer administrasi
            $alreadyReviewer = $this->reviewerService->isReviewerBySumberPendanaan($this->idSumberPendanaan);
        }

        if ($alreadyReviewer) { // sudah terdaftar sebaagai reviewer
            $this->alertGeneralInfo = [
                'type' => 'warning',
                'title' => 'Tidak Bisa Membuat Pengajuan',
                'message' => 'Anda sudah terdaftar sebagai reviewer di Sumber Pendanaan tersebut.',
            ];
            $this->forceCannotCreate = true;
            $this->dispatch('scroll-to-top');
        } else {
            $this->alertGeneralInfo = [];
            $this->forceCannotCreate = false;
        }
        // [END] Sumber Pendanaan
    }

    #[Renderless]
    public function updatedIdBidangIlmu($value)
    {
        $this->dispatch('show-loading');

        $value = $this->selectValue($value);

        $this->idBidangIlmu = !empty($value) ? $value : null;

        $this->idTemaKegiatan = null;
    }

    #[Renderless]
    public function updatedIdTemaKegiatan($value)
    {
        $this->dispatch('show-loading');

        $value = $this->selectValue($value);

        $this->idTemaKegiatan = !empty($value) ? $value : null;
    }

    #[Renderless]
    public function updatedIdSumberPendanaan($value)
    {
        $this->dispatch('show-loading');

        $value = $this->selectValue($value);

        $this->idSumberPendanaan = !empty($value) ? $value : null;

        $this->apakahAdaSumberPendanaan = !empty($this->idSumberPendanaan);
    }

    #[Renderless]
    public function updatedIdKlasterPendanaan($value)
    {
        $this->dispatch('show-loading');

        $value = $this->selectValue($value);

        $this->idKlasterPendanaan = !empty($value) ? $value : null;

        $this->apakahAdaKlasterPendanaan = !empty($this->idKlasterPendanaan);
    }

    #[On('save-member-from-child')]
    public function saveAnggota($newRecord = [])
    {
        // pemrosesan ada di child MemberForm.php
        $this->recordSavedAnggota = $newRecord;
    }

    /**
     * Proses save tapi dengan validasi berbeda.
     * Hanya untuk menyimpan pengajuan untuk sementara, tanpa mengajukan proposalnya.
     *
     * @return \Illuminate\Http\RedirectResponse|void|null
     * @throws ValidationException
     */
    public function draft($isStateCreate = false)
    {
        // validasi data
        $fields = WebRequest::buildFields($this->model, $this->defineFormFields(), flattenFields: true);
        $data = Arr::only($this->record, array_map(fn($item) => $item['field'], $fields));
        foreach ($data as $key => $value) {
            $filterField = array_filter($fields, fn($item) => $item['field'] == $key);
            $filterField = array_values($filterField)[0] ?? [];

            if (isset($filterField['options']) && (isset($filterField['control']) && $filterField['control'] == 'select')) {
                $selectValue = $this->selectValue($value);
                $data[$key] = $selectValue;

                // Jika select dan tidak ada value maka set null
                if (is_null($selectValue)) {
                    $data[$key] = null;
                }
            }

            // penyesuaian jika memiliki control
            if (isset($filterField['control'])) {
                if ($filterField['control'] === 'switch') {
                    $data[$key] = $value ?? false;
                }
            }

            if (isset($filterField['format-currency'])) {
                $data[$key] = str_replace('.', '', $value);
            }

            if (isset($filterField['type']) && $filterField['type'] === 'number') {
                $data[$key] = $value === '' ? null : $value;
            }
        }

        $attributes = Page::defineLabelByField(false, $this->urlInfo);

        try {
            // khusus draft, yg required hanya yg ada di card Pernyataan Proposal
            $dataForValidate = Arr::only($data, [
                'id_periode_pendanaan',
                'kode_jenis_pendanaan',
                'judul_penelitian',
                'id_bidang_ilmu',
                'id_tema_kegiatan',
                'id_sumber_pendanaan',
                'id_klaster_pendanaan',
            ]);

            // cek custom validasi draft jika ada
            $resultCustomValidate = $this->customValidationSaveDraft();

            // cek validasi model
            WebRequest::validateData($dataForValidate, $this->model, $this->edit, $attributes, $this->messageValidation());
        } catch (\Exception $e) {
            $this->dispatch('hide-loading');
            throw $e;
        }

        if ($resultCustomValidate === false) { // jika custom validasi gagal
            $this->dispatch('hide-loading');
            return;
        }

        // Sanitasi input
        $data = WebRequest::sanitizeXSS($data, $this->model);

        if (!empty($this->mergeData)) {
            $data = array_merge($data, $this->mergeData);
        }

        if (!empty($this->choices)) {
            $data = array_merge($data, $this->choices);
        }

        try {
            if ($this->edit) {
                $return = $this->service->update($data, $this->edit);
            } else {
                $return = $this->service->store($data);
            }
        } catch (ValidationException $e) {
            $this->dispatch('hide-loading');
            throw $e;
        }

        if (Error::isError($return)) {
            $this->dispatch('hide-loading');

            // Jika error, maka kembalikan alert
            $this->alert = [
                'type' => 'error',
                'message' => $return->message,
            ];

            $this->dispatch('scroll-to-top');

            return $this->loadData();
        }

        $message = 'Draft berhasil disimpan';

        if (count($this->urlInfo['segments']) > 4) {
            // delete last segment
            array_pop($this->urlInfo['segments']);

            if ($this->edit) {
                return redirect()->to(implode('/', $this->urlInfo['segments']))
                    ->with('success', $message);
            } else {
                return redirect()->to(implode('/', $this->urlInfo['segments']) . '/' . $return->id)
                    ->with('success', $message);
            }
        }

        if (!$isStateCreate) {
            $url = route('litabmas.pengajuan-pendanaan.show', $return->id) . '/overview';
            return redirect()->to($url)->with('success', $message);
        } else {
            $this->edit = $return->id;
        }
    }

    public function confirmSave()
    {
        $this->draft(true);

        $this->dispatch('confirm-save');
    }

    public function nextStep($setStep = null)
    {
        $isError = false;
        $sectionError = null;

        if ($setStep) {
            $this->steps = $setStep;
        } else {
            switch ($this->steps) {
                case 1:
                    [$isError, $sectionError] = $this->pernyataanProposalValidation($isError, $sectionError);

                    if (!$isError) {
                        $this->draft(true);
                    }
                    break;
                case 2:
                    if (!$this->edit) {
                        $draft = $this->service->getDraftPengajuanByBiodata($this->headerInfoKlaster['x_kid'], $this->currentIdBiodata);

                        if (!$draft) {
                            $isError = true;
                            $sectionError = 'informasi-umum';
                            $this->steps = 1;
                            $this->nextStep();
                        }
                    } else {
                        [$isError, $sectionError] = $this->isianProposalValidation($isError, $sectionError);

                        if (!$isError) {
                            $this->draft(true);
                        }
                    }
                    break;
                case 3:
                    if (!$this->edit) {
                        $draft = $this->service->getDraftPengajuanByBiodata($this->headerInfoKlaster['x_kid'], $this->currentIdBiodata);

                        if (!$draft) {
                            $isError = true;
                            $sectionError = 'informasi-umum';
                            $this->steps = 1;
                            $this->nextStep();
                        }
                    } else {
                        [$isError, $sectionError] = $this->memberValidation($isError, $sectionError);

                        if (!$isError) {
                            $this->draft(true);
                        }
                    }
                    break;
                case 4:
                    if (!$this->edit) {
                        $draft = $this->service->getDraftPengajuanByBiodata($this->headerInfoKlaster['x_kid'], $this->currentIdBiodata);

                        if (!$draft) {
                            $isError = true;
                            $sectionError = 'informasi-umum';
                            $this->steps = 1;
                            $this->nextStep();
                        }
                    } else {
                        [$isError, $sectionError] = $this->rekeningValidation($isError, $sectionError);

                        if (!$isError) {
                            $validations = [
                                'pernyataanProposalValidation',
                                'isianProposalValidation',
                                'memberValidation'
                            ];

                            foreach ($validations as $step => $validation) {
                                [$isError, $sectionError] = $this->$validation($isError, $sectionError);

                                if ($isError) {
                                    $this->steps = $step + 1;
                                    break;
                                }
                            }
                        }
                    }
                    break;
            }

            if ($isError) {
                $this->loadData();
                $this->dispatch('scroll-to-section', $sectionError);
                $this->dispatch('hide-loading');
                return false;
            }

            $this->steps++;

            if ($this->steps > 4) {
                $this->steps = 4;
                $this->confirmSave();
            }
        }

        $this->dispatch('scroll-to-top');
        $this->dispatch('hide-loading');
    }

    public function save()
    {
        $this->service->ajukanProposal($this->edit);

        $url = route('litabmas.pengajuan-pendanaan.show', $this->edit) . '/overview';

        return redirect()->to($url)->with('success', 'Proposal berhasil diajukan');
    }

    protected function successUrlAfterSave()
    {
        return route('litabmas.pengajuan-pendanaan.show', $this->edit) . '/overview';
    }

    public function customValidationBeforeSave(): bool
    {
        $this->alertIsianProposal = $this->alertAnggota = [];

        // masukkan cutome record ke mergeData
        $this->mergeData['id_biodata_leader'] = $this->currentIdBiodata ?? null;
        $this->mergeData['_anggota_penelitian'] = $this->recordSavedAnggota;
        $this->mergeData['jenis_output_penelitian'] = $this->record['jenis_output_penelitian'] ?? null;
        $this->mergeData['waktu_snk_disetujui'] = $this->apakahSnkDisetujui ? now() : null;
        $this->mergeData['status_agenda_kegiatan'] = $this->apakahButuhApproveSemuaAnggota
            ? PengajuanPendanaanStatus2::LEVEL2_KONFIRMASI_ANGGOTA
            : PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN;
        $this->mergeData['current_id_biodata'] = $this->currentIdBiodata ?? null;
        $this->mergeData['is_draft'] = false;

        return true;
    }

    private function customValidationSaveDraft()
    {
        $this->mergeData['id_periode_pendanaan'] = $this->idPeriodePendanaan ?? null;
        $this->mergeData['id_dokumen_proposal'] = $this->idDokumenProposal ?? null;
        $this->mergeData['id_dokumen_rab'] = $this->idDokumenRab ?? null;
        $this->mergeData['id_foto_tabungan'] = $this->idFotoTabungan ?? null;
        $this->mergeData['status_agenda_kegiatan'] = StatusAgendaKegiatanEnum::DRAFT;
        $this->mergeData['id_biodata_leader'] = $this->currentIdBiodata ?? null;
        $this->mergeData['_anggota_penelitian'] = $this->recordSavedAnggota;
        $this->mergeData['jenis_output_penelitian'] = $this->record['jenis_output_penelitian'] ?? null;
        $this->mergeData['waktu_snk_disetujui'] = $this->apakahSnkDisetujui ? now() : null;
        $this->mergeData['current_id_biodata'] = $this->currentIdBiodata ?? null;
        $this->mergeData['is_draft'] = true;

        return true;
    }

    private function pernyataanProposalValidation($isError, $sectionError)
    {
        //validate pernyataan proposal section
        $fields = $this->defineFormFields()['informasi-umum']['items'];

        // cek semua field required
        foreach ($fields as $field) {
            $value = $this->record[$field['field']] ?? null;
            if (empty($value)) {
                $isError = true;
                $sectionError ??= 'informasi-umum';
                $this->addError(
                    $field['field'],
                    __('validation.required', ['attribute' => __('litabmas::pengajuan_pendanaan.' . $field['field'])])
                );
            }
        }


        return [$isError, $sectionError];
    }

    private function isianProposalValidation($isError, $sectionError)
    {
        // cek current jenis pendanaan
        $kodeJenisPendanaan = $this->kodeJenisPendanaan;
        // get semua field yg ada di isian_proposal__{kode_jenis_pendanaan}__*
        $isianProposalFields = array_filter(array_keys($this->record), function ($field) use ($kodeJenisPendanaan) {
            return preg_match("/^isian_proposal__{$kodeJenisPendanaan}__/", $field);
        });
        // cek tidak boleh ada $record yg kosong berdasarkan $isianProposalFields
        foreach ($isianProposalFields as $field) {
            $regexScript = '/<script\b[^>]>(.?)<\/script>/is';
            $value = $this->record[$field] ?? null;
            $value = preg_replace($regexScript, '', $value);

            // Pengecekan jika kosong atau <p></p> tag yang tidak ada textnya
            $regexNull = '/(<([^>]+)>)/i';
            $hasText = (bool) preg_replace($regexNull, '', $value);


            if (empty($value) || !$hasText) {
                $isError = true;
                $sectionError ??= 'isian-proposal';
                $this->addError(
                    $field,
                    __('validation.required', ['attribute' => 'Isian proposal'])
                );
            }
        }

        // cek harus upload file proposal
        if (empty($this->idDokumenProposal) && empty($this->hasUploadDokumenProposal)) {
            $isError = true;
            $sectionError ??= 'isian-proposal';
            $this->addError(
                'id_dokumen_proposal',
                __('validation.required', ['attribute' => 'File proposal'])
            );
        }

        return [$isError, $sectionError];
    }

    private function memberValidation($isError, $sectionError)
    {
        if (!empty($this->kategoriKlaster)) {
            if ($this->kategoriKlaster === KlasterPendanaan::KATEGORI_KELOMPOK) {
                // cek minimal anggota
                $totalAnggota = $this->recordSavedAnggota ? count($this->recordSavedAnggota) : 0;
                if ($totalAnggota < $this->minimalAnggota) {
                    $isError = true;
                    $sectionError ??= 'data-peneliti';
                    $this->alertAnggota = [
                        'type' => 'error',
                        'message' => 'Berdasarkan aturan Klaster yang Anda pilih, minimal anggota peneliti adalah ' . $this->minimalAnggota
                    ];
                }

                // cek maksimal anggota
                if ($totalAnggota > $this->maksimalAnggota) {
                    $isError = true;
                    $sectionError ??= 'data-peneliti';
                    $this->alertAnggota = [
                        'type' => 'error',
                        'message' => 'Berdasarkan aturan Klaster yang Anda pilih, maksimal anggota peneliti adalah ' . $this->maksimalAnggota
                    ];
                }
            }
        }

        return [$isError, $sectionError];
    }

    private function rekeningValidation($isError, $sectionError)
    {
        $fields = $this->defineFormFields()['rekening']['items'];

        // cek yg required
        $excludeFieldCheckRequired = ['id_dokumen_rab'];
        foreach ($fields as $field) {
            if (
                isset($field['required'])
                && !in_array($field['field'], $excludeFieldCheckRequired)
                && $field['required'] === true
            ) {
                $value = $this->record[$field['field']] ?? null;
                if (empty($value)) {
                    $isError = true;
                    $sectionError ??= 'detail-pendanaan';
                    $this->addError(
                        $field['field'],
                        __('validation.required', ['attribute' => $field['label']])
                    );
                }
            }
        }

        // validasi snk tidak dichecklist
        if (empty($this->apakahSnkDisetujui)) {
            $isError = true;
            $sectionError ??= 'detail-pendanaan';
            $this->addError('snk', 'Syarat dan Ketentuan harus Anda setujui.');
        }

        // cek harus upload file rab
        if (empty($this->idDokumenRab) && empty($this->hasUploadDokumenRab)) {
            $isError = true;
            $sectionError ??= 'detail-pendanaan';
            $this->addError(
                'id_dokumen_rab',
                __('validation.required', ['attribute' => 'File Rancangan Anggaran Biaya (RAB)'])
            );
        }

        return [$isError, $sectionError];
    }

    protected function loadData()
    {
        // [Start] proses get rule dari model (dan set value jika edit)
        if ($this->edit) {
            WebRequest::validateId($this->edit);

            // get data pengajuan pendanaan
            $this->dataPengajuanPendanaan = $this->service->show($this->edit);
            if ($this->isInitEdit) {
                $this->idBidangIlmu = $this->dataPengajuanPendanaan['id_bidang_ilmu'] ?? null;
                $this->idTemaKegiatan = $this->dataPengajuanPendanaan['id_tema_kegiatan'] ?? null;
                $this->idSumberPendanaan = $this->dataPengajuanPendanaan['id_sumber_pendanaan'] ?? null;
                $this->idKlasterPendanaan = $this->dataPengajuanPendanaan['id_klaster_pendanaan'] ?? null;
            } else {
                $recBidangIlmu = !empty($this->selectValue($this->record['id_bidang_ilmu'])) ? $this->selectValue($this->record['id_bidang_ilmu']) : null;
                $recTemaKegiatan = !empty($this->selectValue($this->record['id_tema_kegiatan'])) ? $this->selectValue($this->record['id_tema_kegiatan']) : null;
                $recSumberPendanaan = !empty($this->selectValue($this->record['id_sumber_pendanaan'])) ? $this->selectValue($this->record['id_sumber_pendanaan']) : null;
                $recKlasterPendanaan = !empty($this->selectValue($this->record['id_klaster_pendanaan'])) ? $this->selectValue($this->record['id_klaster_pendanaan']) : null;

                $this->idBidangIlmu = $this->idBidangIlmu ?? $recBidangIlmu ?? null;
                $this->idTemaKegiatan = $this->idTemaKegiatan ?? $recTemaKegiatan ?? null;
                $this->idSumberPendanaan = $this->idSumberPendanaan ?? $recSumberPendanaan ?? null;
                $this->idKlasterPendanaan = $this->idKlasterPendanaan ?? $recKlasterPendanaan ?? null;
            }

            // keperluan document
            $this->hasUploadDokumenProposal = !empty($this->dataPengajuanPendanaan['id_dokumen_proposal']);
            $this->hasUploadDokumenRab = !empty($this->dataPengajuanPendanaan['id_dokumen_rab']);

            // get klaster pendanaan keperluan member & rekening section
            $klasterPendanaan = $this->idKlasterPendanaan
                ? $this->klasterPendanaanService->getInfoAnggotaDanAnggaran($this->idKlasterPendanaan)
                : null;
            $this->setPropertySideEffectKlaster($klasterPendanaan);

            $this->data = WebController::buildFormCard(
                $this->defineFormFields($this->dataPengajuanPendanaan),
                $this->model,
                $this->dataPengajuanPendanaan
            );
        } else {
            $this->data = WebController::buildFormCard(
                $this->defineFormFields(),
                $this->model,
                data: $this->record
            );
        }
        // [End] proses get rule dari model (dan set value jika edit)

        // Set wire:model (dan selected value ketika options)
        $this->initForm();

        // Set value ke record ketika edit
        $this->loadRecord();

        if ($this->isInitEdit) {
            $this->record['nominal_anggaran_diajukan'] = (float) $this->record['nominal_anggaran_diajukan'];
        }

        // Get data untuk confirmation sebelum submit
        $this->getDataForConfirmation();
    }

    /**
     * Proses set wire:model (dan selected ketika options)
     *
     * @return void
     */
    protected function initForm()
    {
        // init function utk process item
        $processItem = function ($item, &$record) {
            if (empty($item['wire:model'])) {
                $item['wire:model'] = 'record.' . $item['field'];
            }

            $record[$item['field']] = $record[$item['field']] ?? null;

            // TODO: Sementara masih menggunakan cara untuk mengakali choices
            if (isset($item['options'])) {
                $selectedValue = $this->selectValue($record[$item['field']]);
                if ($this->isInitEdit) {
                    if ($selectedValue !== null && !is_array($selectedValue)) {
                        $item = array_merge($item, ['selected' => $selectedValue, 'value' => $selectedValue]);
                    }
                } else {
                    if (!is_array($selectedValue)) {
                        $item = array_merge($item, ['selected' => $selectedValue, 'value' => $selectedValue]);
                    }
                }
            }

            return $item;
        };

        foreach ($this->data as $i => $card) {
            $items = $card['items'] ?? [];
            foreach ($items as $j => $item) {
                $this->data[$i]['items'][$j] = $processItem($item, $this->record);
            }
        }
    }

    /**
     * Set value ke record ketika edit
     *
     * @return void
     */
    protected function loadRecord()
    {
        if (empty($this->edit)) {
            // jika tidak ada updated select/record
            if (empty($this->record['kode_jenis_pendanaan'])) {
                $this->record['kode_jenis_pendanaan'] = $this->getKodeJenisPendanaan();
                $this->kodeJenisPendanaan = $this->record['kode_jenis_pendanaan'];
            }

            if (empty($this->record['id_periode_pendanaan'])) {
                $this->record['id_periode_pendanaan'] = $this->idPeriodePendanaan;
            }

            return;
        }
        foreach ($this->data as $items) {
            foreach ($items['items'] as $row) {
                // Jika file maka skip value
                if (isset($row['file_type'])) {
                    continue;
                }

                if (isset($row['control']) && $row['control'] === 'checkbox') {
                    $this->record[$row['field']] = [];

                    $records = $row['value'];

                    foreach ($records as $val) {
                        $value = $this->record[$row['field']][$val] ?? null;
                        if (!isset($value)) {
                            $this->record[$row['field']][$val] = true;
                        }
                    }

                    continue;
                }

                $fromInputValue = $this->record[$row['field']] ?? null;
                $value = $fromInputValue ?? $row['value'];

                if (isset($row['control']) && $row['control'] === 'switch') {
                    $value = $value ?? false;
                }

                $this->record[$row['field']] = $value;
            }
        }

        // set oldRecord to record
        if (empty($this->apakahSnkDisetujui)) {
            $this->apakahSnkDisetujui = $this->apakahSnkDisetujui ?? !empty($this->dataPengajuanPendanaan['waktu_snk_disetujui']);
        }

        // get data output (yang sudah dipilih/checked) berdasarkan id_pengajuan_pendanaan
        if (empty($this->oldRecordOutput)) {
            $checkedOutput = $this->service->getOutputByIdPengajuanPendanaan($this->edit);
            foreach ($checkedOutput as $output) {
                $this->oldRecordOutput[$output->id_jenis_output_penelitian] = $output->id_jenis_output_penelitian;
            }
            $this->record['jenis_output_penelitian'] = $this->oldRecordOutput;
        }

        // get data output (wajib) berdasarkan klaster pendanaan yg dipilih
        if (!empty($this->idKlasterPendanaan) && empty($this->requiredOutputs)) {
            $result = $this->klasterPendanaanService->getOutputWajibByIdKlasterPendanaan($this->idKlasterPendanaan);
            $this->requiredOutputs = $result->isEmpty() ? [] : $result->toArray();
        }

        // set data isian proposal
        $isianProposal = $this->service->getIsianProposalByIdPengajuanPendanaan($this->edit);
        foreach ($isianProposal as $isian) {
            $field = 'isian_proposal__' . $this->kodeJenisPendanaan . '__' . $isian->id_aspek_penilaian_isian_proposal;

            // jika record yg mengandung 'isian_proposal__{kode_jenis_pendanaan}__' kosong
            if ($this->isInitEdit) {
                $this->record[$field] = $isian->isian_proposal ?? null;
            }
        }

        // get data anggota/peneliti
        if (empty($this->mergeData['_anggota_penelitian'])) {
            $result = $this->service->getAnggotaPengajuanPendanaan($this->edit);
            foreach ($result as $anggota) {
                if (!empty($anggota->nama_unit_kerja)) {
                    $nama[$anggota->id_biodata] = ($anggota->nama_user ?? $anggota->nama) . ' (' . $anggota->nama_unit_kerja . ')';
                }
                $this->recordSavedAnggota[$anggota->id_biodata] = [
                    'memberType' => $anggota->jenis_anggota,
                    'id_biodata' => $anggota->id_biodata,
                    'name' => $nama[$anggota->id_biodata] ?? $anggota->nama_user ?? $anggota->nama ?? null,
                    'apakah_undangan_diterima' => $anggota->apakah_undangan_diterima,
                ];
            }

            $this->mergeData['_anggota_penelitian'] = $this->recordSavedAnggota;
        }
    }

    private function reOrderRecordSavedAnggot()
    {
        // order by memberType lalu name
        $this->recordSavedAnggota = collect($this->recordSavedAnggota)
            ->sortBy('name')
            ->sortBy('memberType')
            ->all();
    }

    private function setPropertySideEffectKlaster($klasterPendanaan)
    {
        // keperluan member section
        $this->apakahButuhApproveSemuaAnggota = $klasterPendanaan->apakah_butuh_approve_semua_anggota ?? false;
        $this->kategoriKlaster = $klasterPendanaan->kategori_klaster ?? null;
        $this->minimalAnggota = $klasterPendanaan->minimal_anggota ?? null;
        $this->maksimalAnggota = $klasterPendanaan->maksimal_anggota ?? null;

        // keperluan rekening section
        $this->maksimalAnggaranKlasterPendanaan = $klasterPendanaan->maksimal_anggaran ?? 0;
        $this->mataUangKlasterPendanaan = $klasterPendanaan->mata_uang ?? null;
    }

    #[Computed]
    private function getDataForConfirmation()
    {
        // utk confirm data saat simpan
        $periodeOpt = $this->periodePendanaanOptions;
        $mataUang = $this->mataUangKlasterPendanaan;
        $convert = $mataUang != config('money.defaults.currency');

        $usulanBiaya = null;
        if (!empty($this->record['nominal_anggaran_diajukan'])) {
            // jika dia tidak convert, maka perlu tambahan .00
            if ($convert) {
                $nominalAnggaran = $this->record['nominal_anggaran_diajukan'];
            } else {
                $nominalAnggaran = $this->record['nominal_anggaran_diajukan'] . '.00';
            }
            $nominalAnggaran = str_replace('.', '', $nominalAnggaran);
            $usulanBiaya = money((int) $nominalAnggaran ?? 0, $mataUang, $convert);
        }

        $confirmAnggota = [];
        $confirmAnggota[] = [
            'label' => 'Nama Ketua',
            'value' => $this->nipKetua . ' - ' . $this->namaKetua
        ];
        if (!empty($this->recordSavedAnggota)) {
            $this->reOrderRecordSavedAnggot();

            $noDosen = $noMahasiswa = 1;
            $jmlhDosen = count(array_filter($this->recordSavedAnggota, fn($anggota) => $anggota['memberType'] != PengajuanPendanaanAnggota::JENIS_MAHASISWA));
            $jmlhMahasiswa = count(array_filter($this->recordSavedAnggota, fn($anggota) => $anggota['memberType'] == PengajuanPendanaanAnggota::JENIS_MAHASISWA));
            foreach ($this->recordSavedAnggota as $anggota) {
                $jenis = ($anggota['memberType'] == PengajuanPendanaanAnggota::JENIS_MAHASISWA)
                    ? ('Mahasiswa ' . ($jmlhMahasiswa > 1 ? $noMahasiswa++ : ''))
                    : ('Dosen ' . ($jmlhDosen > 1 ? $noDosen++ : ''));
                $confirmAnggota[] = [
                    'label' => 'Nama Anggota ' . $jenis,
                    'value' => $anggota['name']
                ];
            }
        }

        $this->confirmData = [
            [
                'periode' => [
                    'label' => 'Periode Pendanaan',
                    'value' => $periodeOpt[$this->idPeriodePendanaan] ?? null,
                ],
                'judul' => [
                    'label' => 'Judul Proposal',
                    'value' => $this->record['judul_penelitian'] ?? null,
                ],
                'klaster' => [
                    'label' => 'Klaster Pendanaan',
                    'value' => $this->headerInfoKlaster['nama_klaster'] ?? null
                ],
            ],
            $confirmAnggota,
            [
                'nama_bank' => [
                    'label' => 'Nama Bank',
                    'value' => $this->record['nama_bank'] ?? null,
                ],
                'nomor_rekening' => [
                    'label' => 'Nomor Rekening',
                    'value' => $this->record['nomor_rekening'] ?? null,
                ],
                'cabang_bank' => [
                    'label' => 'Cabang Bank',
                    'value' => $this->record['cabang_bank'] ?? null,
                ],
                'nama_pemilik_rekening' => [
                    'label' => 'Nama Pemilik Rekening',
                    'value' => $this->record['nama_pemilik_rekening'] ?? null,
                ],
                'usulan_biaya' => [
                    'label' => 'Usulan Biaya',
                    'value' => "$usulanBiaya"
                ],
            ]
        ];
    }

    protected function defineFormFields($data = null)
    {
        $maxAnggaran = $this->maksimalAnggaranKlasterPendanaan ?? 0;
        $mataUang = $this->mataUangKlasterPendanaan ?? 'IDR';
        $convert = $mataUang !== config('money.defaults.currency');
        $humanReadableMaxAnggaran = money($maxAnggaran, $mataUang, $convert);
        $helperMaxAnggaran = " Maksimum Dana dari klaster yang Anda pilih: $humanReadableMaxAnggaran";
        $disabledAnggaran = empty($this->idKlasterPendanaan);

        $this->kodeJenisPendanaan = $this->kodeJenisPendanaan ?? $data['kode_jenis_pendanaan'] ?? $this->getKodeJenisPendanaan();
        $this->apakahAdaSumberPendanaan = !empty($this->idSumberPendanaan);
        $this->apakahAdaKlasterPendanaan = !empty($this->idKlasterPendanaan);

        $this->bidangIlmuOptions = $this->getBidangIlmuOptions(
            idKlasterPendanaan: $this->headerInfoKlaster['x_kid'],
            idSumberPendanaan: $this->headerInfoKlaster['x_kis'],
            kodeJenisPendanaan: $this->headerInfoKlaster['x_kjp']
        );

        $this->temaKegiatanOptions = $this->getTemaKegiatanOptions(
            idBidangIlmu: $this->idBidangIlmu,
            idKlasterPendanaan: $this->headerInfoKlaster['x_kid'],
            idSumberPendanaan: $this->headerInfoKlaster['x_kis'],
            kodeJenisPendanaan: $this->headerInfoKlaster['x_kjp']
        );

        // cek jika options kosong
        $isEmptyOptBidangIlmu = empty($this->bidangIlmuOptions);
        $isEmptyOptTemaKegiatan = empty($this->temaKegiatanOptions);

        // set utk informasi-umum
        if ($this->kodeJenisPendanaan === JenisPendanaanEnum::CODE_PENELITIAN) {
            $labelJudul = 'Judul Penelitian';
        } else {
            $labelJudul = 'Judul Pengabdian Masyarakat';
        }

        $fields = [
            'informasi-umum' => [
                'title' => 'Pernyataan Proposal',
                'icon' => 'document-magnifying-glass',
                'items' => [
                    [
                        'field' => 'kode_jenis_pendanaan',
                        'control' => 'radio',
                        'inline' => false,
                        'type' => 'hidden',
                        'selected' => $this->kodeJenisPendanaan,
                    ],
                    [
                        'field' => 'judul_penelitian',
                        'control' => 'textarea',
                        'label' => $labelJudul,
                        'placeholder' => 'Masukkan judul proposal yang akan Anda ajukan',
                    ],
                    [
                        'field' => 'id_bidang_ilmu',
                        'wire:model.change' => 'idBidangIlmu',
                        'control' => 'select',
                        'options' => $this->bidangIlmuOptions,
                        'disabled' => $isEmptyOptBidangIlmu,
                        'helper' => $isEmptyOptBidangIlmu
                            ? 'Tidak ada pilihan berdasarkan Jenis Pendanaan, Tema Kegiatan, Sumber Pendanaan, ataupun Klaster Pendanaan yang dipilih'
                            : null
                    ],
                    [
                        'field' => 'id_tema_kegiatan',
                        'wire:model.change' => 'idTemaKegiatan',
                        'control' => 'select',
                        'options' => $this->temaKegiatanOptions,
                        'disabled' => $isEmptyOptTemaKegiatan,
                        'helper' => $isEmptyOptTemaKegiatan
                            ? 'Tidak ada pilihan berdasarkan Jenis Pendanaan, Bidang Ilmu, Sumber Pendanaan, ataupun Klaster Pendanaan yang dipilih'
                            : null
                    ],
                    [
                        'field' => 'id_klaster_pendanaan',
                        'type' => 'hidden',
                        'wire:model' => 'idKlasterPendanaan',
                    ],
                    [
                        'field' => 'id_sumber_pendanaan',
                        'type' => 'hidden',
                        'wire:model' => 'idSumberPendanaan',
                    ],
                    [
                        'field' => 'id_periode_pendanaan',
                        'type' => 'hidden',
                        'wire:model' => 'idPeriodePendanaan',
                    ]
                ],
            ],
            'isian-proposal' => [
                'title' => 'Komponen Proposal',
                'icon' => 'document-text',
                'items' => [ // ada tambahan di method setIsianProposalItems
                    [
                        'field' => 'id_dokumen_proposal',
                        'label' => 'Upload Dokumen Proposal',
                        'required' => true,
                        'remove' => false,
                        'wire:change' => 'uploadDokumenProposal',
                        'wire:model' => 'idDokumenProposal'
                    ]
                ],
            ],
            'member' => [
                'title' => 'Data Peneliti',
                'icon' => 'users',
                'items' => [], // di set di method setAnggotaItems
            ],
            'rekening' => [
                'title' => 'Detail Pendanaan',
                'icon' => 'credit-card',
                'items' => [
                    [
                        'field' => 'nominal_anggaran_diajukan',
                        'label' => __('litabmas::pengajuan_pendanaan.nominal_anggaran_diajukan'),
                        'placeholder' => $disabledAnggaran
                            ? 'Pilih klaster pendanaan terlebih dahulu'
                            : 'Masukkan usulan biaya penelitian',
                        'required' => true,
                        'control' => 'text',
                        'format-currency' => true,
                        'disabled' => $disabledAnggaran,
                        'helper' => ($maxAnggaran > 0 && !$disabledAnggaran) ? $helperMaxAnggaran : null
                    ],
                    [
                        'field' => 'id_dokumen_rab',
                        'wire:change' => 'uploadDokumenRab',
                        'wire:model' => 'idDokumenRab',
                        'required' => true,
                        'remove' => false,
                        'label' => 'Upload file Rancangan Anggaran Biaya (RAB)'
                    ],
                    [
                        'field' => 'nama_pemilik_rekening',
                        'required' => true,
                        'label' => __('litabmas::pengajuan_pendanaan.nama_pemilik_rekening')
                    ],
                    [
                        'field' => 'nama_bank',
                        'required' => true,
                        'label' => __('litabmas::pengajuan_pendanaan.nama_bank')
                    ],
                    [
                        'field' => 'nomor_rekening',
                        'required' => true,
                        'type' => 'number',
                        'label' => __('litabmas::pengajuan_pendanaan.nomor_rekening')
                    ],
                    ['field' => 'cabang_bank', 'label' => __('litabmas::pengajuan_pendanaan.cabang_bank')],
                    [
                        'field' => 'id_foto_tabungan',
                        'label' => 'Upload foto halaman depan buku tabungan',
                        'type' => 'file',
                        'wire:change' => 'uploadFotoTabungan',
                        'max_size' => 1024 * 2,
                        'remove' => false,
                        'file_type' => ['jpg', 'jpeg', 'png'],
                        'wire:model' => 'idFotoTabungan'
                    ]
                ],
            ]
        ];

        // Ada tambahan 2 custom field yaitu jenis_output_penelitian dan apakah_berkontribusi_bidang_ilmu
        if (!empty($this->edit)) {
            $fields['informasi-umum']['items'][7] = [
                'field' => 'jenis_output_penelitian',
                'type' => 'hidden',
                'wire:model' => 'record.jenis_output_penelitian'
            ];
        }

        $fields = $this->setIsianProposalItems($fields);

        $fields = $this->setAnggotaItems($fields);

        return $fields;
    }

    public function uploadDokumenRab()
    {
        $this->isUploading = true;
    }

    public function updatedIdDokumenRab($value)
    {
        $this->idDokumenRab = $value;

        $this->isUploading = false;
    }

    public function uploadFotoTabungan()
    {
        $this->isUploading = true;
    }

    public function updatedIdFotoTabungan($value)
    {
        $this->idFotoTabungan = $value;

        $this->isUploading = false;
    }

    public function uploadDokumenProposal()
    {
        $this->isUploading = true;
    }

    public function updatedIdDokumenProposal($value)
    {
        $this->idDokumenProposal = $value;

        $this->isUploading = false;
    }

    protected function messageValidation()
    {
        return [
            'apakah_berkontribusi_bidang_ilmu.required' => 'Tentukan apakah penelitian berkontribusi pada pengembangan keilmuan di Prodi',
        ];
    }

    private function setIsianProposalItems($fields)
    {
        $isianProposal = $this->checkHasIsianProposal() ?? null;
        if (empty($isianProposal)) {
            $this->apakahAdaIsianProposal = false;

            return $fields;
        }

        // looping, add to items fields
        foreach ($isianProposal as $content) {
            $fields['isian-proposal']['items'][] = [
                'field' => 'isian_proposal__' . $this->kodeJenisPendanaan . '__' . $content->id,
                'label' => $content->nama_isian_proposal,
                'type' => 'hidden',
                'required' => true,
                'showLabel' => false,
                'showHelper' => false,
            ];
        }

        $this->apakahAdaIsianProposal = true;
        return $fields;
    }

    private function setAnggotaItems($fields)
    {
        $fields['member']['items'] = [
            [ // hanya utk view saja, di backend default berdasarkan yg create pakai id_biodata
                'field' => 'member_leader',
                'label' => 'Nama Ketua',
                'default' => $this->namaKetua,
                'placeholder' => $this->namaKetua,
                'disabled' => true,
                'required' => true,
                'helper' => 'Anda otomatis menjadi ketua penelitian karena Anda yang mengajukan proposal'
            ]
        ];

        return $fields;
    }

    #[Computed]
    private function getKodeJenisPendanaan()
    {
        $this->prevUrl = $this->prevUrl ?? url()->previous();
        $queryStringPrev = parse_url($this->prevUrl)['query'] ?? '';
        $paramsPrev = [];
        parse_str($queryStringPrev, $paramsPrev);

        // handle dari request query string
        $kodeJenisPendanaan = null;
        if ($this->isInitEdit) {
            // get full current url to get klaster_id

            $kodeJenisPendanaanParam = $this->headerInfoKlaster['x_kjp'];
            $idSumberPendanaanParam = $this->headerInfoKlaster['x_kis'] ?? null;
            $idKlasterPendanaanParam = $this->headerInfoKlaster['x_kid'] ?? null;

            // set ke property jika semua param ada
            if (
                filter_var($idKlasterPendanaanParam, FILTER_VALIDATE_INT) &&
                !empty($idKlasterPendanaanParam) &&
                filter_var($idSumberPendanaanParam, FILTER_VALIDATE_INT) &&
                !empty($idSumberPendanaanParam) &&
                !empty($kodeJenisPendanaanParam)
            ) {
                $kodeJenisPendanaan = $kodeJenisPendanaanParam;

                $this->idSumberPendanaan = $idSumberPendanaanParam;
                $this->record['id_sumber_pendanaan'] = $idSumberPendanaanParam;
                $this->apakahAdaSumberPendanaan = true;

                $this->idKlasterPendanaan = $idKlasterPendanaanParam;
                $this->record['id_klaster_pendanaan'] = $idKlasterPendanaanParam;
                $this->apakahAdaKlasterPendanaan = true;
            }
        }

        // jika kode jenis pendanaan dari paramscurrent kosong, maka ambil dari paramsprev
        if (empty($kodeJenisPendanaan)) {
            $kodeJenisPendanaan = $paramsPrev['tab'] ?? JenisPendanaanEnum::CODE_PENELITIAN;
        }

        return $kodeJenisPendanaan;
    }

    private function checkHasIsianProposal()
    {
        if (empty($this->kodeJenisPendanaan) || empty($this->idPeriodePendanaan)) {
            return null;
        }

        $result = $this->aspekPenilaianIsianProposalService
            ->getByJenisPendanaanDanPeriodePendanaan($this->kodeJenisPendanaan, $this->idPeriodePendanaan);
        return $result->isEmpty() ? null : $result;
    }

    #[Computed]
    private function getSumberPendanaanOptions(int $idBidangIlmu = null, int $idTemaKegiatan = null, int $idKlasterPendanaan = null, string $kodeJenisPendanaan = null)
    {
        // jika kosong semua berarti user belum dependensi apapun, jadi tampilin aja semua yg aktif
        if (empty($idBidangIlmu) && empty($idTemaKegiatan) && empty($idKlasterPendanaan) && empty($kodeJenisPendanaan)) {
            return SumberPendanaan::sumberPendanaanOptions($this->idPeriodePendanaan ?? null);
        }

        return SumberPendanaan::optionDynamic(
            idBidangIlmu: $idBidangIlmu,
            idTemaKegiatan: $idTemaKegiatan,
            idKlasterPendanaan: $idKlasterPendanaan,
            kodeJenisPendanaan: $kodeJenisPendanaan,
            idPeriodePendanaan: $this->idPeriodePendanaan,
            idAgendaKegiatanPendaftaran: $this->idAgendaKegiatanPendaftaran
        );
    }

    #[Computed]
    private function getKlasterPendanaanOptions(int $idBidangIlmu = null, int $idTemaKegiatan = null, int $idSumberPendanaan = null, string $kodeJenisPendanaan = null)
    {
        // jika kosong semua berarti user belum dependensi apapun, jadi tampilin aja semua yg aktif
        if (empty($idBidangIlmu) && empty($idTemaKegiatan) && empty($idSumberPendanaan) && empty($kodeJenisPendanaan)) {
            return KlasterPendanaan::getKlasterPendanaanAktifOptions($this->idPeriodePendanaan ?? null);
        }

        return KlasterPendanaan::optionDynamic(
            idBidangIlmu: $idBidangIlmu,
            idTemaKegiatan: $idTemaKegiatan,
            idSumberPendanaan: $idSumberPendanaan,
            kodeJenisPendanaan: $kodeJenisPendanaan,
            idPeriodePendanaan: $this->idPeriodePendanaan,
            idAgendaKegiatanPendaftaran: $this->idAgendaKegiatanPendaftaran
        );
    }

    #[Computed]
    private function getBidangIlmuOptions(int $idTemaKegiatan = null, int $idKlasterPendanaan = null, int $idSumberPendanaan = null, string $kodeJenisPendanaan = null)
    {
        // jika kosong semua berarti user belum dependensi apapun, jadi tampilin aja semuanya
        if (empty($idTemaKegiatan) && empty($idSumberPendanaan) && empty($idKlasterPendanaan) && empty($kodeJenisPendanaan)) {
            return BidangIlmu::options();
        }

        return BidangIlmu::optionDynamic(
            idTemaKegiatan: $idTemaKegiatan,
            idKlasterPendanaan: $idKlasterPendanaan,
            idSumberPendanaan: $idSumberPendanaan,
            kodeJenisPendanaan: $kodeJenisPendanaan,
            idAgendaKegiatanPendaftaran: $this->idAgendaKegiatanPendaftaran
        );
    }

    #[Computed]
    private function getTemaKegiatanOptions(int $idBidangIlmu = null, int $idKlasterPendanaan = null, int $idSumberPendanaan = null, string $kodeJenisPendanaan = null)
    {
        // jika kosong semua berarti user belum dependensi apapun, jadi tampilin aja semuanya
        if (empty($idBidangIlmu) && empty($idSumberPendanaan) && empty($idKlasterPendanaan) && empty($kodeJenisPendanaan)) {
            return TemaKegiatan::options();
        }

        return TemaKegiatan::optionDynamic(
            idBidangIlmu: $idBidangIlmu,
            idKlasterPendanaan: $idKlasterPendanaan,
            idSumberPendanaan: $idSumberPendanaan,
            kodeJenisPendanaan: $kodeJenisPendanaan,
            idAgendaKegiatanPendaftaran: $this->idAgendaKegiatanPendaftaran
        );
    }
}
