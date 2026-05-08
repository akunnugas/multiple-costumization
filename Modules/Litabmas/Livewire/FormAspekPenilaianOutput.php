<?php

namespace Modules\Litabmas\Livewire;

use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Livewire\CreateEditComponent;
use Modules\Core\Livewire\Traits\ChoicesMultiple;
use Modules\Litabmas\Models\AspekPenilaianOutputPertanyaan;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Services\AspekPenilaianOutputService;
use Modules\Core\Helpers\Relation;
use Modules\Core\Helpers\Cstr;


class FormAspekPenilaianOutput extends CreateEditComponent
{
    use LitabmasViewData, ChoicesMultiple;

    public $fields;

    public $jumlahOpsiJawaban = 2;
    public $maxOpsiJawaban = 4;
    public array $jawabanPenilaianOutput = [];
    public array $oldRecordJawabanPenilaianOutput = [];

    public $prevPeriodePendanaan;

    #[Renderless]
    public function loadService()
    {
        $this->service = new AspekPenilaianOutputService;
    }

    #[Renderless]
    public function loadModel()
    {
        $this->model = AspekPenilaianOutputPertanyaan::class;
    }

    /**
     * Dipanggil sekali ketika component terbuat.
     *
     * @return void
     */
    public function mount()
    {
        parent::mount();

        $this->processGetFirstDataAndAttribute();
    }

    /**
     * Dipanggil ketika setiap awal request.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->processGetFirstDataAndAttribute();
    }

    protected function successUrlAfterSave()
    {
        return route('litabmas.aspek-penilaian-output.index', ['filter[id_periode_pendanaan]' => $this->prevPeriodePendanaan]);
    }

    public function addOpsiJawaban()
    {
        // add empty JawabanPenilaianOutput
        $this->jawabanPenilaianOutput[] = ['no' => ++$this->jumlahOpsiJawaban];
    }

    public function deleteOpsiJawaban($no)
    {
        // Set records to $jawabanPenilaianOutput
        $this->jawabanPenilaianOutput = collect($this->record['jawaban_penilaian_output'] ?? [])
            ->map(function ($jawabanRecord, $noRecord) {
                return ['no' => $noRecord, 'jawaban_penilaian_output' => $jawabanRecord];
            })->toArray();

        // Delete JawabanPenilaianOutput
        $this->jawabanPenilaianOutput = array_filter($this->jawabanPenilaianOutput, function ($jawaban) use ($no) {
            return $jawaban['no'] != $no;
        });


        // hitung ulang no dari jawabanPenilaianOutput dan record jawaban_penilaian_output
        $no = 1;
        $this->record['jawaban_penilaian_output'] = [];
        foreach ($this->jawabanPenilaianOutput as $key => $jawaban) {
            $this->jawabanPenilaianOutput[$key]['no'] = $no;
            $this->record['jawaban_penilaian_output'][$no] = $jawaban['jawaban_penilaian_output'] ?? null;

            $no++;
        }

        // reindex array jawabanPenilaianOutput ke 0, 1, 2, 3, dst
        $this->jawabanPenilaianOutput = array_values($this->jawabanPenilaianOutput);
        // hitung jumlah opsi jawaban
        $this->jumlahOpsiJawaban = count($this->jawabanPenilaianOutput);
    }

    /**
     * Digunakan ketika membutuhkan custom validasi.
     * Method ini jalan sebelum validasi model WebRequest::validateData() yg ada di dalam proses save()
     *
     * @return bool
     */
    public function customValidationBeforeSave(): bool
    {
        $this->alert = null;

        if ($this->edit) {
            //mapping array exist by this->jawabanPenilaianOutput
            $existJawaban = [];
            foreach ($this->jawabanPenilaianOutput as $jawaban) {
                $existJawaban[$jawaban['no']] = $jawaban['jawaban_penilaian_output'];
            }

            // get old record
            $oldRecord = $this->model::find($this->edit);

            // Check if any of the specified fields have changed
            $fieldsToCheck = [
                'pertanyaan_penilaian_output',
            ];

            $hasChanges = Cstr::isArrayDifferent($oldRecord->toArray(), $this->record, $fieldsToCheck);

            //compare checked output and outcome
            foreach ($this->record['jawaban_penilaian_output'] as $no => $statusPenilaian) {
                if ($existJawaban[$no] != $statusPenilaian) {
                    $hasChanges = true;
                    break;
                }
            }

            if ($hasChanges) {

                $relationsForCheck = ['penilaianReviewerOutputBersama'];

                // Check if datua has relation
                $hasRelations = Relation::hasRelationsData($oldRecord, $relationsForCheck);
                if (!empty($hasRelations['status'])) {
                    $this->alert = [
                        'type' => 'error',
                        'message' => "Kriteria Penilaian Luaran tidak bisa diubah karena sudah memiliki Jawaban.",
                    ];
                    $this->dispatch('hide-loading');
                    return false;
                }
            }
        }

        // validasi opsi jawaban: harus ada minimal 2 opsi jawaban dan maksimal 4 opsi jawaban dan tidak boleh ganjil
        if ($this->jumlahOpsiJawaban < 2 || $this->jumlahOpsiJawaban > 4 || $this->jumlahOpsiJawaban % 2 != 0) {
            $this->dispatch('hide-loading');

            $this->alert = [
                'type' => 'error',
                'message' => "Pilihan jawaban harus berjumlah 2 atau berjumlah 4."
            ];

            $this->dispatch('scroll-to-top');
            return false;
        }

        // cek required jawaban_penilaian_output berdasarkan jumlah $jawabanPenilaianOutput
        $errorField = [];
        foreach ($this->jawabanPenilaianOutput as $jawaban) {
            $value = $this->record['jawaban_penilaian_output'][$jawaban['no']] ?? null;
            if (empty($value)) {
                $this->dispatch('hide-loading');

                $errorField[] = 'jawaban_penilaian_output[' . $jawaban['no'] . ']';
            }
        }

        if (!empty($errorField)) {
            foreach ($errorField as $field) {
                $this->addError($field, 'jawaban tidak boleh kosong.');
            }
            return false;
        }

        $this->mergeData['jawaban_penilaian_output'] = $this->record['jawaban_penilaian_output'] ?? null;

        return true;
    }

