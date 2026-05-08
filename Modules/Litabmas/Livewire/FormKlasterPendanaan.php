<?php

namespace Modules\Litabmas\Livewire;

use Livewire\Attributes\On;

use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\WithFileUploads;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Livewire\CreateEditComponent;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Page;
use Modules\Litabmas\Models\AgendaKegiatan;
use Modules\Litabmas\Models\TemaKegiatan;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Models\SumberPendanaan;
use Modules\Litabmas\Models\BidangIlmu;
use Modules\Litabmas\Services\KlasterPendanaanService;
use Modules\Litabmas\Services\SumberPendanaanService;
use Modules\Litabmas\Services\JenisOutcomePenelitianService;
use Modules\Litabmas\Services\JenisOutputPenelitianService;
use Modules\Core\Helpers\Relation;
use Modules\Core\Helpers\Cstr;
use Modules\Litabmas\Models\JenisOutcomePenelitian;
use Modules\Litabmas\Models\PengajuanPendanaan;

class FormKlasterPendanaan extends CreateEditComponent
{
    use LitabmasViewData, WithFileUploads;

    // protected yg dari CreateEditComponent
    protected array $customViewData = [
        'showCollapseInSection' => true
    ];

    // protected service
    protected SumberPendanaanService $sumberPendanaanService;
    protected JenisOutputPenelitianService $outputService;
    protected JenisOutcomePenelitianService $outcomeService;

    // custom properties public
    public $pivotKlasterOutcome = null;
    public $pivotKlasterOutput = null;
    public $pivotKlasterAgenda = null;

    public $klasterPendanaan = null;
    public $idPeriodePendanaan = null;
    public $idSumberPendanaan = null;
    public $kategoriKlaster = null;
    public $isApproveAll = null;
    public $apakahBisaMultiAjuan = null;
    public array $state = [
        'addBidangIlmuDanTemaKegiatan' => false,
        'editBidangIlmuDanTemaKegiatan' => false,
        'onEditIdBidangIlmu' => null
    ];
    public $mataUangSumberPendanaan = null;
    public $totalPendanaanSumberPendanaan = null;
    public array $alertBidangIlmuTemaKegiatan = [];
    public array $alertOutputOutcome = [];
    public string|null $selectedOlderOutcome = null;
    public array $alertAgendaKegiatan = [];
    public $recordSavedBidangIlmuTemaKegiatan = []; // utk nampung record bidang ilmu and tema kegiatan
    public $usedBidangIlmuTemaKegiatan = []; // utk nampung record bidang ilmu and tema kegiatan

    public $temaKegiatanOptions;
    public $bidangIlmuOptions;
    public $minAnggotaOptions;
    public $maxAnggotaOptions;
    public $maxSubmissionOptions;

    public $totalPendanaanSumber = 0;
    public $danaDigunakan = 0;
    public $sisaAnggaran = 0;
    public $idDokumenTemplateRab;
    public $statusPublikasi;
    public $isEmptyPeriode = false;
    public $listPeriode = [];
    public $listSumberPendanaan = [];
    public $steps = 1;
    public $isDisableEdit = false;

    private PeriodePendanaan|null $periodeAktif = null;
    private $oldRecordOutput = [];
    private $oldRecordOutcome = [];
    private $oldRecordAgendaKegiatan = [];
    private int|null $idAgendaKegiatanStepOutcome = null;
    private bool $isUpdatedSumberPendanaan = false;
    public $recordTglAkhirPublikasi = null;

    #[Renderless]
    public function loadService()
    {
        $this->service = new KlasterPendanaanService;
        $this->sumberPendanaanService = new SumberPendanaanService;
        $this->outputService = new JenisOutputPenelitianService;
        $this->outcomeService = new JenisOutcomePenelitianService;
    }

    #[Renderless]
    public function loadModel()
    {
        $this->model = KlasterPendanaan::class;
    }

    /**
     * Dipanggil sekali ketika component pertama kali terbuat (setelah boot tapi hanya sekali).
     *
     * @return void
     */
    public function mount()
    {
        parent::mount();

        $periodeAktif = (new PeriodePendanaan)->periodeAktif();

        $this->temaKegiatanOptions = TemaKegiatan::options();
        $this->bidangIlmuOptions = BidangIlmu::options();
        $this->minAnggotaOptions = KlasterPendanaan::minMemberOptions();
        $this->maxAnggotaOptions = KlasterPendanaan::maxMemberOptions();
        $this->maxSubmissionOptions = KlasterPendanaan::maxSubmissionOptions();

        if ($this->edit) {
            $this->klasterPendanaan = $this->getKlasterPendanaan();
            $this->kategoriKlaster = $this->klasterPendanaan->kategori_klaster;
            $this->apakahBisaMultiAjuan = $this->klasterPendanaan->apakah_bisa_multi_ajuan;
        } else {
            if (!$periodeAktif) {
                $this->isEmptyPeriode = true;
            } else {
                $this->idPeriodePendanaan = $periodeAktif->id;
            }
        }

        $this->periodeAktif = $periodeAktif ?? null;
        $this->pivotKlasterOutput = $this->outputService->getListCache();
        $this->pivotKlasterOutcome = $this->outcomeService->getListCache();

        if ($this->edit) {
            $this->isDisableEdit = PengajuanPendanaan::where('id_klaster_pendanaan', $this->edit)->exists();
            $this->updatedIdPeriodePendanaan($this->klasterPendanaan->sumberPendanaan->id_periode_pendanaan);
            $this->updatedIdSumberPendanaan($this->klasterPendanaan->id_sumber_pendanaan);
        }

        $this->listPeriode = PeriodePendanaan::options();
        $this->listPeriode[$periodeAktif?->id] = $periodeAktif?->tahun . ' (Periode Aktif)';

        $this->listSumberPendanaan = SumberPendanaan::sumberPendanaanOptions($this->idPeriodePendanaan);

        $this->renderDateEndOutcome();

        if ($this->edit && empty($this->recordSavedBidangIlmuTemaKegiatan)) {
            $bidangIlmuTemaMapping = $this->service->getBidangIlmuDanTemaByIdKlasterPendanaan($this->edit)
                ->groupBy('id_bidang_ilmu');
            foreach ($bidangIlmuTemaMapping as $idBidangIlmu => $temaKegiatans) {
                $this->recordSavedBidangIlmuTemaKegiatan[$idBidangIlmu] = $temaKegiatans->pluck('id_tema_kegiatan')->toArray();
                foreach ($temaKegiatans as $tema) {
                    if ($tema->total_pengajuan > 0) {
                        $this->usedBidangIlmuTemaKegiatan[$idBidangIlmu][$tema->id_tema_kegiatan] = true;
                    }
                }
            }
        }
    }

    private function renderDateEndOutcome()
    {
        if (!empty($this->idPeriodePendanaan)) {
            $periodePendanaan = PeriodePendanaan::find($this->idPeriodePendanaan);
            foreach ($this->pivotKlasterOutcome as $outcome) {
                $outcome->format_batas_pengumpulan_outcome = null;
                $startDatePeriod = Carbon::parse($periodePendanaan->tanggal_mulai);
                $formatedCollectionLimit = $startDatePeriod->addYears((int) $outcome->batas_pengumpulan_outcome);
                $outcome->format_batas_pengumpulan_outcome = $formatedCollectionLimit
                    ->translatedFormat('d F Y') . ' (' . $outcome->batas_pengumpulan_outcome . ' tahun)'; // ex: 12 November 2026 (2 tahun)
                $outcome->penambahan_batas_pengumpulan_outcome = $formatedCollectionLimit->format('Y-m-d'); // ex: 2026-11-12
            }
            $listUpdated = [];
            foreach ($this->pivotKlasterOutcome as $outcome) {
                $listUpdated[$outcome->id] = $outcome->format_batas_pengumpulan_outcome;
            }
            $this->dispatch('update-date-end-outcome', $listUpdated);
        }
    }

