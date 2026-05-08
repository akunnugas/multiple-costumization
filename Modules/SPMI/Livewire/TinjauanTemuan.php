<?php

namespace Modules\SPMI\Livewire;

use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Error;
use Modules\Core\Livewire\MainComponent;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\AuditTemuan;
use Modules\SPMI\Services\PenilaianMatriksManagementService;
use Modules\SPMI\Services\TinjauanTemuanManagementService;

class TinjauanTemuan extends MainComponent
{
    use SpmiViewData;

    // Resource Id adalah id dari penialian auditor
    public $resourceId = null;
    public $information = [];
    public $raw = [];
    public $assessmentMatrices = [];
    public $periodData = null;
    public $isFinishAssessment = false;
    public $listPenilaianMatriksSkor = [];
    public $idPenilaianPredikat = null;
    public $checkedScore = null;
    public $findingTypeOptions = AuditTemuan::TYPES;

    // Kebutuhan validasi
    public $isCanAction = false;
    public $isFinalizeTemuan = false;

    // Kebutuhan untuk form
    public $isShowForm = false;
    public $penilaianMatriksId = null;
    public $record = [
        'akar_masalah' => null,
        'rencana_peningkatan_mutu' => null,
        'pelaksana' => null,
        'tanggal_peningkatan_mutu' => null,
        'nilai_target' => null,
        'nilai_target_default' => []
    ];

    // Kebutuhan filter dan pencarian data
    public $filterElement = null;
    public $filterCategory = null;
    public $filteredMatrices = [];
    public $search = null;

    protected $queryString = ['id_unit', 'periode_audit', 'id_penilaian_panduan', 'id_jadwal_audit'];

