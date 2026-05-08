<?php

namespace Modules\SPMI\Livewire;

use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Modules\Core\Helpers\Error;
use Modules\Core\Livewire\MainComponent;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\AuditTemuan as ModelsAuditTemuan;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Services\AuditTemuanManagementService;
use Modules\SPMI\Services\PenilaianAuditorManagementService;

class AuditTemuan extends MainComponent
{
    use SpmiViewData;

    // Resource Id adalah id dari penialian auditor
    public $resourceId = null;
    public $information = [];
    public $raw = [];
    public $assessmentMatrices = [];
    public $periodData = null;
    public $isFinishAssessment = false;
    public $isFinalized = false;

    // Kebutuhan validasi
    public $isCanAction = false;

    // Kebutuhan untuk form
    public $isShowForm = false;
    public $penilaianMatriksId = null;
    public $findingTypeOptions = ModelsAuditTemuan::TYPES;
    public $record = [
        'uraian_temuan_audit' => null,
        'rencana_peningkatan_mutu' => null,
        'pelaksana' => null,
        'tanggal_peningkatan_mutu' => null,
        'jenis_temuan' => null,
        'akar_masalah' => null,
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
            $this->isFinalized = $assessment?->apakah_temuan_terfinalisasi ?? false;

            // Jika tidak ditemukan maka abort 404
            if (!$assessment) {
                abort(404);
            }

            $this->periodData = AuditPeriode::find($assessment->id_audit_periode);
        }

        // Jika resource penilaian ditemukan maka redirect ke edit
        $isExistResourceId = !!$assessment && ($assessment->id_audit_periode == $this->periodData?->id);
        if (!$this->resourceId && $isExistResourceId) {
            return redirect()->route('spmi.audit-temuan.show', [$assessment->id]);
        }

        $this->loadData();
        $this->isCanAction = $this->validatePermission('post') || $this->validatePermission('put');
    }

    public function loadService()
    {
        $this->service = new AuditTemuanManagementService();
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

        return $this->buildView('spmi::pages.audit-temuan.create')
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

            if ($filterElement === 'checked' && !empty($item->id_audit_temuan) && $isIndicator) {
                return true;
            }

            if ($filterElement === 'unchecked' && empty($item->id_audit_temuan) && $isIndicator) {
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

    public function finalizeData()
    {
        if (!$this->validatedPermission('put', 'post')) {
            $this->alert('error', 'Anda tidak memiliki akses untuk melakukan finalisasi data.');
            return;
        }

        $checkHasNotFilledScore = !!array_filter($this->assessmentMatrices, function ($item) {
            return $item->kategori_penilaian == PenilaianMatriks::CATEGORY_INDICATOR && empty($item->id_audit_temuan);
        });

        if ($checkHasNotFilledScore) {
            $this->alert('error', 'Terdapat indikator temuan yang belum diisi. Silakan mengisi semua temuan terlebih dahulu.');
            return;
        }

        $totalTemuan = count(array_filter($this->assessmentMatrices, function ($item) {
            return $item->kategori_penilaian == PenilaianMatriks::CATEGORY_INDICATOR && !empty($item->id_audit_temuan);
        }));

        $update = (new PenilaianAuditorManagementService)->update([
            // Update total temuan juga
            'total_temuan' => $totalTemuan,
            'apakah_temuan_terfinalisasi' => true
        ], $this->resourceId);

        if (Error::isError($update)) {
            $this->alert('error', 'Terjadi kesalahan saat menyimpan data.' . $update->message);
            return;
        }

        $this->isFinalized = true;
        $this->alert('success', 'Berhasil finalisasi data temuan audit');
    }

    public function unfinalizeData()
    {
        if (!$this->validatedPermission('put', 'post')) {
            $this->alert('error', 'Anda tidak memiliki akses untuk melakukan batalkan finalisasi data.');
            return;
        }

        // Cek apakah RTM sudah diisi
        $isHasRTM = $this->service->checkHasDataRTM($this->resourceId);

        if ($isHasRTM) {
            $this->alert('error', 'Temuan auditor tidak bisa membatalkan finalisasi data, Karena sudah ada data RTM yang terisi.');
            return;
        }

        $update = (new PenilaianAuditorManagementService)->update([
            'apakah_temuan_terfinalisasi' => false
        ], $this->resourceId);

        if (Error::isError($update)) {
            $this->alert('error', 'Terjadi kesalahan saat menyimpan data.' . $update->message);
            return;
        }

        $this->isFinalized = false;
        $this->alert('success', 'Berhasil membatalkan finalisasi data temuan audit');
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

    public function saveFinding()
    {
        $this->validateData();

        $data = [
            'id_penilaian_audit' => $this->raw['id_penilaian_audit'],
            'id_penilaian_matriks' => $this->penilaianMatriksId,
            'uraian_temuan_audit' => $this->record['uraian_temuan_audit'],
            'rencana_peningkatan_mutu' => $this->record['rencana_peningkatan_mutu'],
            'pelaksana' => $this->record['pelaksana'],
            'tanggal_peningkatan_mutu' => $this->record['tanggal_peningkatan_mutu'],
            'jenis_temuan' => $this->selectValue($this->record['jenis_temuan']),
            'akar_masalah' => $this->record['akar_masalah'],
        ];

        $saveData = $this->service->store($data);

        if (Error::isError($saveData)) {
            $this->alert('error', $saveData->message);
            return;
        }

        $this->loadMatricesData();
        $this->showForm(false);

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
        $savedFinding = $this->service->showByAssessment($this->raw['id_penilaian_audit'], $this->penilaianMatriksId);
        $this->record = [
            'uraian_temuan_audit' => $savedFinding['uraian_temuan_audit'] ?? null,
            'rencana_peningkatan_mutu' => $savedFinding['rencana_peningkatan_mutu'] ?? null,
            'pelaksana' => $savedFinding['pelaksana'] ?? null,
            'tanggal_peningkatan_mutu' => $savedFinding['tanggal_peningkatan_mutu'] ?? null,
            'jenis_temuan' => $savedFinding['jenis_temuan'] ?? null,
            'akar_masalah' => $savedFinding['akar_masalah'] ?? null,
        ];
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
            'record.uraian_temuan_audit' => 'required',
            'record.rencana_peningkatan_mutu' => 'required',
            'record.pelaksana' => 'required',
            'record.tanggal_peningkatan_mutu' => 'required',
            'record.jenis_temuan' => 'required',
        ], [
            'record.uraian_temuan_audit.required' => 'Temuan wajib diisi.',
            'record.rencana_peningkatan_mutu.required' => 'Rencana perbaikan wajib diisi.',
            'record.pelaksana.required' => 'Pelaksana wajib diisi.',
            'record.tanggal_peningkatan_mutu.required' => 'Tanggal perbaikan wajib diisi.',
            'record.jenis_temuan.required' => 'Jenis temuan wajib diisi.',
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
            'record'
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

    protected function validatedPermission(...$permission)
    {
        $isValidated = false;

        foreach ($permission as $perm) {
            if ($this->permission[$perm]) {
                $isValidated = true;
            }
        }

        return $isValidated;
    }
}