    public function beforeRender()
    {
        if (!isset($this->idSumberPendanaan)) {
            $this->idSumberPendanaan = $this->selectValue($this->record['id_sumber_pendanaan'] ?? null);
        }

        // handle ketika ganti sumber pendanaan maka reset max anggaran (kepentingan UX)
        if ($this->isUpdatedSumberPendanaan && !empty($this->record['maksimal_anggaran'])) {
            $this->record['maksimal_anggaran'] = null;
        }

        // dari yg dipilih user record['jenis_outcome_penelitian'] cari yg tertinggi penambahan_batas_pengumpulan_outcome
        $this->selectedOlderOutcome = null;
        if (!empty($this->record['jenis_outcome_penelitian'])) {
            foreach ($this->record['jenis_outcome_penelitian'] as $key => $value) {
                if ($value) {
                    $outcome = $this->pivotKlasterOutcome->where('id', $key)->first();
                    if ($outcome) {
                        if (empty($this->selectedOlderOutcome)) {
                            $this->selectedOlderOutcome = $outcome->penambahan_batas_pengumpulan_outcome;
                        } else {
                            // format to carbon
                            $selectedOlderOutcome = Carbon::parse($this->selectedOlderOutcome);
                            $outcomeFormat = Carbon::parse($outcome->penambahan_batas_pengumpulan_outcome);
                            if ($selectedOlderOutcome->lessThan($outcomeFormat)) {
                                $this->selectedOlderOutcome = $outcome->penambahan_batas_pengumpulan_outcome;
                            }
                        }
                    }
                }
            }
        }

        // mempertahankan daftar agenda kegiatan ketika memiliki sumber pendanaan yang dipilih dan sedang tidak edit data
        if (!empty($this->idSumberPendanaan) && empty($this->edit)) {
            $this->pivotKlasterAgenda = $this->getAktifAgendaKegiatanByIdSumberPendanaan($this->idSumberPendanaan);
        }

        // set otomatis value waktu_selesai dari agenda outcome (khususon)
        if (!empty($this->pivotKlasterAgenda)) {
            foreach ($this->pivotKlasterAgenda as $agenda) {
                if ($agenda->kode_agenda == AgendaKegiatan::STEP_PENGUMPULAN_HASIL) {
                    if (!empty($this->recordTglAkhirPublikasi)) {
                        $this->record['agenda_kegiatan'][$agenda->id_agenda_kegiatan]['waktu_selesai'] = $this->recordTglAkhirPublikasi;
                    }
                }
            }
        }
    }

    #[Computed]
    private function getKlasterPendanaan()
    {
        return $this->service->show($this->edit);
    }

    #[Computed]
    private function getTotalPendanaanDanMataUang($idSumberPendanaan)
    {
        return $this->sumberPendanaanService->getTotalPendanaanDanMataUang($idSumberPendanaan);
    }

    #[Computed]
    private function getAktifAgendaKegiatanByIdSumberPendanaan($idSumberPendanaan)
    {
        return $this->sumberPendanaanService->getAktifAgendaKegiatanByIdSumberPendanaan($idSumberPendanaan);
    }

    #[Computed]
    private function getAktifAgendaKegiatanByIdKlasterPendanaan()
    {
        return $this->service->getAktifAgendaKegiatanByIdKlasterPendanaan($this->edit);
    }

    public function updatedIdSumberPendanaan($value)
    {
        $value = $this->selectValue($value);
        $this->idSumberPendanaan = !empty($value) ? $value : null;
        $this->isUpdatedSumberPendanaan = true;
        $this->record['maksimal_anggaran'] = null;

        if (!empty($this->idSumberPendanaan)) {
            $this->pivotKlasterAgenda = $this->getAktifAgendaKegiatanByIdSumberPendanaan($this->idSumberPendanaan);
            $sumberPendanaan = $this->getTotalPendanaanDanMataUang($this->idSumberPendanaan);
            $this->mataUangSumberPendanaan = $sumberPendanaan->mata_uang;
            $this->totalPendanaanSumberPendanaan = $sumberPendanaan->sisa_anggaran ?? $sumberPendanaan->total_pendanaan;
            if ($this->edit) {
                $this->totalPendanaanSumberPendanaan = $sumberPendanaan->total_pendanaan;
            }
        } else {
            $this->pivotKlasterAgenda = [];
            $this->mataUangSumberPendanaan = $this->totalPendanaanSumberPendanaan = null;
        }
    }

    public function updatedIdPeriodePendanaan($value)
    {
        $this->idPeriodePendanaan = $this->selectValue($value);

        $this->listSumberPendanaan = SumberPendanaan::sumberPendanaanOptions($this->idPeriodePendanaan);

        $this->updatedIdSumberPendanaan(null);

        $this->renderDateEndOutcome();
    }

    public function updatedKategoriKlaster($value)
    {
        $value = $this->selectValue($value);
        $this->kategoriKlaster = !empty($value) ? $value : null;
    }

    public function updatedIsApproveAll($value)
    {
        $value = $this->selectValue($value);
        $this->isApproveAll = $value;
    }

    public function updatedApakahBisaMultiAjuan($value)
    {
        $value = $this->selectValue($value);
        $this->apakahBisaMultiAjuan = !is_null($value) ? $value : null;
    }

    /**
     * Digunakan ketika membutuhkan custom validasi.
     * Method ini jalan sebelum validasi model WebRequest::validateData() yg ada di dalam proses save()
     *
     * @return bool
     */
    public function customValidationBeforeSave(): bool
    {
        $isError = false;
        $sectionError = null;

        // cek id sumber pendanaan
        if (empty($this->idSumberPendanaan)) {
            return false;
        }

        //if edit
        if ($this->edit) {
            $errorArray = $this->editCustomValidationBeforeSave();
            if ($errorArray['isError']) {
                $isError = true;
                $sectionError = $errorArray['sectionError'];
                return false;
            }
        }

        if ($this->record['maksimal_anggaran']) {
            $recordAnggaran = floatval(str_replace('.', '', $this->record['maksimal_anggaran']));
            $totalAnggaran = floatval($this->totalPendanaanSumberPendanaan);
            $mataUang = $this->mataUangSumberPendanaan ?? 'IDR';

            if ($totalAnggaran <= 0) {
                $this->addError('maksimal_anggaran', "Anggaran sumber pendanaan sudah habis atau tidak tersedia.");
                $isError = true;
                $sectionError = 'informasi-umum';
            } elseif ($recordAnggaran > $totalAnggaran) {
                $this->addError('maksimal_anggaran', "Batas pengajuan dana tidak boleh lebih dari total pendanaan sumber pendanaan " . $mataUang . " " . number_format($totalAnggaran, 0, ',', '.'));
                $isError = true;
                $sectionError = 'informasi-umum';
            }
        }

        $this->alertBidangIlmuTemaKegiatan = $this->alertOutputOutcome = $this->alertAgendaKegiatan = [];

        $validasiKelompok = $this->validasiKategoriKelompok();
        if ($validasiKelompok['isError']) {
            $isError = true;
            $sectionError = $validasiKelompok['sectionError'];
        }

        if (!empty($this->idDokumenTemplateRab)) {
            $this->mergeData['id_dokumen_template_rab'] = $this->idDokumenTemplateRab;
        }

        if ($this->edit) {
            $checkFileRab = $this->idDokumenTemplateRab ?? $this->klasterPendanaan->id_dokumen_template_rab;
        } else {
            $checkFileRab = $this->idDokumenTemplateRab;
        }

        // cek harus upload file rab
        if (empty($checkFileRab)) {
            $isError = true;
            $sectionError ??= 'informasi-umum';
            $this->addError(
                'id_dokumen_template_rab',
                __('validation.required', ['attribute' => 'File Template Rancangan Anggaran Biaya (RAB)'])
            );
        }

        // validasi ketika tidak ada bidang ilmu & tema
        if (empty($this->recordSavedBidangIlmuTemaKegiatan)) {
            $sectionError = 'bidang-ilmu-tema';
            $this->alertBidangIlmuTemaKegiatan = [
                'type' => 'error',
                'message' => 'Bidang Ilmu & Tema minimal harus ada 1'
            ];
            $isError = true;
        }

        // validasi ketika tidak ada output & outcome
        // jika isi dari record jenis_output_penelitian valuenya false semua atau jika isi dari recrod jenis_outcome_penelitian valuenya false semua
        $isEmptyJenisOutputPenelitian = array_filter($this->record['jenis_output_penelitian'] ?? [], fn($item) => $item === true);
        $isEmptyJenisOutcomePenelitian = array_filter($this->record['jenis_outcome_penelitian'] ?? [], fn($item) => $item === true);
        if (empty($isEmptyJenisOutputPenelitian) || empty($isEmptyJenisOutcomePenelitian)) {
            $sectionError ??= 'output-outcome';
            $this->alertOutputOutcome = [
                'type' => 'error',
                'message' => 'Output & Outcome minimal harus memilih satu'
            ];
            $isError = true;
        }

        $validasiAgenda = $this->validasiAgendaKegiatan();
        if ($validasiAgenda['isError']) {
            $isError = true;
            $sectionError = $validasiAgenda['sectionError'];
        }

        if ($isError) {
            $this->loadData();
            $this->dispatch('scroll-to-section', $sectionError);
            $this->dispatch('hide-loading');
            return false;
        }

        $this->mergeData['apakah_sudah_publikasi'] = true;

        // masukkan recordSavedBidangIlmuTemaKegiatan ke mergeData
        $this->mergeData['_bidang_ilmu_dan_tema_kegiatan'] = $this->recordSavedBidangIlmuTemaKegiatan;

        // masukkan agenda kegiatan ke mergeData
        $this->mergeData['agenda_kegiatan'] = $this->record['agenda_kegiatan'];

        return true;
    }