    public $id_penilaian_panduan = null;
    public $id_unit = null;
    public $periode_audit = null;
    public $id_jadwal_audit = null;

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
            $assessment = PenilaianAudit::findByStudyProgramId($this->id_penilaian_panduan, $this->id_unit, $this->periodData->id, false, $this->id_jadwal_audit);
        } else {
            $assessment = PenilaianAudit::find($this->resourceId);
            $this->id_penilaian_panduan = $assessment->id_penilaian_panduan;
            $this->isFinishAssessment = $assessment?->apakah_terfinalisasi ?? false;
            $this->isFinalizeTemuan = $assessment?->apakah_temuan_terfinalisasi ?? false;

            // Jika tidak ditemukan maka abort 404
            if (!$assessment) {
                abort(404);
            }

            $this->periodData = AuditPeriode::find($assessment->id_audit_periode);
        }

        // Jika resource penilaian ditemukan maka redirect ke edit
        $isExistResourceId = !!$assessment && ($assessment->id_audit_periode == $this->periodData?->id);
        if (!$this->resourceId && $isExistResourceId) {
            return redirect()->route('spmi.tinjauan-temuan.show', [$assessment->id]);
        }

        $this->loadData();
        $this->isCanAction = $this->validatePermission('post') || $this->validatePermission('put');
    }

    public function loadService()
    {
        $this->service = new TinjauanTemuanManagementService();
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

        return $this->buildView('spmi::pages.tinjauan-temuan.create')
            ->layout('core::components.layouts.main-outer');
    }

    /**
     * Event saat pencarian berubah.
     *
     * @param mixed $value
     */
    public function updatedSearch($value = null)
    {
        $this->reset('filteredMatrices');

        $filterElement = $this->selectValue($this->filterElement);
        $filterCategory = $this->selectValue($this->filterCategory);

        $matriksPenilaian = array_filter($this->assessmentMatrices, function ($item) use ($filterElement) {
            $isIndicator = $item->kategori_penilaian == PenilaianMatriks::CATEGORY_INDICATOR;

            if ((empty($filterElement) || $filterElement == 'all') && $isIndicator) {
                return true;
            }

            if ($filterElement === 'checked' && !empty($item->id_tinjauan_temuan) && $isIndicator) {
                return true;
            }

            if ($filterElement === 'unchecked' && empty($item->id_tinjauan_temuan) && $isIndicator) {
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
     * Event saat filter element berubah.
     *
     * @param mixed $value
     */
    public function updateFilter($value, $type)
    {
        $value = $this->selectValue($value);

        if ($type == 1) {
            $this->filterElement = $value;
        } else {
            $this->filterCategory = $value;
        }

        $this->updatedSearch($this->search);
    }

    public function showForm($isShowForm = true, $penilaianMatriksId = null)
    {
        $this->resetForm();
        $this->isShowForm = true;

        // Tampilkan form untuk edit
        if ($isShowForm) {
            $this->isShowForm = true;
            $this->penilaianMatriksId = $penilaianMatriksId;
            $this->loadFormData();
            return;
        }

        // Jika tidak menampilkan form maka reset data
        $this->resetForm();
    }

    public function syncNextTarget()
    {
        $checkHasNotFilledScore = !!array_filter($this->assessmentMatrices, function ($item) {
            return $item->kategori_penilaian == PenilaianMatriks::CATEGORY_INDICATOR && empty($item->id_tinjauan_temuan);
        });

        if ($checkHasNotFilledScore) {
            $this->alert('error', 'Terdapat indikator temuan yang belum diisi. Silakan mengisi semua indikator RTM terlebih dahulu.');
            return;
        }

        $nextTargetUrl = $this->service->syncNextTarget($this->raw['id_penilaian_audit']);

        $err = Error::isError($nextTargetUrl);
        if ($err) {
            $this->alert('error', $nextTargetUrl->message);
            return;
        }

        $nextPeriode = $this->information['periode_audit'] + 1;
        $this->alert('success', "Data RTM berhasil diterapkan di target capaian periode {$nextPeriode}. Silakan periksa target terbaru
            <a href='{$nextTargetUrl}' target='_blank' rel='noopener noreferrer'><b style='color: #0F6AF5;'>di sini</b></a>.");
    }

    public function saveTinjauanTemuan()
    {
        $this->validateData();

        $data = [
            'id_penilaian_audit' => $this->raw['id_penilaian_audit'],
            'id_penilaian_matriks' => $this->penilaianMatriksId,
            'akar_masalah' => $this->record['akar_masalah'],
            'rencana_peningkatan_mutu' => $this->record['rencana_peningkatan_mutu'],
            'pelaksana' => $this->record['pelaksana'],
            'tanggal_peningkatan_mutu' => $this->record['tanggal_peningkatan_mutu'],
            'id_predikat_matriks_penilaian' => $this->idPenilaianPredikat,
            'nilai_target' => $this->record['nilai_target'],
            'nilai_target_default' => array_keys($this->record['nilai_target_default'])[0] ?? null,
        ];

        $saveData = $this->service->store($data);

        if (Error::isError($saveData)) {
            $this->alert('error', 'Terjadi kesalahan saat menyimpan data.');
            return;
        }

        $this->loadMatricesData();
        $this->showForm(false);

        $this->updatedSearch($this->search);
    }

    /**
     * Event saat skor dipilih.
     */
    public function onCheckedScore($idPenilaianPredikat)
    {
        $this->idPenilaianPredikat = $idPenilaianPredikat;

        if (isset($this->record['nilai_target_default'][$this->checkedScore])) {
            unset($this->record['nilai_target_default'][$this->checkedScore]);
        }

        $this->checkedScore = array_keys($this->record['nilai_target_default'])[0] ?? null;
        if (!is_null($this->checkedScore)) {
            $this->record['nilai_target'] = $this->checkedScore . '.00';
        }
    }

    /**
     * Fungsi untuk load data.
     */
    protected function loadData()
    {
        // Jika mode create
        if (!isset($this->resourceId)) {
            $data = $this->service->showInformationByStudyProgram($this->id_unit, $this->periodData?->toArray(), $this->id_jadwal_audit ? (int) $this->id_jadwal_audit : null);

            if (Error::isError($data)) {
                abort($data->code);
            }

            [$this->information, $this->raw] = $data;
            $this->loadMatricesData();

            return;
        }

        // Jika mode edit
        $data = $this->service->showInformationByAssessment($this->resourceId);

        if (Error::isError($data)) {
            abort($data->code);
        }

        [$this->information, $this->raw] = $data;
        $this->loadMatricesData();
    }

    /**
     * Fungsi untuk load data form.
     */
    protected function loadFormData()
    {
        $this->loadMatrixScores();

        $savedTinjauanTemuan = $this->service->showByAssessment($this->raw['id_penilaian_audit'], $this->penilaianMatriksId);
        $selectedMatriksPenilaian = (array) array_values(
            array_filter($this->assessmentMatrices, fn ($item) => $item->id == $this->penilaianMatriksId)
        )[0] ?? [];
        $defaultTemuan = [
            'akar_masalah' => $selectedMatriksPenilaian['t_akar_masalah'],
            'rencana_peningkatan_mutu' => $selectedMatriksPenilaian['t_rencana_peningkatan_mutu'],
            'pelaksana' => $selectedMatriksPenilaian['t_pelaksana'],
            'tanggal_peningkatan_mutu' => $selectedMatriksPenilaian['t_tanggal_peningkatan_mutu'],
        ];

        $this->record = array_merge($defaultTemuan, $savedTinjauanTemuan);

        if (isset($this->record['nilai_target_default']) && is_numeric($this->record['nilai_target_default'])) {
            $this->checkedScore = (int) $this->record['nilai_target_default'];
        }

        // Jika kosong maka default skor diambil dari $this->record['nilai_target']
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
        $listPenilaianMatriksSkor = (new PenilaianMatriksManagementService)->showMatrixScores($this->penilaianMatriksId);

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
     * Fungsi untuk set default score.
     *
     * @param mixed $score
     */
    protected function setDefaultScore($score)
    {
        $this->record['nilai_target_default'] = [((int) $score) => true];
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
     * Fungsi untuk load semua data matriks.
     */
    protected function loadMatricesData()
    {
        // Reset data
        $this->filteredMatrices = [];
        $this->assessmentMatrices = [];

        $assessmentMatrices =
            $this->service->showAllMatricesByPenilaianPanduan(
                $this->raw['id_penilaian_panduan'],
                $this->raw['id_unit'],
                $this->periodData->id,
                $this->resourceId,
                false,
                !empty($this->raw['id_jadwal_audit']) ? (int) $this->raw['id_jadwal_audit'] : null
            );
        $this->assessmentMatrices = $assessmentMatrices;

        $this->updatePositionMatriksWithParent($assessmentMatrices);
    }

    public function updatePositionMatriksWithParent($assessmentMatrices)
    {
        $matrixIndicators = array_filter($assessmentMatrices, function ($matrix) {
            return $matrix->kategori_penilaian == PenilaianMatriks::CATEGORY_INDICATOR;
        });

        $matrixIndicators = array_values($matrixIndicators);

        foreach ($matrixIndicators as $matrix) {
            if (!$this->isInfilteredMatrices($matrix)) {
                $this->filteredMatrices[] = $matrix;
            }
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

    protected function validateData()
    {
        $this->validate([
            'record.akar_masalah' => 'required',
            'record.rencana_peningkatan_mutu' => 'required',
            'record.pelaksana' => 'required',
            'record.tanggal_peningkatan_mutu' => 'required',
            'record.nilai_target' => 'required',
            'record.nilai_target_default' => 'required',
        ], [
            'record.akar_masalah.required' => 'Akar masalah wajib diisi.',
            'record.rencana_peningkatan_mutu.required' => 'Rencana tindak lanjut wajib diisi.',
            'record.pelaksana.required' => 'Pelaksana wajib diisi.',
            'record.tanggal_peningkatan_mutu.required' => 'Tanggal perbaikan wajib diisi.',
            'record.nilai_target.required' => 'Nilai target wajib diisi.',
            'record.nilai_target_default.required' => 'Nilai target wajib diisi.',
        ]);
    }

    /**
     * Fungsi untuk reset form.
     */
    protected function resetForm()
    {
        $this->reset(
            'isShowForm',
            'penilaianMatriksId',
            'record',
            'checkedScore',
            'listPenilaianMatriksSkor',
            'idPenilaianPredikat'
        );

        $this->resetErrorBag();
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
            'isHtml' => true,
        ];
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
