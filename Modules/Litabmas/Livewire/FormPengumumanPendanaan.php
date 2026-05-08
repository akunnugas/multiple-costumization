<?php

namespace Modules\Litabmas\Livewire;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Renderless;
use Livewire\WithFileUploads;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Page;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Livewire\CreateEditComponent;
use Modules\Core\Models\UnitKerja;
use Modules\Litabmas\Models\PengumumanPendanaan;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Models\SumberPendanaan;
use Modules\Litabmas\Services\PengumumanPendanaanManagementService;
use Modules\Litabmas\Services\SumberPendanaanService;

class FormPengumumanPendanaan extends CreateEditComponent
{
    use LitabmasViewData, WithFileUploads;

    public $fields;

    public $isInitEdit = false; // apakah merupakan state edit pertama kali (sblm livewire render lagi)
    public $prevPeriodePendanaan;
    public $idPeriodePendanaan;
    public $idSumberPendanaan;
    public $idDokumenThumbnail;
    public $totalPendanaan;
    public $mataUangSumberPendanaan;
    public $pengelolaPendanaan;

    // options
    public $pengelolaBantuanOptions;
    public $periodePendanaanOptions;


    #[Renderless]
    public function loadService()
    {
        $this->service = new PengumumanPendanaanManagementService;
    }

    #[Renderless]
    public function loadModel()
    {
        $this->model = PengumumanPendanaan::class;
    }