    private function validasiAgendaKegiatan()
    {
        $isError = false;
        $sectionError = null;
        // [Start] Validasi Tahapan Kegiatan
        $isHasEmptyAgenda = false;
        $listAgendaKegiatan = (new AgendaKegiatan())->getListCache();
        $onlyFirstDayAgendas = AgendaKegiatan::ONLY_FIRST_DAY_STEPS;
        $onlyLastDayAgendas = AgendaKegiatan::ONLY_LAST_DAY_STEPS;

        // ubah key singleStepAgenda menjadi id dari $listAgendaKegiatan, cocokan berdasarkan code $listAgendaKegiatan dan vallue dari $singleStepAgenda
        $refactorFirstDayAgenda = [];
        foreach ($onlyFirstDayAgendas as $code) {
            $agenda = $listAgendaKegiatan->where('kode_agenda', $code)->first();
            if ($agenda) {
                $refactorFirstDayAgenda[$agenda->id] = $agenda->kode_agenda;
            }
        }

        $refactorLastDayAgenda = [];
        foreach ($onlyLastDayAgendas as $code) {
            $agenda = $listAgendaKegiatan->where('kode_agenda', $code)->first();
            if ($agenda) {
                $refactorLastDayAgenda[$agenda->id] = $agenda->kode_agenda;
            }
        }

        $this->pivotKlasterAgenda = $this->getAktifAgendaKegiatanByIdSumberPendanaan($this->idSumberPendanaan);

        // validasi apakah tanggal agenda ada yg kosong (sesuai kondisi single step atau tidak)
        $this->renderDateEndOutcome();

        // dari yg dipilih user record['jenis_outcome_penelitian'] cari yg tertinggi penambahan_batas_pengumpulan_outcome
        $this->selectedOlderOutcome = null;
        if (!empty($this->record['jenis_outcome_penelitian'])) {
            foreach ($this->record['jenis_outcome_penelitian'] as $key => $value) {
                if ($value) {
                    $outcome = $this->pivotKlasterOutcome->where('id', $key)->first();
                    if ($outcome) {
                        if (empty($this->selectedOlderOutcome)) {
                            $this->selectedOlderOutcome = $outcome->penambahan_batas_pengumpulan_outcome;
                        } else {
                            // format to carbon
                            $selectedOlderOutcome = Carbon::parse($this->selectedOlderOutcome);
                            $outcomeFormat = Carbon::parse($outcome->penambahan_batas_pengumpulan_outcome);
                            if ($selectedOlderOutcome->lessThan($outcomeFormat)) {
                                $this->selectedOlderOutcome = $outcome->penambahan_batas_pengumpulan_outcome;
                            }
                        }
                    }
                }
            }
        }

        // Ambil key paling besar dari COLLECTION_LIMIT_OPTIONS
        $maxBatasPublikasi = max(array_keys(JenisOutcomePenelitian::COLLECTION_LIMIT_OPTIONS));
        $periodePendanaan = PeriodePendanaan::find($this->idPeriodePendanaan)['tanggal_mulai'];

        $maxBatasTanggal = Carbon::parse($periodePendanaan)
            ->addYears($maxBatasPublikasi)
            ->format('Y-m-d');

        // set otomatis value waktu_selesai dari agenda outcome (khususon)

        if (!empty($this->pivotKlasterAgenda)) {
            foreach ($this->pivotKlasterAgenda as $agenda) {
                if ($agenda->kode_agenda == AgendaKegiatan::STEP_PENGUMPULAN_HASIL) {
                    if (!empty($this->recordTglAkhirPublikasi)) {
                        $this->record['agenda_kegiatan'][$agenda->id_agenda_kegiatan]['waktu_selesai'] = $this->recordTglAkhirPublikasi;
                    }

                    if ($this->record['agenda_kegiatan'][$agenda->id_agenda_kegiatan]['waktu_selesai'] > $maxBatasTanggal) {
                        $isError = true;
                        $detailError[$agenda->id_agenda_kegiatan] = 'Tanggal akhir pengumpulan publikasi tidak boleh melebihi batas ' . $maxBatasPublikasi . ' tahun.';
                        $this->alertAgendaKegiatan = [
                            'type' => 'error',
                            'message' => 'Terjadi kesalahan pada pemilihan tanggal.',
                            'detailError' => $detailError
                        ];
                        break;
                    }
                }
            }
        }

        // validasi apakah tanggal agenda ada yg kosong (sesuai kondisi single step atau tidak)
        foreach ($this->record['agenda_kegiatan'] as $id => $agenda) {
            // check jika id tidak ada di record agenda_kegiatan, maka skip (artinya tidak dipilih di sumber pendanaan)
            if (!array_key_exists($id, $this->record['agenda_kegiatan'])) {
                continue;
            }

            // khusus outcome ngambil dari selected agar tidak tercekal validasi dibawah
            if (!empty($this->idAgendaKegiatanStepOutcome) && $id == $this->idAgendaKegiatanStepOutcome) {
                $agenda['waktu_selesai'] = $this->selectedOlderOutcome;
                $this->record['agenda_kegiatan'][$id]['waktu_selesai'] = $this->selectedOlderOutcome;
            }

            // jika single step hanya cek waktu_mulai saja, jika tidak maka cek waktu_mulai dan waktu_selesai
            $isFirstDay = array_key_exists($id, $refactorFirstDayAgenda);
            $isLastDay = array_key_exists($id, $refactorLastDayAgenda);
            if ($isFirstDay) { // jika dia first day tapi waktu_mulai nya kosong, maka error
                if (empty($agenda['waktu_mulai'])) {
                    $sectionError ??= 'agenda-kegiatan';
                    $isError = true;
                    break;
                }
            } elseif ($isLastDay) { // jika dia last day tapi waktu_selesai nya kosong, maka error
                if (empty($agenda['waktu_selesai'])) {
                    $sectionError ??= 'agenda-kegiatan';
                    $isError = true;
                    break;
                }
            } else { // jika bukan first day dan last day, cek waktu_mulai dan waktu_selesai
                if (empty($agenda['waktu_mulai']) || empty($agenda['waktu_selesai'])) {
                    $sectionError ??= 'agenda-kegiatan';
                    $isError = true;
                    break;
                }
            }
        }

        if ($sectionError === 'agenda-kegiatan') { // jika hanya terkena error di section agenda-kegiatan saja.
            $this->alertAgendaKegiatan = [
                'type' => 'error',
                'message' => 'Tahapan Kegiatan tidak boleh ada yang kosong'
            ];
            $isHasEmptyAgenda = true;
        }

        // validasi tanggal agenda kegiatan masing-masing tidak boleh berpotongan
        if (!$isHasEmptyAgenda) { // jika tidak ada agenda kegiatan yang kosong
            // validasi tanggal berpotongan
            $isErrorIntersection = $this->validateDateAgendaKegiatanIntersection($listAgendaKegiatan);
            if ($isErrorIntersection['isError']) {
                $sectionError ??= 'agenda-kegiatan';
                $this->alertAgendaKegiatan = [
                    'type' => 'error',
                    'message' => $isErrorIntersection['message'],
                    'detailError' => $isErrorIntersection['detailError']
                ];
                $isError = true;
            }
        }
        // [End] Validasi Tahapan Kegiatan

        return ['isError' => $isError, 'sectionError' => $sectionError];
    }

