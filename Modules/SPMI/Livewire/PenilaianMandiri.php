<?php

namespace Modules\SPMI\Livewire;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\SessionManager;
use Modules\Core\Livewire\MainComponent;
use Modules\Gate\Models\Role;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianSkor;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;
use Modules\SPMI\Services\PenilaianMatriksManagementService;
use Modules\SPMI\Services\PenilaianMandiriManagementService;
use Modules\SPMI\Services\SuratTugasAuditorManagementService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PenilaianMandiri extends MainComponent
{
    use SpmiViewData;

    public $resourceId = null;
    public $information = [];
    public $raw = [];
    public $assessmentMatrices = [];
    public $keyPanduan = [];
    public $periodData = null;

    // Kebutuhan untuk validasi
    public $isNotFinalizedTargetIndikator = false;
    public $isValidAssessmentDate = false;
    public $isFinalized = false;
    public $isCanAction = false;
    public $isInternalRole = false;

    // Kebutuhan untuk form
    public $isShowScoreForm = false;
    public $idMatriksPenilaian = null;
    public $idMatriksPenilaianSkor = null;
    public $statusOptions = PenilaianSkor::STATUS;
    public $listPenilaianMatriksSkor = [];
    public $checkedScore = null;
    public $savedScore = [];
    public $selectedMatrix = [];
    public $editableStatus = null;
    public $referenceIndicators = [];
    public $quantitativeScores = [];
    public $record = [
        'nilai_default' => [],
        'nilai' => null,
        'status_penilaian' => null,
    ];
    public $isRoleFinalisasi = false;

    // Kebutuhan filter dan pencarian data
    public $filterCategory = null;
    public $filterElement = null;
    public $filteredMatrices = [];
    public $search = null;

    protected $queryString = ['id_unit', 'periode_audit', 'id_penilaian_panduan', 'id_jadwal_audit'];

    public $id_penilaian_panduan = null;
    public $id_unit = null;
    public $periode_audit = null;
    public $id_jadwal_audit = null;

    protected $model = PenilaianAudit::class;

    public function mount()
    {
        parent::mount();
        $this->resourceId = $this->viewData['resourceId'];

        if (!isset($this->resourceId) && !isset($this->id_unit, $this->periode_audit)) {
            abort(404);
        }

        // Persiapan data awal
        if (!isset($this->resourceId)) {
            $this->periodData = AuditPeriode::findByYear($this->periode_audit);
            $assessment = PenilaianAudit::findByStudyProgramId($this->id_penilaian_panduan, $this->id_unit, $this->periodData->id, true, $this->id_jadwal_audit);
        } else {
            $assessment = PenilaianAudit::find($this->resourceId);
            $this->id_penilaian_panduan = $assessment->id_penilaian_panduan;
            $this->isFinalized = $assessment?->apakah_terfinalisasi ?? false;

            // Jika tidak ditemukan maka abort 404
            if (!$assessment) {
                abort(404);
            }

            $this->periodData = AuditPeriode::find($assessment->id_audit_periode);
        }

        // Jika resource ditemukan maka redirect ke edit
        $isExistResourceId = !!$assessment && ($assessment->id_audit_periode == $this->periodData?->id);
        if (!$this->resourceId && $isExistResourceId) {
            return redirect()->route('spmi.penilaian-mandiri.edit', [$assessment->id]);
        }

        $this->loadData();

        $achievementTargetStatus = $this->service->showTargetIndikatorStatus(
            $this->id_penilaian_panduan,
            $this->id_unit ?? $this->raw['id_unit'],
            $this->periodData?->id ?? $this->raw['id_audit_periode'],
            !empty($this->raw['id_jadwal_audit']) ? (int) $this->raw['id_jadwal_audit'] : null
        );
        $totalPenilaianMatriksIndicator = count(array_filter($this->assessmentMatrices, function ($item) {
            return $item->kategori_penilaian == PenilaianMatriks::CATEGORY_INDICATOR;
        }));

        $isFillAllMatrix = ($achievementTargetStatus['total_target_terisi'] ?? 0) >= $totalPenilaianMatriksIndicator;
        if ($achievementTargetStatus['apakah_terfinalisasi'] ?? false) {
            $this->isNotFinalizedTargetIndikator = false;
        } else {
            $this->isNotFinalizedTargetIndikator = !$isFillAllMatrix;
        }

        $this->isValidAssessmentDate = Carbon::now()->between(
            $this->raw['tanggal_awal_penilaian'],
            Carbon::parse($this->raw['tanggal_akhir_penilaian'])->addDay(),
            true
        );

        $idBiodata = auth()->user()->biodata?->id ?? null;
        $roleUser = (new SuratTugasAuditorManagementService)->getPosisiAuditor(
            $idBiodata,
            $this->periodData?->id ?? null,
            $this->id_unit ?? $this->raw['id_unit']
        );

        $isAdminPenjaminanMutu = auth()->user()?->kode_role === Role::ROLE_ADMIN_PENJAMINAN_MUTU;
        $this->isRoleFinalisasi = in_array($roleUser, [
            SuratTugasAuditorPegawai::POSITION_AUDITEE,
        ]) || $isAdminPenjaminanMutu;

        $this->keyPanduan = PenilaianPanduan::where('id', $this->id_penilaian_panduan)
            ->select('apakah_data_default', 'apakah_iku_kualitatif', 'kode_penilaian_panduan')
            ->first()
            ->toArray();

        $this->isInternalRole = SessionManager::isInternalRole();
        $this->isCanAction = $this->validatePermission('post') || $this->validatePermission('put');
    }

    public function loadService()
    {
        $this->service = new PenilaianMandiriManagementService();
    }

    public function render()
    {
        View::share($this->viewData + [
            'isLivewire' => true,
            'permission' => $this->permission,
            'urlInfo' => $this->urlInfo,
            'alert' => $this->alert,
            'headerClass' => 'header_position-static',
        ]);

        return $this->buildView('spmi::pages.penilaian-mandiri.create')
            ->layout('core::components.layouts.main-outer');
    }

    /**
     * Event saat pencarian berubah.
     *
     * @param mixed $value
     */
    public function updatedSearch($value)
    {
        $this->reset('filteredMatrices');

        $filterElement = $this->selectValue($this->filterElement);
        $filterCategory = $this->selectValue($this->filterCategory);

        $matriksPenilaian = array_filter($this->assessmentMatrices, function ($item) use ($filterElement) {
            $isIndicator = $item->kategori_penilaian == PenilaianMatriks::CATEGORY_INDICATOR;

            if (empty($filterElement) || $filterElement == 'all') {
                return true;
            }

            if ($filterElement === 'checked' && !empty($item->nilai_auditee) && $isIndicator) {
                return true;
            }

            if ($filterElement === 'unchecked' && empty($item->nilai_auditee) && $isIndicator) {
                return true;
            }

            return false;
        });

        $matriksPenilaian = array_filter($matriksPenilaian, function ($item) use ($filterCategory) {
            if (empty($filterCategory) || $filterCategory == 'all') {
                return true;
            }

            return $item->jenis_penilaian == $filterCategory;
        });

        $this->filteredMatrices = array_filter($matriksPenilaian, function ($item) use ($value) {
            $pertanyaanPenilaian = strtolower(preg_replace('/\s+/', '', strip_tags($item->pertanyaan_penilaian)));
            $value = strtolower(preg_replace('/\s+/', '', $value));
            return str_contains($pertanyaanPenilaian, $value);
        });

        $this->updatePositionMatriksWithParent($this->filteredMatrices);
    }

    /**
     * Event saat finalisasi data.
     */
    public function finalizeData()
    {
        $checkHasNotFilledScore = !!array_filter($this->assessmentMatrices, function ($item) {
            return $item->kategori_penilaian == PenilaianMatriks::CATEGORY_INDICATOR && empty($item->nilai_auditee);
        });

        // Count total indicator
        $totalIndicator = count(array_filter($this->assessmentMatrices, function ($item) {
            return $item->kategori_penilaian == PenilaianMatriks::CATEGORY_INDICATOR;
        }));

        if ($checkHasNotFilledScore) {
            $this->alert('error', 'Terdapat indikator yang belum diisi skor. Silakan mengisi semua skor terlebih dahulu.');
            return;
        }

        $update = $this->service->update([
            'apakah_terfinalisasi' => true,
            'total_indikator_matriks' => $totalIndicator,
        ], $this->resourceId);

        if (Error::isError($update)) {
            $this->alert('error', 'Terjadi kesalahan saat menyimpan data.' . $update->message);
            return;
        }

        $this->isFinalized = true;
    }

    /**
     * Event saat finalisasi data.
     */
    public function unfinalizeData()
    {
        $this->isFinalized = false;
        $update = $this->service->update([
            'apakah_terfinalisasi' => false,
        ], $this->resourceId);

        if (Error::isError($update)) {
            $this->alert('error', 'Terjadi kesalahan saat menyimpan data.');
            return;
        }
    }

    /**
     * Event saat filter element berubah.
     *
     * @param mixed $value
     */
    public function updateFilter($value)
    {
        $value = $this->selectValue($value);

        if (in_array($value, ['checked', 'unchecked'])) {
            $this->filterElement = $value;
        } else {
            $this->filterCategory = $value;
        }

        $this->updatedSearch($this->search);
    }


    /**
     * Event saat menampilkan form skor.
     *
     * @param bool $isShowScoreForm
     * @param int $idMatriksPenilaian
     */
    public function showScoreForm($isShowScoreForm = true, $idMatriksPenilaian = null)
    {
        $this->resetForm();

        // Tampilkan form jika isShowScoreForm true, dan load data awal
        if ($isShowScoreForm) {
            $this->isShowScoreForm = $isShowScoreForm;
            $this->idMatriksPenilaian = $idMatriksPenilaian;
            $this->loadFormData();
            $this->dispatch('scroll-to-active');
            return;
        }

        // Jika tidak menampilkan form maka reset data
        $this->resetForm();
        $this->dispatch('scroll-to-active');
    }

    /**
     * Event saat skor dipilih.
     *
     * @param int $idMatriksPenilaianSkor
     */
    public function onCheckedScore($idMatriksPenilaianSkor)
    {
        // Event saat skor dipilih
        $this->idMatriksPenilaianSkor = $idMatriksPenilaianSkor;

        if (isset($this->record['nilai_default'][$this->checkedScore])) {
            unset($this->record['nilai_default'][$this->checkedScore]);
        }

        $this->checkedScore = array_keys($this->record['nilai_default'])[0] ?? null;
        if (!is_null($this->checkedScore)) {
            $this->record['nilai'] = $this->checkedScore . '.00';
        }

        // Merubah skor status penilaian indikator secara realtime
        $achievementTarget = $this->selectedMatrix['nilai_target'] ?? null;
        $this->editableStatus = $this->setStatusNilai($this->record['nilai'], $achievementTarget);
    }

    public function recalculateScore()
    {
        // Validasi hitung ulang hanya boleh untuk admin internal
        if (!$this->isInternalRole) {
            return new Error('Hitung ulang hanya boleh dilakukan oleh super admin');
        }

        $assessmentData = [
            'id_jadwal_audit' => $this->raw['id_jadwal_audit'],
            "id_audit_periode" => $this->raw['id_audit_periode'],
            "id_unit" => $this->raw['id_unit'],
            "id_lembaga_akreditasi" => $this->raw['id_lembaga_akreditasi'],
            "id_penilaian_panduan" => $this->raw['id_penilaian_panduan'],
            'apakah_penilaian_mandiri' => true,
        ];

        $scoreData = array_filter($this->assessmentMatrices, function ($item) {
            return $item->kategori_penilaian == PenilaianMatriks::CATEGORY_INDICATOR;
        });

        $scoreData = array_map(function ($item) {
            $scoreStatus = $this->setStatusNilai($item->nilai_auditee, $item->nilai_target);

            return [
                'id_penilaian_matriks' => $item->id,
                'id_predikat_matriks_penilaian' => $item->id_predikat_matriks_penilaian,
                'nilai_default' => $item->nilai_default,
                'nilai' => $item->nilai_auditee,
                'status_penilaian' => $scoreStatus,
            ];
        }, $scoreData);

        $scoreData = array_values($scoreData);

        $result = $this->service->recalculateScore($scoreData, $assessmentData, $this->resourceId);

        if (Error::isError($result)) {
            $this->alert('error', $result->message);
            return;
        }

        $totalSuccess = $result['total_success'];
        $totalFailed = $result['total_failed'];

        // Load ulang data dan tutup form
        $this->loadMatricesData();

        // Penghitungan ulang skor akhir, agar tetap sinkron ketika beberapa auditor mengisi skor secara bersamaan
        $finalPenilaianSkor = $this->sumElementMatrixScore();
        $this->information['nilai_akhir'] = number_format($finalPenilaianSkor, 2, '.', ',');
        $this->isFinalized = $result['is_finalized'];

        $this->alert(
            'success',
            'Skor berhasil dihitung ulang. ' . $totalSuccess . ' berhasil, ' . $totalFailed . ' gagal. Silakan finalisasi data untuk menyimpan perubahan.'
        );
    }

    /**
     * Event saat skor disimpan.
     */
    public function saveScore()
    {
        if (empty($this->record['nilai'])) {
            $this->throwValidationError('record.score', 'Skor tidak boleh kosong.');
            return;
        }

        // Ambil target Capaian untuk menentukan status skor indikator
        $achievementTarget = $this->selectedMatrix['nilai_target'] ?? null;
        $scoreStatus = $this->setStatusNilai($this->record['nilai'], $achievementTarget);

        $assessmentData = [
            'id_jadwal_audit' => $this->raw['id_jadwal_audit'],
            "id_audit_periode" => $this->raw['id_audit_periode'],
            "id_unit" => $this->raw['id_unit'],
            "id_lembaga_akreditasi" => $this->raw['id_lembaga_akreditasi'],
            "id_penilaian_panduan" => $this->raw['id_penilaian_panduan'],
            'apakah_penilaian_mandiri' => true,
        ];

        $scoreData = [
            'id_penilaian_matriks' => $this->idMatriksPenilaian,
            'id_predikat_matriks_penilaian' => $this->idMatriksPenilaianSkor,
            'nilai_default' => array_keys($this->record['nilai_default'])[0] ?? null,
            'nilai' => $this->record['nilai'],
            'status_penilaian' => $scoreStatus,
        ];

        // Simpan data
        $savedScore = $this->service->saveScoreAndAssessment($scoreData, $assessmentData, $this->resourceId, true);

        if (Error::isError($savedScore)) {
            $this->alert('error', $savedScore->message);
            return;
        }

        if (empty($this->resourceId)) {
            $this->resourceId = $savedScore['id'];
        }

        // Load ulang data dan tutup form
        $this->loadMatricesData();
        $this->showScoreForm(false);
        $this->updatedSearch($this->search);
    }

    /**
     * Fungsi untuk load data.
     */
    protected function loadData()
    {
        // Jika mode create
        if (!isset($this->resourceId)) {
            $data = $this->service->showInformationByStudyProgram($this->id_unit, $this->periodData?->toArray(), $this->id_jadwal_audit ? (int) $this->id_jadwal_audit : null);
            if (($data[0]['nilai_akhir'] ?? '-') == '-') {
                unset($data[0]['nilai_akhir']);
            }

            if (Error::isError($data)) {
                abort($data->code);
            }

            [$this->information, $this->raw] = $data;
            $this->loadMatricesData();

            return;
        }

        // Jika mode edit
        $data = $this->service->showInformation($this->resourceId);
        if (($data[0]['nilai_akhir'] ?? '-') == '-') {
            unset($data[0]['nilai_akhir']);
        }

        if (Error::isError($data)) {
            abort($data->code);
        }

        [$this->information, $this->raw] = $data;
        $this->loadMatricesData();
    }

    /**
     * Fungsi untuk load semua data untuk form.
     */
    protected function loadFormData()
    {
        $this->loadMatrixScores();

        $this->savedScore = $this->service->showScore($this->resourceId, $this->idMatriksPenilaian);
        $this->referenceIndicators = $this->service->getReferenceIndicator(
            $this->idMatriksPenilaian,
            $this->raw['id_unit'],
            $this->periodData->id
        );

        if (!empty($this->savedScore)) {
            $this->idMatriksPenilaianSkor = $this->savedScore['id_predikat_matriks_penilaian'];
            $this->idMatriksPenilaian = $this->savedScore['id_penilaian_matriks'];
        }

        // Ambil data matriks yang dipilih
        $selectedMatrix = array_filter($this->assessmentMatrices, fn ($item) => $item->id == $this->idMatriksPenilaian);
        $this->selectedMatrix = ((array) array_values($selectedMatrix)[0]) ?? [];

        // Penilaian kuantitatif
        if (!empty($this->selectedMatrix)
                && $this->selectedMatrix['jenis_penilaian'] == PenilaianMatriks::TYPE_QUANTITATIVE
                && !$this->keyPanduan['apakah_iku_kualitatif']) {
            $quantitativeScore = $this->service->countScoreQuantitative(
                $this->selectedMatrix['id'],
                $this->periodData->id,
                $this->raw['id_unit'],
                $this->selectedMatrix['rumus_penilaian'],
                $this->listPenilaianMatriksSkor
            );

            // Jika terjadi error saat menghitung skor kuantitatif, maka tampilkan error
            if (Error::isError($quantitativeScore)) {
                $this->alert(
                    'error',
                    $quantitativeScore->message
                );

                $this->reset('savedScore', 'checkedScore', 'record');
                return;
            }

            $this->quantitativeScores = $quantitativeScore['show'];
        }

        if (!empty($quantitativeScore['selected'])) {
            $defaultScore = $quantitativeScore['selected']['nilai'];
            $scoreDecimal = $quantitativeScore['selected']['nilai_decimal'];
            $this->checkedScore = $defaultScore;
            $this->savedScore = [
                ...$this->savedScore,
                'nilai_default' => $defaultScore,
                'nilai' => $scoreDecimal
            ];
        }

        $record = Arr::only($this->savedScore, ['nilai', 'status_penilaian', 'nilai_default']);

        $this->record = array_merge($this->record, $record);

        if (isset($this->record['nilai_default']) && is_numeric($this->record['nilai_default'])) {
            $this->checkedScore = (int) $this->record['nilai_default'];
        }

        // Jika kosong maka default skor diambil dari $this->savedScore['nilai']
        if (!isset($this->checkedScore) && !empty($this->savedScore)) {
            $this->checkedScore = (int) ($this->savedScore['nilai'] ?? null);
            $this->record['nilai'] = $this->checkedScore . '.00';
        }

        if ($this->checkedScore !== null) {
            $this->setDefaultScore($this->checkedScore);
        }
    }

    /**
     * Fungsi untuk load skor matriks.
     */
    protected function loadMatrixScores()
    {
        $listPenilaianMatriksSkor = (new PenilaianMatriksManagementService)->showMatrixScores($this->idMatriksPenilaian);

        $disableScores = [];
        foreach ($listPenilaianMatriksSkor as $i => $score) {
            $this->listPenilaianMatriksSkor[$i] = $score;

            $isDisable = $score->apakah_nonaktif;

            if ($isDisable) {
                $disableScores[] = $i;
            }
        }

        $squentialDisableScores = $this->breakSequential($disableScores);
        foreach ($squentialDisableScores as $sequential) {
            foreach ($sequential as $index) {
                $this->listPenilaianMatriksSkor[$index]->disable_score = $sequential;

                // jika index terakhir
                if ($index === end($sequential)) {
                    $this->listPenilaianMatriksSkor[$index]->end_disable = true;
                    $this->listPenilaianMatriksSkor[$index]->colspan = count($sequential);

                    continue;
                }
            }
        }
    }

    protected function updatePositionMatriksWithParent($assessmentMatrices)
    {
        $matrixIndicators = array_filter($assessmentMatrices, function ($matrix) {
            return $matrix->kategori_penilaian == PenilaianMatriks::CATEGORY_INDICATOR;
        });

        $matrixIndicators = array_values($matrixIndicators);

        foreach ($matrixIndicators as $matrix) {
            $this->recursiveFindParent($matrix);
        }

        // Sort ascending info_left
        usort($this->filteredMatrices, function ($a, $b) {
            return $a->info_left <=> $b->info_left;
        });
    }

    /**
     * Cari parent dari indicator.
     *
     * @param mixed $matrix
     */
    protected function recursiveFindParent($matrix)
    {
        $parent = array_filter($this->assessmentMatrices, function ($item) use ($matrix) {
            return $item->id == $matrix->id_parent;
        });

        if (empty($parent)) {
            return;
        }

        $parent = array_values($parent)[0];
        $parent = $parent;

        if (!$this->isInfilteredMatrices($parent)) {
            $this->filteredMatrices[] = $parent;
        }

        if (isset($parent->id_parent)) {
            $this->recursiveFindParent($parent);
        }

        return;
    }

    /**
     * Cek apakah item sudah ada di filtered matrices.
     *
     * @param mixed $item
     */
    protected function isInfilteredMatrices($item)
    {
        foreach ($this->filteredMatrices as $filteredItem) {
            if ($filteredItem->id === $item->id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Fungsi untuk memecah angka yang berurutan.
     */
    protected function breakSequential($arr)
    {
        $result = [];
        $temp = [];
        $prev = null;

        foreach ($arr as $num) {
            if ($prev !== null && $num !== $prev + 1) {
                $result[] = $temp;
                $temp = [];
            }
            $temp[] = $num;
            $prev = $num;
        }
        if (!empty($temp)) {
            $result[] = $temp;
        }
        return $result;
    }

    /**
     * Fungsi untuk reset form.
     */
    protected function resetForm()
    {
        $this->reset(
            'isShowScoreForm',
            'idMatriksPenilaian',
            'listPenilaianMatriksSkor',
            'idMatriksPenilaianSkor',
            'checkedScore',
            'record',
            'savedScore',
            'selectedMatrix',
            'editableStatus',
            'referenceIndicators',
            'quantitativeScores'
        );

        $this->resetErrorBag();
    }

    /**
     * Fungsi untuk ambil value choices.
     */
    protected function selectValue($value)
    {
        if (is_array($value) && array_key_exists('value', $value)) {
            return $value['value'];
        }

        return $value ?? null;
    }

    /**
     * Fungsi untuk set status nilai.
     *
     * @param mixed $nilaiIndikator
     * @param mixed $nilaiTarget
     */
    protected function setStatusNilai($nilaiIndikator, $nilaiTarget)
    {
        if ($nilaiIndikator > $nilaiTarget) {
            return PenilaianSkor::STATUS_MELAMPAUI;
        }

        if ($nilaiIndikator < $nilaiTarget) {
            return PenilaianSkor::STATUS_BELUM_MEMENUHI;
        }

        return PenilaianSkor::STATUS_MEMENUHI;
    }

    /**
     * Fungsi untuk load semua data matriks.
     */
    protected function loadMatricesData()
    {
        $this->assessmentMatrices =
            $this->service->showAllMatricesByPenilaianPanduan(
                $this->raw['id_penilaian_panduan'],
                $this->raw['id_unit'],
                $this->periodData->id,
                $this->resourceId,
                !empty($this->raw['id_jadwal_audit']) ? (int) $this->raw['id_jadwal_audit'] : null
            );
        $this->filteredMatrices = $this->assessmentMatrices;
    }

    protected function sumElementMatrixScore()
    {
        $finalPenilaianSkor = array_filter($this->assessmentMatrices, function ($item) {
            return ($item->kategori_penilaian === PenilaianMatriks::CATEGORY_ELEMENT) &&
                ($item->jenis_penilaian === PenilaianMatriks::TYPE_FINAL_SCORE);
        });
        $finalPenilaianSkor = array_values($finalPenilaianSkor);
        $finalPenilaianSkor = array_map(function ($item) {
            return $item->nilai_akhir;
        }, $finalPenilaianSkor);

        $finalPenilaianSkor = array_sum($finalPenilaianSkor);

        return $finalPenilaianSkor;
    }

    /**
     * Fungsi untuk menampilkan alert.
     *
     * @param string $type
     * @param string $message
     */
    protected function alert($type, $message)
    {
        $this->alert = [
            'type' => $type,
            'message' => $message,
        ];
    }

    /**
     * Fungsi untuk set default score.
     *
     * @param mixed $score
     */
    protected function setDefaultScore($score)
    {
        $this->record['nilai_default'] = [((int) $score) => true];
    }

    /**
     * Export data to Excel.
     */
    public function exportExcel()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()
                ->setCreator('SIAKAD')
                ->setTitle('Penilaian Mandiri')
                ->setSubject('Penilaian Mandiri Export')
                ->setDescription('Export data penilaian mandiri');

            // Add header information
            $currentRow = 1;
            $sheet->setCellValue('A' . $currentRow, 'PENILAIAN MANDIRI');
            $sheet->mergeCells('A' . $currentRow . ':J' . $currentRow);
            $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $currentRow++;

            // Add information section
            foreach ($this->information as $key => $value) {
                $label = \Modules\Core\Helpers\Page::defineLabelByField($key);
                $cleanValue = strip_tags(str_replace('<br>', ', ', $value));
                $sheet->setCellValue('A' . $currentRow, $label);
                $sheet->setCellValue('B' . $currentRow, ':');
                $sheet->setCellValue('C' . $currentRow, $cleanValue);
                $sheet->mergeCells('C' . $currentRow . ':J' . $currentRow);
                $currentRow++;
            }

            $currentRow++;

            // Get data to export
            $dataToExport = !empty($this->filteredMatrices) ? $this->filteredMatrices : $this->assessmentMatrices;

            // Get all indicator matrix IDs for fetching reference indicators
            $indicatorMatrixIds = array_map(
                fn($item) => $item->id ?? $item['id'],
                array_filter($dataToExport, fn($item) => ($item->kategori_penilaian ?? $item['kategori_penilaian']) == PenilaianMatriks::CATEGORY_INDICATOR)
            );

            // Fetch all reference indicators at once
            $allReferenceIndicators = $this->service->getAllReferenceIndicators(
                array_values($indicatorMatrixIds),
                $this->raw['id_unit'],
                $this->periodData->id
            );

            // Table headers
            $headers = ['No', 'Elemen Dan Indikator', 'Kategori', 'Bobot', 'Target', 'Skor Auditee', 'Skor Akhir', 'Status', 'Bukti Referensi'];
            $column = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($column . $currentRow, $header);
                $sheet->getStyle($column . $currentRow)->getFont()->setBold(true);
                $sheet->getStyle($column . $currentRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE8E8E8');
                $sheet->getStyle($column . $currentRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle($column . $currentRow)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                $column++;
            }
            $currentRow++;

            // Add data
            foreach ($dataToExport as $item) {
                $item = (array) $item;
                $isElement = $item['kategori_penilaian'] == PenilaianMatriks::CATEGORY_ELEMENT;
                $isIndicator = $item['kategori_penilaian'] == PenilaianMatriks::CATEGORY_INDICATOR;
                $isDimension = $item['kategori_penilaian'] == PenilaianMatriks::CATEGORY_DIMENSION;

                if ($isElement || $isDimension) {
                    // Element or Dimension row
                    $sheet->setCellValue('A' . $currentRow, strip_tags($item['pertanyaan_penilaian']));
                    $sheet->mergeCells('A' . $currentRow . ':I' . $currentRow);
                    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
                    $sheet->getStyle('A' . $currentRow)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF9FAFC');
                    $sheet->getStyle('A' . $currentRow . ':I' . $currentRow)->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getStyle('A' . $currentRow)->getAlignment()->setWrapText(true);
                } else if ($isIndicator) {
                    // Indicator row
                    $sheet->setCellValue('A' . $currentRow, $item['nomor_penilaian']);
                    $sheet->setCellValue('B' . $currentRow, strip_tags($item['pertanyaan_penilaian']));
                    $sheet->setCellValue('C' . $currentRow, PenilaianMatriks::TYPES[$item['jenis_penilaian']] ?? '');
                    $sheet->setCellValue('D' . $currentRow, $item['bobot_penilaian'] ?? 'NA');
                    $sheet->setCellValue('E' . $currentRow, $item['nilai_target'] ?? 'NA');
                    $sheet->setCellValue('F' . $currentRow, $item['nilai_auditee'] ?? 'NA');
                    $sheet->setCellValue('G' . $currentRow, $item['nilai_akhir'] ?? 'NA');

                    // Status
                    $status = 'NA';
                    if (!empty($item['status_penilaian'])) {
                        $status = PenilaianSkor::STATUS[$item['status_penilaian']] ?? 'NA';
                    }
                    $sheet->setCellValue('H' . $currentRow, $status);

                    // Bukti Referensi
                    $matrixId = $item['id'];
                    $referenceNames = $allReferenceIndicators[$matrixId] ?? [];
                    $buktiReferensi = !empty($referenceNames) ? implode(', ', $referenceNames) : '-';
                    $sheet->setCellValue('I' . $currentRow, $buktiReferensi);

                    // Apply borders to indicator rows
                    $sheet->getStyle('A' . $currentRow . ':I' . $currentRow)->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);

                    // Enable text wrapping for all cells in this row
                    $sheet->getStyle('A' . $currentRow . ':I' . $currentRow)->getAlignment()->setWrapText(true);

                    // Align numeric columns to right
                    $sheet->getStyle('D' . $currentRow . ':G' . $currentRow)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                $currentRow++;
            }

            // Set specific column widths (not auto-size to control width)
            $sheet->getColumnDimension('A')->setWidth(8);  // No
            $sheet->getColumnDimension('B')->setWidth(50); // Elemen Dan Indikator
            $sheet->getColumnDimension('C')->setWidth(15); // Kategori
            $sheet->getColumnDimension('D')->setWidth(10); // Bobot
            $sheet->getColumnDimension('E')->setWidth(10); // Target
            $sheet->getColumnDimension('F')->setWidth(12); // Skor Auditee
            $sheet->getColumnDimension('G')->setWidth(12); // Skor Akhir
            $sheet->getColumnDimension('H')->setWidth(15); // Status
            $sheet->getColumnDimension('I')->setWidth(50); // Bukti Referensi

            // Generate filename
            $filename = 'Penilaian_Mandiri_' . ($this->information['nama_program_studi'] ?? 'Export') . '_' . date('Y-m-d_His') . '.xlsx';
            $filename = preg_replace('/[^A-Za-z0-9_\\-\\.]/', '_', $filename);

            // Save to temporary file
            $writer = new Xlsx($spreadsheet);
            $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
            $writer->save($tempFile);

            // Return download response
            return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            $this->alert('error', 'Terjadi kesalahan saat mengekspor data: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fungsi untuk throw validation error.
     *
     * @param string $column
     * @param string $message
     * @param array $errors
     */
    protected function throwValidationError($column = null, $message = null, $errors = [])
    {
        // Fungsi untuk menampilkan error validasi
        if (empty($errors)) {
            $error = ValidationException::withMessages([
                $column => $message,
            ]);

            throw $error;
        }

        $error = ValidationException::withMessages($errors);

        throw $error;
    }
}