    /**
     * Dipanggil sekali ketika component pertama kali terbuat (setelah boot tapi hanya sekali).
     *
     * @return void
     */
    public function mount()
    {
        parent::mount();

        $periodeAktif = PeriodePendanaan::periodeAktif();
        $this->idPeriodePendanaan = $periodeAktif->id ?? null;
        $this->periodePendanaanOptions = PeriodePendanaan::options();
        $this->pengelolaBantuanOptions = UnitKerja::options();

        if (empty($this->edit) && empty($this->idPeriodePendanaan)) {
            $this->alert = [
                'type' => 'warning',
                'title' => 'Tidak Bisa Membuat Pengajuan',
                'message' => 'Periode pendanaan belum aktif, silakan hubungi Administrator.',
            ];
        }

        if (!empty($this->edit)) {
            $this->isInitEdit = true;
        }
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
     * Digunakan ketika membutuhkan custom validasi.
     * Method ini jalan sebelum validasi model WebRequest::validateData() yg ada di dalam proses save()
     *
     * @return bool
     */
    public function customValidationBeforeSave(): bool
    {
        $this->mergeData['id_dokumen_thumbnail'] = $this->idDokumenThumbnail;
        $this->mergeData['status_pengumuman'] = PengumumanPendanaan::STATUS_TERPUBLIKASI;

        return true;
    }

    /**
     * Digunakan ketika membutuhkan custom validasi draft.
     * Method ini jalan sebelum validasi model WebRequest::validateData() yg ada di dalam proses draft()
     *
     * @return bool
     */
    private function customValidationSaveDraft(): bool
    {
        $this->mergeData['id_dokumen_thumbnail'] = $this->idDokumenThumbnail;
        $this->mergeData['status_pengumuman'] = PengumumanPendanaan::STATUS_DRAFT;

        return true;
    }

    /**
     * Proses save tapi dengan validasi berbeda.
     * Hanya untuk menyimpan pengajuan untuk sementara, tanpa mengajukan proposalnya.
     *
     * @return \Illuminate\Http\RedirectResponse|void|null
     * @throws ValidationException
     */
    public function draft()
    {
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
        $data = Arr::only($this->record, array_map(fn ($item) => $item['field'], $fields));

        foreach ($data as $key => $value) {
            $filterField = array_filter($fields, fn ($item) => $item['field'] == $key);
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
            $data = Arr::only($data, [
                'id_periode_pendanaan', 'id_sumber_pendanaan', 'id_dokumen_thumbnail', 'id_dokumen_petunjuk_teknis'
            ]);

            // cek custom validasi draft jika ada
            $resultCustomValidate = $this->customValidationSaveDraft();

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

        return redirect()->route($this->urlInfo['module'] . '.' . $this->urlInfo['resource'] . '.show', $return->id)
            ->with('success', $message);
    }

    public function save()
    {
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

        $this->record['id_periode_pendanaan'] = $this->idPeriodePendanaan;
        $this->record['id_sumber_pendanaan'] = $this->idSumberPendanaan;
        $this->record['id_dokumen_thumbnail'] = $this->idDokumenThumbnail;

        $this->dispatch('show-loading');

        parent::save();
    }

    protected function loadData()
    {
        $isCanAction = $this->validatePermission('post');

        if (!$isCanAction) {
            abort(403);
        }

        // [Start] proses get rule dari model (dan set value jika edit)
        if ($this->edit) {
            WebRequest::validateId($this->edit);

            // get data
            $data = $this->service->show($this->edit);

            // cek jika edit maka ambil dari data
            if ($this->isInitEdit) {
                $this->idSumberPendanaan = $data['id_sumber_pendanaan'] ?? null;
            } else { // jika tidak edit maka ambil dari pilihan user yg terakhir
                $recSumberPendanaan = !empty($this->selectValue($this->record['id_sumber_pendanaan'])) ? $this->selectValue($this->record['id_sumber_pendanaan']) : null;
                $recIdDokumenThumbnail = !empty($this->selectValue($this->record['id_dokumen_thumbnail'])) ? $this->selectValue($this->record['id_dokumen_thumbnail']) : null;

                $this->idSumberPendanaan = $this->idSumberPendanaan ?? $recSumberPendanaan ?? null;
                $this->idDokumenThumbnail = $this->idDokumenThumbnail ?? $recIdDokumenThumbnail ?? null;
            }

            if ($this->idSumberPendanaan) {
                $totalPendanaanDanMataUang = (new SumberPendanaanService())->getTotalPendanaanDanMataUang($this->idSumberPendanaan);
                $pengelolaBantuan = (new SumberPendanaanService())->getPengelolaBantuan($this->idSumberPendanaan);
            }

            $this->totalPendanaan = $totalPendanaanDanMataUang->total_pendanaan ?? null;
            $this->mataUangSumberPendanaan = $totalPendanaanDanMataUang->mata_uang ?? null;
            $this->pengelolaPendanaan = $pengelolaBantuan->nama_unit ?? null;

            $this->fields = $this->defineFormFields();
            $this->data = WebController::buildFormCard(
                $this->fields,
                $this->model,
                data: $data
            );
        } else {
            $this->fields = $this->defineFormFields();
            $this->data = WebController::buildFormCard(
                $this->fields,
                $this->model
            );
        }
        // [End] proses get rule dari model (dan set value jika edit)

        // Set wire:model (dan selected value ketika options)
        $this->initForm();

        // Set value ke record ketika edit
        $this->loadRecord();
    }

    /**
     * Jalan setelah loadData()
     *
     * @return void
     */
    protected function beforeRender()
    {
        $this->dispatch( 'hide-loading');
    }

    /**
     * Proses set wire:model (dan selected ketika options)
     *
     * @return void
     */
    protected function initForm()
    {
        // Menambahkan wire:model
        foreach ($this->data as $k => $item) {
            if (empty($item['wire:model'])) {
                $this->data[$k] = [...$item, 'wire:model' => 'record.' . $item['field']];
            }

            $record = $this->record[$item['field']] ?? null;

            // Jika field tidak ada di record maka tambahkan dengan nilai null
            if (!array_key_exists($item['field'], $this->record)) {
                $this->record[$item['field']] = null;
                if (!empty($item['is_array_value'])) {
                    $this->record[$item['field']] = [];
                }
            }

            // Jika options seperti select, maka pertahankan value yang dipilih ketika rerender
            // TODO: Sementara masih menggunakan cara untuk mengakali choices
            if (isset($item['options'])) {
                $record = $this->selectValue($record);

                // Jika valuenya tidak array atau multiple maka pertahankan value yang dipilih ketika rerender
                if (isset($record) && !is_array($record)) {
                    $this->data[$k] = array_merge($this->data[$k], ['selected' => $record, 'value' => $record]);
                }
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
        $this->record['id_periode_pendanaan'] = $this->prevPeriodePendanaan;

        if (empty($this->edit)) {
            if (empty($this->record['id_periode_pendanaan'])) {
                $this->record['id_periode_pendanaan'] = $this->idPeriodePendanaan;
            }

            return;
        }

        foreach ($this->data as $keyData => $row) {
            if ($row['field'] == 'total_pendanaan' && empty($this->totalPendanaan)) {
                $this->data[$keyData]['value'] = null;
                continue;
            }
            if ($row['field'] == 'id_pengelola_bantuan' && empty($this->pengelolaPendanaan)) {
                $this->data[$keyData]['value'] = null;
                continue;
            }

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

    #[Renderless]
    public function updatedIdPeriodePendanaan($value)
    {
        $this->dispatch('show-loading');

        $value = $this->selectValue($value);
        $this->idPeriodePendanaan = !empty($value) ? $value : null;
    }

    #[Renderless]
    public function updatedIdSumberPendanaan($value)
    {
        $this->dispatch('show-loading');

        $value = $this->selectValue($value);
        $this->idSumberPendanaan = !empty($value) ? $value : null;

        if (empty($this->idSumberPendanaan)) {
            $this->totalPendanaan = null;
            $this->mataUangSumberPendanaan = null;
            $this->pengelolaPendanaan = null;
            return;
        }

        $totalPendanaanDanMataUang = (new SumberPendanaanService())->getTotalPendanaanDanMataUang($this->idSumberPendanaan);
        $this->totalPendanaan = $totalPendanaanDanMataUang->total_pendanaan;
        $this->mataUangSumberPendanaan = $totalPendanaanDanMataUang->mata_uang;

        $pengelolaBantuan = (new SumberPendanaanService())->getPengelolaBantuan($this->idSumberPendanaan);
        $this->pengelolaPendanaan = $pengelolaBantuan->nama_unit;
    }

    #[Computed]
    private function getSumberPendanaanOptions(array $excludeIdSumberPendanaan = null)
    {
        return SumberPendanaan::sumberPendanaanOptions($this->idPeriodePendanaan ?? null, $excludeIdSumberPendanaan);
    }
}