    private function validasiKategoriKelompok()
    {
        $isError = false;
        $sectionError = null;
        // validasi jika kategori kelompok
        if ($this->kategoriKlaster === KlasterPendanaan::KATEGORI_KELOMPOK) {
            $this->record['minimal_anggota'] = $this->selectValue($this->record['minimal_anggota']);
            $this->record['maksimal_anggota'] = $this->selectValue($this->record['maksimal_anggota']);

            if (empty($this->record['minimal_anggota'])) {
                $this->addError('minimal_anggota', __('validation.required', [
                    'attribute' => __('litabmas::klaster_pendanaan.minimal_anggota')
                ]));
                $isError = true;
            }

            if (empty($this->record['maksimal_anggota'])) {
                $this->addError('maksimal_anggota', __('validation.required', [
                    'attribute' => __('litabmas::klaster_pendanaan.maksimal_anggota')
                ]));
                $isError = true;
            }

            if ($this->record['maksimal_anggota'] < $this->record['minimal_anggota']) {
                $this->addError('maksimal_anggota', 'Maksimal Anggota harus lebih besar dari Minimal Anggota.');
                $isError = true;
            }

            if ($isError) {
                $sectionError = 'informasi-umum';
            }
        }

        return ['isError' => $isError ?? false, 'sectionError' => $sectionError ?? null];
    }

    private function editCustomValidationBeforeSave()
    {
        // get data output
        $checkedOutput = $this->service->getOutputWajibByIdKlasterPendanaan($this->edit)
            ->groupBy('id_jenis_output_penelitian')->toArray();
        foreach ($this->pivotKlasterOutput as $output) {
            $isset = isset($checkedOutput[$output->id]);
            $output->is_checked = $isset; // set value is_checked
            $this->oldRecordOutput[$output->id] = $isset; // set old record output
        }

        // get data outcome
        $checkedOutcome = $this->service->getOutcomeWajibByIdKlasterPendanaan($this->edit)
            ->groupBy('id_jenis_outcome_penelitian')->toArray();
        foreach ($this->pivotKlasterOutcome as $outcome) {
            $isset = isset($checkedOutcome[$outcome->id]);
            $outcome->is_checked = $isset; // set value is_checked
            $this->oldRecordOutcome[$outcome->id] = $isset; // set old record outcome
        }

        // get old record
        $oldRecord = $this->klasterPendanaan;

        // Check if any of the specified fields have changed
        $fieldsToCheck = [
            'kode_jenis_pendanaan',
            'kategori_klaster',
            'id_sumber_pendanaan',
            'maksimal_anggota',
            'minimal_anggota',
        ];

        //format
        if (!empty($oldRecord['maksimal_anggaran'])) {
            $oldRecord['maksimal_anggaran'] = floatval($oldRecord['maksimal_anggaran']);
            $this->record['maksimal_anggaran'] = floatval(str_replace('.', '', $this->record['maksimal_anggaran']));
        }

        $hasChanges = Cstr::isArrayDifferent($oldRecord->toArray(), $this->record, $fieldsToCheck);

        //compare checked output and outcome
        foreach ($this->record['jenis_output_penelitian'] as $output => $statusOutput) {
            if ($this->oldRecordOutput[$output] != $statusOutput) {
                $hasChanges = true;
                break;
            }
        }

        foreach ($this->record['jenis_outcome_penelitian'] as $outcome => $statusOutcome) {
            if ($this->oldRecordOutcome[$outcome] != $statusOutcome) {
                $hasChanges = true;
                break;
            }
        }

        // cek sumber pendanaan
        if (is_array($this->record['id_periode_pendanaan'])) {
            $sumberCheck = SumberPendanaan::where([
                'id_periode_pendanaan' => array_values($this->record['id_periode_pendanaan'])[0],
                'id' => $this->record['id_sumber_pendanaan']
            ])->first();

            if (!$sumberCheck) {
                $this->addError('id_sumber_pendanaan', 'Sumber Pendanaan wajib dipilih');
                return ['isError' => true, 'sectionError' => 'informasi-umum'];
            }
        }

        if ($hasChanges) {

            $relationsForCheck = ['pengajuanPendanaan'];

            // Check if data has relation
            $hasRelations = Relation::hasRelationsData($oldRecord, $relationsForCheck);
            if (!empty($hasRelations['status'])) {
                $relation = __('litabmas::klaster_pendanaan.' . $hasRelations['relation']);
                $this->alert = [
                    'type' => 'error',
                    'message' => "Klaster Pendanaan tidak bisa diubah karena sudah memiliki data $relation",
                ];
                $this->dispatch('hide-loading');
                return ['isError' => true, 'sectionError' => 'informasi-umum'];
            }
        }

        //validasi maksimal anggaran tidak boleh dari totalPendanaanSumberPendanaan
        $CheckAnggaran = true;
        if ($this->edit && $this->record['maksimal_anggaran'] == $oldRecord['maksimal_anggaran']) {
            $CheckAnggaran = false;
        }


        //jika check anggaran true
        if ($CheckAnggaran) {
            $maxAnggaran = $this->totalPendanaanSumberPendanaan;
            $recordMaxAnggaran = floatval($this->record['maksimal_anggaran']);
            $mataUang = $this->mataUangSumberPendanaan ?? 'IDR';

            if ($maxAnggaran <= 0) {
                $this->addError('maksimal_anggaran', "Anggaran sumber pendanaan sudah habis atau tidak tersedia.");
                return ['isError' => true, 'sectionError' => 'informasi-umum'];
            } elseif ($recordMaxAnggaran > $maxAnggaran) {
                $this->addError('maksimal_anggaran', "Batas pengajuan dana tidak boleh lebih dari total pendanaan sumber pendanaan " . $mataUang . " " . number_format($maxAnggaran, 0, ',', '.'));
                return ['isError' => true, 'sectionError' => 'informasi-umum'];
            }
        }

        return ['isError' => false, 'sectionError' => null];
    }

    public function stateAddBidangIlmuDanTemaKegiatan()
    {
        $this->state['addBidangIlmuDanTemaKegiatan'] = true;
        $this->state['editBidangIlmuDanTemaKegiatan'] = false;
        $this->state['onEditIdBidangIlmu'] = null;
        $this->record['bidang_ilmu'] = null;
        $this->record['tema_kegiatan'] = null;
    }

    public function stateBackBidangIlmuDanTemaKegiatan($state)
    {
        $this->state['onEditIdBidangIlmu'] = null;

        if ($state === 'edit') {
            $this->state['editBidangIlmuDanTemaKegiatan'] = false;
            return;
        }

        $this->state['addBidangIlmuDanTemaKegiatan'] = false;
    }

    public function stateEditBidangIlmuDanTemaKegiatan($idBidangIlmu)
    {
        $this->state['onEditIdBidangIlmu'] = $idBidangIlmu;
        $this->record['bidang_ilmu'] = $idBidangIlmu;
        $this->record['tema_kegiatan'] = $this->recordSavedBidangIlmuTemaKegiatan[$idBidangIlmu];

        $this->state['editBidangIlmuDanTemaKegiatan'] = true;
    }

    public function deleteBidangIlmuDanTemaKegiatan($idBidangIlmu)
    {
        unset($this->recordSavedBidangIlmuTemaKegiatan[$idBidangIlmu]);
    }