    protected function loadData()
    {
        $isCanAction = $this->validatePermission('post');

        if (!$isCanAction) {
            abort(403);
        }

        $this->fields = $this->defineFormFields();

        // [Start] proses get rule dari model (dan set value jika edit)
        if ($this->edit) {
            WebRequest::validateId($this->edit);

            $data = $this->service->show($this->edit);

            $this->data = WebController::buildFormCard(
                $this->fields,
                $this->model,
                data: $data
            );
        } else {
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

        // Jika menggunakan trait ChoicesMultiple maka load choices
        if (method_exists($this, 'loadChoices')) {
            if (empty($this->choices)) {
                return;
            }

            foreach ($this->data as $i => $card) {
                $items = $card['items'] ?? [];
                foreach ($items as $j => $item) {
                    $field = $item['field'] ?? null;
                    if ($field !== null && array_key_exists($field, $this->choices)) {
                        $this->data[$i]['items'][$j]['values'] = $this->choices[$field];
                    }
                }
            }
        }
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
            return;
        }

        foreach ($this->data as $row) {
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

    protected function defineFormFields()
    {
        $fields = [
            [
                'field' => 'id_periode_pendanaan',
                'label' => 'Periode Pendanaan',
                'required' => true,
                'disabled' => true,
                'options' => \Modules\Litabmas\Models\PeriodePendanaan::options(),
                'selected' => $this->getPeriodePendanaan(),
            ],
            [
                'field' => 'pertanyaan_penilaian_output',
                'label' => 'Pertanyaan',
                'required' => true,
                'control' => 'textarea',
                'wire:model' => 'record.pertanyaan_penilaian_output',
            ],
        ];

        // get jawaban penilaian output
        $examplePlaceholder = ['Baik', 'Kurang Baik', 'Cukup Baik', 'Sangat Baik'];
        if (empty($this->edit) && empty($this->jawabanPenilaianOutput)) {
            $this->jawabanPenilaianOutput = [
                ['no' => 1],
                ['no' => 2],
            ];
        }
        if (!empty($this->edit) && empty($this->record['jawaban_penilaian_output'])) {
            foreach ($this->oldRecordJawabanPenilaianOutput as $jawaban) {
                $this->record['jawaban_penilaian_output'][$jawaban['no']] = $jawaban['jawaban_penilaian_output'];
            }
            $this->jumlahOpsiJawaban = count($this->oldRecordJawabanPenilaianOutput);
            $this->jawabanPenilaianOutput = $this->oldRecordJawabanPenilaianOutput;
        }
        $jawabanPenilaianOutput = !empty($this->jawabanPenilaianOutput)
            ? $this->jawabanPenilaianOutput
            : $this->oldRecordJawabanPenilaianOutput;

        // looping set to $fields
        foreach ($jawabanPenilaianOutput as $i => $jawaban) {
            $no = $jawaban['no'];
            $fields[] = [
                'no' => $no, // 'no' => '1', '2', '3', '4
                'label' => 'Jawaban ' . $no,
                'name' => 'jawaban_penilaian_output[' . $no . ']',
                'field' => 'jawaban_penilaian_output',
                'placeholder' => 'Contoh: ' . $examplePlaceholder[$i] ?? null,
                'is_opsi_jawaban' => true,
                'is_array_value' => true,
                'required' => true,
                'wire:model' => 'record.jawaban_penilaian_output.' . $no,
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

        $dataPertanyaan = $this->service->show($this->edit);
        $dataJawaban = $dataPertanyaan->jawaban()->get();

        $this->record['pertanyaan_penilaian_output'] = $dataPertanyaan->pertanyaan_penilaian_output;

        $no = 1;
        foreach ($dataJawaban as $jawaban) {
            $this->oldRecordJawabanPenilaianOutput[] = [
                'no' => $no++,
                'jawaban_penilaian_output' => $jawaban->jawaban_penilaian_output,
            ];
        }
    }

    private function getPeriodePendanaan()
    {
        $prevUrl = url()->previous();
        $queryString = parse_url($prevUrl)['query'] ?? '';
        $params = [];
        parse_str($queryString, $params);
        $filterPeriode = $params['filter']['id_periode_pendanaan'] ?? null;

        $firstPeriode = key(PeriodePendanaan::options());

        $this->prevPeriodePendanaan = $this->prevPeriodePendanaan ?? $filterPeriode ?? $firstPeriode;

        return $this->prevPeriodePendanaan;
    }
}