    public function saveBidangIlmuDanTemaKegiatan($idBidangIlmu = null)
    {
        // bidang ilmu
        $newBidangIlmu = $this->record['bidang_ilmu'] ?? null;
        $newBidangIlmu = $this->selectValue($newBidangIlmu);
        $oldBidangIlmu = $idBidangIlmu ?? null;

        // tema kegiatan
        $newTemaKegiatan = $this->record['tema_kegiatan'] ?? null;

        // validate required
        if (empty($newBidangIlmu) || empty($newTemaKegiatan)) {
            if (empty($newBidangIlmu)) {
                $this->addError('bidang_ilmu', __('validation.required', [
                    'attribute' => __('litabmas::bidang_ilmu.nama_bidang_ilmu')
                ]));
            }

            if (empty($newTemaKegiatan)) {
                $this->addError('tema_kegiatan', __('validation.required', [
                    'attribute' => __('litabmas::tema_kegiatan.main')
                ]));
            }
            return;
        }

        // validasi apakah $newBidangIlmu sudah ada sebelumnya di recordSavedBidangIlmuTemaKegiatan, tapi jangan cek diri sendiri
        $existBidangIlmu = array_key_exists($newBidangIlmu, $this->recordSavedBidangIlmuTemaKegiatan);
        if (isset($idBidangIlmu) && $existBidangIlmu && $idBidangIlmu != $newBidangIlmu) {
            $this->addError('bidang_ilmu', __('validation.unique', ['attribute' => __('litabmas::bidang_ilmu.nama_bidang_ilmu')]));
            return;
        }

        // cek create apakah sudah ada di recordSavedBidangIlmuTemaKegiatan
        if (!isset($idBidangIlmu) && $existBidangIlmu) {
            $this->addError('bidang_ilmu', __('validation.unique', ['attribute' => __('litabmas::bidang_ilmu.nama_bidang_ilmu')]));
            return;
        }

        // cek edit atau add berdasarkan $idBidangIlmu
        if (isset($idBidangIlmu)) {
            $this->recordSavedBidangIlmuTemaKegiatan[$newBidangIlmu] = $newTemaKegiatan;
            $this->state['editBidangIlmuDanTemaKegiatan'] = false;
        }

        // cek ketika update dan bidang_ilmu tidak sama dengan bidang_ilmu sebelumnya
        if (!empty($oldBidangIlmu) && $oldBidangIlmu !== $newBidangIlmu) {
            unset($this->recordSavedBidangIlmuTemaKegiatan[$oldBidangIlmu]);
        }

        $this->recordSavedBidangIlmuTemaKegiatan[$newBidangIlmu] = $newTemaKegiatan;
        $this->state['addBidangIlmuDanTemaKegiatan'] = false;

        // reset state and record
        $this->state['onEditIdBidangIlmu'] = null;
        $this->record['bidang_ilmu'] = null;
        $this->record['tema_kegiatan'] = null;
    }

    /**
     * Override method save dari CreateEditComponent
     * untuk menambahkan custom success message
     *
     * @return void
     */
    public function save()
    {
        $this->dispatch('show-loading');

        $isCanAction = $this->validatePermission('post');

        if (!$isCanAction) {
            $this->dispatch('hide-loading');

            $this->alert = [
                'type' => 'error',
                'message' => "Anda tidak memiliki akses untuk menyimpan data",
            ];

            $this->dispatch('scroll-to-top');
            return;
        }

        // validasi data
        $defineFields = $this->defineFormFields();
        if (isset($this->additionalRecords) && !empty($this->additionalRecords)) {
            $defineFields = array_merge($defineFields, $this->additionalRecords);
        }
        $fields = WebRequest::buildFields($this->model, $defineFields, flattenFields: true);
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
                } elseif ($filterField['control'] === 'currency' && !empty($value)) {
                    // hapus titik ataupun koma, karena value aslinya adalah number
                    $data[$key] = (int) str_replace(['.', ','], '', $value);
                }
            }

            if (isset($filterField['type']) && $filterField['type'] === 'number') {
                $data[$key] = $value === '' ? null : $value;
            }
        }

        $attributes = Page::defineLabelByField(false, $this->urlInfo);

        try {
            // cek custom validasi jika ada
            $resultCustomValidate = $this->customValidationBeforeSave();

            // cek validasi model
            WebRequest::validateData($data, $this->model, $this->edit, $attributes, $this->messageValidation());
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

        // Custom success message untuk Klaster Pendanaan
        $namaKlaster = $return->nama_klaster ?? 'Klaster Pendanaan';
        if ($this->edit) {
            $message = "Klaster Pendanaan '{$namaKlaster}' berhasil diperbarui";
        } else {
            $message = "Klaster Pendanaan '{$namaKlaster}' berhasil ditambahkan";
        }

        $customRedirect = $this->successUrlAfterSave();
        if (!empty($customRedirect)) {
            return redirect()->to($customRedirect)->with('success', $message);
        }

        if (count($this->urlInfo['segments']) > 4) {
            $param = [];
            // sub resource
            foreach ($this->urlInfo['parameters'] as $key => $value) {
                $param[] = (int) $value;
            }

            $param[] = $return->id;
            if ($this->edit) {
                array_pop($this->urlInfo['segments']);
                return redirect()->to(implode('/', $this->urlInfo['segments']))->with('success', $message);
            } else {
                // delete last segment
                array_pop($this->urlInfo['segments']);
                return redirect()->to(implode('/', $this->urlInfo['segments']) . '/' . $return->id)->with('success', $message);
            }
        }

        return redirect()->route($this->urlInfo['module'] . '.' . $this->urlInfo['resource'] . '.show', $return->id)->with('success', $message);
    }

    /**
     * Simpan Draft
     * Drafts the form for the Klaster Pendanaan.
     *
     * @return void
     */
    public function draft()
    {
        $this->dispatch('show-loading');

        $isCanAction = $this->validatePermission('post');

        if (!$isCanAction) {
            $this->dispatch('hide-loading');

            $this->alert = [
                'type' => 'error',
                'message' => "Anda tidak memiliki akses untuk menyimpan data",
            ];

            $this->dispatch('scroll-to-top');
            return;
        }

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
                } elseif ($filterField['control'] === 'currency' && !empty($value)) {
                    // hapus titik ataupun koma, karena value aslinya adalah number
                    $data[$key] = (int) str_replace(['.', ','], '', $value);
                }
            }

            if (isset($filterField['type']) && $filterField['type'] === 'number') {
                $data[$key] = $value === '' ? null : $value;
            }
        }

        $attributes = Page::defineLabelByField(false, $this->urlInfo);

        try {
            // khusus draft, yg required hanya yg ada di card Informasi Umum
            $dataForValidate = Arr::only($data, [
                'kode_jenis_pendanaan',
                'kategori_klaster',
                'nama_klaster',
                'id_sumber_pendanaan',
                'maksimal_anggaran',
                'minimal_anggota',
                'maksimal_anggota',
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

        if (!empty($this->idDokumenTemplateRab)) {
            $this->mergeData['id_dokumen_template_rab'] = $this->idDokumenTemplateRab;
        }

        $this->mergeData['apakah_sudah_publikasi'] = false;

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

        $namaKlaster = $return->nama_klaster ?? 'Klaster Pendanaan';
        if ($this->edit) {
            $message = "Draft Klaster Pendanaan '{$namaKlaster}' berhasil diperbarui";
        } else {
            $message = "Draft Klaster Pendanaan '{$namaKlaster}' berhasil disimpan";
        }

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

        return redirect()->route($this->urlInfo['module'] . '.' . $this->urlInfo['resource'] . '.show', $return->id)
            ->with('success', $message);
    }

    private function customValidationSaveDraft(): bool
    {
        $isError = false;
        $sectionError = null;

        //if edit
        if ($this->edit) {
            $errorArray = $this->editCustomValidationBeforeSave();
            if ($errorArray['isError']) {
                $isError = true;
                $sectionError = $errorArray['sectionError'];
                return false;
            }
        }

        if ($this->record['maksimal_anggaran']) {
            $recordAnggaran = floatval(str_replace('.', '', $this->record['maksimal_anggaran']));
            $totalAnggaran = floatval($this->totalPendanaanSumberPendanaan);
            $mataUang = $this->mataUangSumberPendanaan ?? 'IDR';

            if ($totalAnggaran <= 0) {
                $this->addError('maksimal_anggaran', "Anggaran sumber pendanaan sudah habis atau tidak tersedia.");
                $isError = true;
                $sectionError = 'informasi-umum';
            } elseif ($recordAnggaran > $totalAnggaran) {
                $this->addError('maksimal_anggaran', "Batas pengajuan dana tidak boleh lebih dari total pendanaan sumber pendanaan " . $mataUang . " " . number_format($totalAnggaran, 0, ',', '.'));
                $isError = true;
                $sectionError = 'informasi-umum';
            }
        }

        $validasiKelompok = $this->validasiKategoriKelompok();
        if ($validasiKelompok['isError']) {
            $isError = true;
            $sectionError = $validasiKelompok['sectionError'];
        }

        if ($this->edit) {
            $checkFileRab = $this->idDokumenTemplateRab ?? $this->klasterPendanaan->id_dokumen_template_rab;
        } else {
            $checkFileRab = $this->idDokumenTemplateRab;
        }

        // cek harus upload file rab
        if (empty($checkFileRab)) {
            $isError = true;
            $sectionError ??= 'informasi-umum';
            $this->addError(
                'id_dokumen_template_rab',
                __('validation.required', ['attribute' => 'File Template Rancangan Anggaran Biaya (RAB)'])
            );
        }

        if ($isError) {
            $this->loadData();
            $this->dispatch('scroll-to-section', $sectionError);
            $this->dispatch('hide-loading');
            return false;
        }

        // masukkan recordSavedBidangIlmuTemaKegiatan ke mergeData
        $this->mergeData['_bidang_ilmu_dan_tema_kegiatan'] = $this->recordSavedBidangIlmuTemaKegiatan;

        // masukkan agenda kegiatan ke mergeData
        $this->mergeData['agenda_kegiatan'] = $this->record['agenda_kegiatan'];

        return true;
    }

    protected function loadData()
    {
        $this->processGetFirstDataAndAttribute();

        $isCanAction = $this->validatePermission('post');

        if (!$isCanAction) {
            abort(403);
        }

        // [Start] proses get rule dari model (dan set value jika edit)
        if ($this->edit) {
            WebRequest::validateId($this->edit);

            $data = $this->klasterPendanaan;

            $this->data = WebController::buildFormCard(
                $this->defineFormFields(),
                $this->model,
                data: $data
            );
        } else {
            $this->data = WebController::buildFormCard(
                $this->defineFormFields(),
                $this->model
            );
        }
        // [End] proses get rule dari model (dan set value jika edit)

        // Set wire:model (dan selected value ketika options)
        $this->initForm();

        // Set value ke record ketika edit
        $this->loadRecord();

        // Render nominal input
        $this->dispatch('render-nominal-input');

        $this->renderDateEndOutcome();
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
                if ($item['control'] === 'select-multiple-v2') {
                    $selectedValue = $selectedValue ?? [];
                    $item = array_merge($item, ['selected' => $selectedValue, 'values' => $selectedValue]);
                } elseif ($selectedValue !== null && !is_array($selectedValue)) {
                    $item = array_merge($item, ['selected' => $selectedValue, 'value' => $selectedValue]);
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
            // set default record output and outcome
            $outputAndOutcomeSection = $this->data['output-dan-outcome'];
            foreach ($outputAndOutcomeSection['items'] as $data) {
                if ($data['field'] == 'jenis_output_penelitian') {
                    $valueBefore = $this->record['jenis_output_penelitian'][$data['__index']] ?? false;
                    $this->record['jenis_output_penelitian'][$data['__index']] = $valueBefore;
                }
            }
            foreach ($outputAndOutcomeSection['items'] as $data) {
                if ($data['field'] == 'jenis_outcome_penelitian') {
                    $valueBefore = $this->record['jenis_outcome_penelitian'][$data['__index']] ?? false;
                    $this->record['jenis_outcome_penelitian'][$data['__index']] = $valueBefore;
                }
            }

            // set default record agenda kegiatan
            $agendaKegiatanSection = $this->data['agenda-kegiatan'];
            foreach ($agendaKegiatanSection['items'] as $data) {
                if ($data['field'] == 'agenda_kegiatan') {
                    $this->record['agenda_kegiatan'][$data['__index']] = [
                        'waktu_mulai' => $this->record['agenda_kegiatan'][$data['__index']]['waktu_mulai'] ?? null,
                        'waktu_selesai' => $this->record['agenda_kegiatan'][$data['__index']]['waktu_selesai'] ?? null
                    ];
                }
            }
            if (!isset($this->record['agenda_kegiatan'])) {
                $this->record['agenda_kegiatan'] = [];
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
        if (empty($this->record['jenis_output_penelitian'])) {
            $this->record['jenis_output_penelitian'] = $this->oldRecordOutput;
        }

        if (empty($this->record['jenis_outcome_penelitian'])) {
            $this->record['jenis_outcome_penelitian'] = $this->oldRecordOutcome;
        }

        if (empty($this->record['agenda_kegiatan'])) {
            $this->record['agenda_kegiatan'] = $this->oldRecordAgendaKegiatan;
        }

        $this->statusPublikasi = $this->klasterPendanaan->apakah_sudah_publikasi;
    }

    protected function defineFormFields()
    {
        $maxAnggaran = $this->totalPendanaanSumberPendanaan ?? 0;
        $mataUang = $this->mataUangSumberPendanaan ?? 'IDR';
        $convert = $mataUang !== config('money.defaults.currency');
        $humanReadableMaxAnggaran = money($maxAnggaran, $mataUang, $convert);

        $disabledAnggaran = empty($this->idSumberPendanaan);
        $helperMaxAnggaran = null;
        $placeholderMaxAnggaran = 'Masukkan batas anggaran dari klaster ini';

        if (empty($this->idSumberPendanaan)) {
            $placeholderMaxAnggaran = 'Pilih sumber pendanaan terlebih dahulu';
        } elseif ($maxAnggaran <= 0) {
            $helperMaxAnggaran = "Anggaran sumber pendanaan sudah habis atau tidak tersedia.";
            $disabledAnggaran = true;
            $placeholderMaxAnggaran = 'Anggaran tidak tersedia';
        } else {
            $helperMaxAnggaran = "Batas pengajuan dana dari sumber pendanaan adalah {$humanReadableMaxAnggaran}";
        }

        $fields = [
            'informasi-umum' => [
                'title' => 'Informasi Umum',
                'icon' => 'document-magnifying-glass',
                'items' => [
                    [
                        'isEmpty' => !$this->isEmptyPeriode,
                        'field' => 'id_periode_pendanaan',
                        'label' => 'Periode Pendanaan',
                        'required' => true,
                        'options' => $this->listPeriode,
                        'selected' => $this->idPeriodePendanaan,
                        'wire:model.change' => 'idPeriodePendanaan',
                    ],
                    [
                        'field' => 'nama_klaster'
                    ],
                    [
                        'field' => 'kode_jenis_pendanaan',
                        'control' => 'radio',
                        'inline' => false
                    ],
                    [
                        'field' => 'id_sumber_pendanaan',
                        'required' => true,
                        'options' => $this->listSumberPendanaan,
                        'wire:model.change' => 'idSumberPendanaan',
                        'selected' => $this->idSumberPendanaan,
                        'disabled' => empty($this->listSumberPendanaan),
                    ],
                    [
                        'field' => 'maksimal_anggaran',
                        'control' => 'currency',
                        'placeholder' => $placeholderMaxAnggaran,
                        'disabled' => $disabledAnggaran,
                        'label' => 'Batas Pengajuan Dana',
                        'helper' => $helperMaxAnggaran
                    ],
                    [
                        'field' => 'kategori_klaster',
                        'control' => 'radio',
                        'inline' => false,
                        'label' => 'Kategori Pendanaan',
                        'wire:model.change' => 'kategoriKlaster',
                        'required' => true
                    ],
                    [
                        'field' => 'minimal_anggota',
                        'options' => $this->minAnggotaOptions,
                        'required' => true,
                        'placeholder' => 'Masukkan minimal anggota dari klaster pendanaan',
                        'helper' => 'Anggota tidak termasuk ketua'
                    ],
                    [
                        'field' => 'maksimal_anggota',
                        'options' => $this->maxAnggotaOptions,
                        'required' => true,
                        'placeholder' => 'Masukkan maksimal anggota dari klaster pendanaan',
                        'helper' => 'Anggota tidak termasuk ketua'
                    ],
                    [
                        'field' => 'apakah_butuh_approve_semua_anggota',
                        'control' => 'radio',
                        'required' => false,
                        'inline' => false,
                        'wire:model.change' => 'isApproveAll',
                        'options' => [1 => 'Ya, Perlu', 0 => 'Tidak Perlu'],
                    ],
                    [
                        'field' => 'apakah_bisa_multi_ajuan',
                        'control' => 'radio',
                        'required' => true,
                        'inline' => false,
                        'label' => 'Apakah Bisa Multi Pengajuan?',
                        'options' => [1 => 'Ya, Bisa', 0 => 'Tidak Bisa'],
                        'wire:model.change' => 'apakahBisaMultiAjuan',
                        'helper' => 'Tentukan apakah user bisa mengajukan lebih dari 1x pada klaster ini'
                    ],
                    [
                        'field' => 'id_dokumen_template_rab',
                        'label' => 'Upload file Template Rancangan Anggaran Biaya (RAB)',
                        'type' => 'file',
                        'required' => true,
                        'max_size' => 1024 * 2,
                        'file_type' => ['xlsx', 'xls'],
                        'wire:model' => 'idDokumenTemplateRab'
                    ]
                ],
            ],
            'bidang-ilmu-dan-tema-kegiatan' => [
                'title' => 'Bidang Ilmu & Tema',
                'icon' => 'document-text',
                'items' => [
                    [
                        'field' => 'bidang_ilmu',
                        'label' => __('litabmas::bidang_ilmu.nama_bidang_ilmu'),
                        'required' => true,
                        'options' => $this->bidangIlmuOptions,
                    ],
                    [
                        'field' => 'tema_kegiatan',
                        'label' => __('litabmas::tema_kegiatan.main'),
                        'required' => true,
                        'options' => $this->temaKegiatanOptions,
                        'control' => 'select-multiple-v2',
                        'placeholder' => 'Pilih tema sesuai bidang ilmu',
                    ]
                ],
            ],
            'output-dan-outcome' => [
                'title' => 'Luaran dan Publikasi',
                'icon' => 'document-plus',
                'items' => [], // diisi oleh method setOutputOutcomeItems
            ],
            'agenda-kegiatan' => [
                'title' => 'Tahapan Kegiatan',
                'icon' => 'calendar-days',
                'items' => [], // diisi oleh method setAgendaKegiatanItems
            ]
        ];

        $fields = $this->setInformasiUmumItems($fields);

        $fields = $this->setMultiAjuanItems($fields);

        $fields = $this->setOutputOutcomeItems($fields);

        $fields = $this->setAgendaKegiatanItems($fields);

        return $fields;
    }

    private function setInformasiUmumItems($fields)
    {
        $fieldKategoriKlaster = $fields['informasi-umum']['items'][1];
        if ($this->kategoriKlaster === KlasterPendanaan::KATEGORI_KELOMPOK) {
            // tambahkan informasi kelompok ke helper
            $fieldKategoriKlaster['helper'] = 'Kategori klaster ini adalah klaster kelompok, '
                . 'maka memerlukan minimal dan maksimal anggota';
            $fields['informasi-umum']['items'][1] = $fieldKategoriKlaster;
        } elseif ($this->kategoriKlaster === KlasterPendanaan::KATEGORI_INDIVIDU) {
            // unset/hilangkan field minimal_anggota dan maksimal_anggota dan apakah_butuh_approve_semua_anggota
            $fields['informasi-umum']['items'] = array_filter($fields['informasi-umum']['items'], function ($item) {
                return $item['field'] !== 'minimal_anggota' &&
                    $item['field'] !== 'maksimal_anggota' &&
                    $item['field'] !== 'apakah_butuh_approve_semua_anggota';
            });

            // tambahkan informasi individu ke helper
            $fieldKategoriKlaster['helper'] = 'Kategori klaster ini adalah klaster individu, '
                . 'maka tidak memerlukan minimal dan maksimal anggota';
            $fields['informasi-umum']['items'][1] = $fieldKategoriKlaster;
        } elseif ($this->kategoriKlaster === null) {
            // unset/hilangkan field minimal_anggota dan maksimal_anggota dan apakah_butuh_approve_semua_anggota
            $fields['informasi-umum']['items'] = array_filter($fields['informasi-umum']['items'], function ($item) {
                return $item['field'] !== 'minimal_anggota' &&
                    $item['field'] !== 'maksimal_anggota' &&
                    $item['field'] !== 'apakah_butuh_approve_semua_anggota';
            });
        }

        if ($this->edit && is_null($this->isApproveAll)) {
            $this->record['apakah_butuh_approve_semua_anggota'] = $this->klasterPendanaan->apakah_butuh_approve_semua_anggota ? 1 : 0;
        } else {
            $this->record['apakah_butuh_approve_semua_anggota'] = $this->isApproveAll ? 1 : 0;
        }

        return $fields;
    }

    private function setMultiAjuanItems($fields)
    {
        // Handle Multi Ajuan
        $fieldApakahBisaMultiAjuan = null;
        $multiAjuanIndex = null;

        foreach ($fields['informasi-umum']['items'] as $index => $item) {
            if ($item['field'] === 'apakah_bisa_multi_ajuan') {
                $fieldApakahBisaMultiAjuan = $item;
                $multiAjuanIndex = $index;
                break;
            }
        }

        if ($this->apakahBisaMultiAjuan == 1) {
            $this->mergeData['maksimal_ajuan_per_user'] = null;
        } else {
            $this->mergeData['maksimal_ajuan_per_user'] = 1;
        }

        return $fields;
    }

    private function setOutputOutcomeItems($fields)
    {
        $outputItems = $this->pivotKlasterOutput;
        $outcomeItems = $this->pivotKlasterOutcome;

        // kalo output dan outcome tidak ada, set temp_field agar tidak error saat set record
        if ($outputItems->isEmpty() && $outcomeItems->isEmpty()) {
            $fields['output-dan-outcome']['items'][] = [
                'field' => 'temp_field',
                'type' => 'hidden'
            ];

            return $fields;
        }

        foreach ($outputItems as $output) {
            $fields['output-dan-outcome']['items'][] = [
                'field' => 'jenis_output_penelitian',
                'label' => $output->nama_output,
                'type' => 'hidden',
                'wire:model' => 'record.jenis_output_penelitian.' . $output->id,
                '__index' => $output->id
            ];
        }

        foreach ($outcomeItems as $outcome) {
            $fields['output-dan-outcome']['items'][] = [
                'field' => 'jenis_outcome_penelitian',
                'label' => $outcome->nama_outcome,
                'type' => 'hidden',
                'wire:model' => 'record.jenis_outcome_penelitian.' . $outcome->id,
                '__index' => $outcome->id
            ];
        }

        return $fields;
    }

    private function setAgendaKegiatanItems($fields)
    {
        $this->idAgendaKegiatanStepOutcome = null;

        // cek belum memilih sumber pendanaan
        if (empty($this->idSumberPendanaan)) {
            $fields['agenda-kegiatan']['items'][] = [
                'field' => 'temp_field',
                'type' => 'hidden'
            ];
            return $fields;
        }

        // unset 'temp_field' using search
        $fields['agenda-kegiatan']['items'] = array_filter($fields['agenda-kegiatan']['items'], function ($item) {
            return $item['field'] !== 'temp_field';
        });

        $agendaKegiatanList = $this->pivotKlasterAgenda;
        if (empty($agendaKegiatanList) || $agendaKegiatanList->isEmpty()) {
            $fields['agenda-kegiatan']['items'][] = [
                'field' => 'temp_field',
                'type' => 'hidden'
            ];
            return $fields;
        }

        foreach ($agendaKegiatanList as $agenda) {
            if ($agenda->kode_agenda == AgendaKegiatan::STEP_PENGUMPULAN_HASIL) {
                $this->idAgendaKegiatanStepOutcome = $agenda->id_agenda_kegiatan;
            }

            $fields['agenda-kegiatan']['items'][] = [
                'field' => 'agenda_kegiatan',
                'label' => $agenda->nama_agenda,
                'type' => 'hidden',
                'wire:model' => 'record.agenda_kegiatan.' . $agenda->id_agenda_kegiatan,
                '__index' => $agenda->id_agenda_kegiatan
            ];
        }

        return $fields;
    }

    /**
     * Merupakan proses ketika edit yang mana membutuhkan value dari tiap inputan
     * Dan juga utk handle ketika ada aksi livewire utk mempertahakan value tersebut.
     */
    private function processGetFirstDataAndAttribute()
    {
        if (empty($this->edit)) {
            return;
        }

        // get data sumber pendanaan
        $idSumberPendanaan = $this->idSumberPendanaan ?? $this->klasterPendanaan->id_sumber_pendanaan;
        $sumberPendanaan = $this->getTotalPendanaanDanMataUang($idSumberPendanaan);
        $this->mataUangSumberPendanaan = $sumberPendanaan->mata_uang;
        $this->totalPendanaanSumberPendanaan = $sumberPendanaan->sisa_anggaran ?? $sumberPendanaan->total_pendanaan;
        if ($this->edit) {
            $this->totalPendanaanSumberPendanaan = $sumberPendanaan->total_pendanaan;
        }

        // get data output
        $checkedOutput = $this->service->getOutputWajibByIdKlasterPendanaan($this->edit)
            ->groupBy('id_jenis_output_penelitian')->toArray();
        foreach ($this->pivotKlasterOutput as $output) {
            $isset = isset($checkedOutput[$output->id]);
            $output->is_checked = $isset; // set value is_checked
            $this->oldRecordOutput[$output->id] = $isset; // set old record output
        }

        // get data outcome
        $checkedOutcome = $this->service->getOutcomeWajibByIdKlasterPendanaan($this->edit)
            ->groupBy('id_jenis_outcome_penelitian')->toArray();
        foreach ($this->pivotKlasterOutcome as $outcome) {
            $isset = isset($checkedOutcome[$outcome->id]);
            $outcome->is_checked = $isset; // set value is_checked
            $this->oldRecordOutcome[$outcome->id] = $isset; // set old record outcome
        }

        // get data agenda kegiatan
        $agendaKegiatanBySumberPendanaan = $this->getAktifAgendaKegiatanByIdSumberPendanaan($idSumberPendanaan);
        $agendaKegiatanByKlasterPendanaan = $this->getAktifAgendaKegiatanByIdKlasterPendanaan();

        // set waktu_mulai and waktu_selesai from klaster pendanaan to agenda kegiatan
        foreach ($agendaKegiatanByKlasterPendanaan as $agenda) {
            $agendaKegiatanBySumberPendanaan->where('id_agenda_kegiatan', $agenda->id_agenda_kegiatan)
                ->each(function ($item) use ($agenda) {
                    // format waktu_mulai and waktu_selesai to YYYY-MM-DD
                    $item->waktu_mulai = $agenda->waktu_mulai
                        ? date('Y-m-d', strtotime($agenda->waktu_mulai))
                        : null;
                    $item->waktu_selesai = $agenda->waktu_selesai
                        ? date('Y-m-d', strtotime($agenda->waktu_selesai))
                        : null;
                });
        }

        // set old record agenda kegiatan
        foreach ($agendaKegiatanBySumberPendanaan as $agenda) {
            $this->oldRecordAgendaKegiatan[$agenda->id_agenda_kegiatan] = [
                'waktu_mulai' => $agenda->waktu_mulai ? date('Y-m-d', strtotime($agenda->waktu_mulai)) : null,
                'waktu_selesai' => $agenda->waktu_selesai ? date('Y-m-d', strtotime($agenda->waktu_selesai)) : null
            ];
        }

        $this->pivotKlasterAgenda = $agendaKegiatanBySumberPendanaan;
    }

    private function validateDateAgendaKegiatanIntersection($listAgendaKegiatan)
    {
        $isError = false;
        $detailError = [];
        $agendaKegiatan = $this->record['agenda_kegiatan'];
        $periodePendanaan = PeriodePendanaan::find($this->idPeriodePendanaan);

        // Iterasi melalui array kegiatan untuk memeriksa setiap kegiatan
        $allowedIntersectSteps = AgendaKegiatan::ALLOWED_INTERSECT_STEPS;
        $activityArray = $listAgendaKegiatan->toArray();
        $agendaKegiatanNames = array_column($activityArray, 'nama_agenda', 'id');
        $agendaKegiatanCodes = array_column($activityArray, 'kode_agenda', 'id');
        foreach ($agendaKegiatan as $id => $agenda) {
            if ($agenda === []) { // jika kegiatan yang berupa array kosong karena pengecekkan ini hanya untuk kegiatan yang dipilih
                continue;
            }

            // cek tidak boleh kurang dari tgl periode aktif
            if (!empty($agenda['waktu_mulai']) && strtotime($agenda['waktu_mulai']) < strtotime($periodePendanaan->tanggal_mulai)) {
                $isError = true;
                $detailError[$id] = "Tidak boleh kurang dari tanggal mulai periode aktif";
                continue;
            }

            // validasi tgl awal tidak boleh lebih dari tgl akhir dan sebaliknya
            if (!empty($agenda['waktu_mulai']) && !empty($agenda['waktu_selesai'])) {
                if (strtotime($agenda['waktu_mulai']) > strtotime($agenda['waktu_selesai'])) {
                    $isError = true;
                    $detailError[$id] = "Tanggal awal tidak boleh lebih dari tanggal akhir";
                } elseif (strtotime($agenda['waktu_selesai']) < strtotime($agenda['waktu_mulai'])) {
                    $isError = true;
                    $detailError[$id] = "Tanggal akhir tidak boleh kurang dari tanggal awal";
                }
            }

            // jika boleh berpotongan, maka skip
            if (in_array($agendaKegiatanCodes[$id], $allowedIntersectSteps)) {
                continue;
            }

            $start = null;
            if (!empty($agenda['waktu_mulai'])) {
                $start = strtotime($agenda['waktu_mulai']);
            } elseif (!empty($agenda['waktu_selesai'])) {
                $start = strtotime($agenda['waktu_selesai']);
            }
            $end = isset($agenda['waktu_selesai']) ? strtotime($agenda['waktu_selesai']) : $start;

            // Bandingkan dengan kegiatan lain
            foreach ($agendaKegiatan as $compareId => $compareAgenda) {
                // Jangan bandingkan kegiatan dengan dirinya sendiri atau kegiatan yang berupa array kosong karena pengecekkan ini hanya untuk kegiatan yang dipilih
                if (($id == $compareId) || $compareAgenda === []) {
                    continue;
                }

                $compareStart = null;
                if (!empty($compareAgenda['waktu_mulai'])) {
                    $compareStart = strtotime($compareAgenda['waktu_mulai']);
                } elseif (!empty($compareAgenda['waktu_selesai'])) {
                    $compareStart = strtotime($compareAgenda['waktu_selesai']);
                }
                $compareEnd = isset($compareAgenda['waktu_selesai']) ? strtotime($compareAgenda['waktu_selesai']) : $compareStart;

                // Periksa apakah tanggal berpotongan
                if ($start <= $compareEnd && $end >= $compareStart) {
                    // Menyimpan pesan tentang kegiatan yang berpotongan
                    $isError = true;
                    $detailError[$id] = "Berpotongan dengan kegiatan '{$agendaKegiatanNames[$compareId]}'";
                }
            }
        }

        return [
            'isError' => $isError,
            'message' => 'Terjadi kesalahan pada pemilihan tanggal.',
            'detailError' => $detailError
        ];
    }

    public function nextStep($setStep = null)
    {
        $this->steps = $setStep;

        $this->dispatch('hide-loading');
    }

    #[On('updateAgendaTanggalSelesai')]
    public function updateAgendaTanggalSelesai($tanggal)
    {
        $this->recordTglAkhirPublikasi = $tanggal;
    }
}