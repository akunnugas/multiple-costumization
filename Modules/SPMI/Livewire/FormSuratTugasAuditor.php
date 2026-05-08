<?php

namespace Modules\SPMI\Livewire;

use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Modules\Core\Livewire\CreateEditComponent;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\SuratTugasAuditor;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;
use Modules\SPMI\Services\SuratTugasAuditorManagementService;

class FormSuratTugasAuditor extends CreateEditComponent
{
    use SpmiViewData, WithFileUploads;

    public $isShowFormPerson = false;
    public $isHasErrorStudyProgram = false;
    public $addedFormPerson = [];
    public $addedFormPersonDetail = [];

    // Data program studi
    public $studyPrograms = [];
    public $filteredStudyPrograms = [];
    public $selectedStudyProgram = null;

    // Data auditee
    public $selectedAuditee = [];
    public $positionAuditee = SuratTugasAuditorPegawai::POSITION_AUDITEE;
    public $positionMemberAuditee = SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE;

    // Data personil yang tersedia untuk dipilih
    public $availablePersonBySKOptions = [];
    public $availablePersonOptions = [];
    public $selectedPersonOptions = [];
    public $rawPersonOptions = [];

    // Data pilihan posisi
    public $positionOptions = SuratTugasAuditorPegawai::POSITIONS;

    // Data personil yang telah tersimpan
    public $savedPersonData = [];

    // Data untuk ditampilkan di list
    public $listPersonData = [];

    public $isEditForm = false;
    public $isHasUnsavedChange = false;

    public $temporarySavedPersonData = [];

    public $tempPenilaianAudit = [];

    public $tempAuditPeriod = null;

    public function loadService()
    {
        $this->service = new SuratTugasAuditorManagementService();
    }

    public function loadModel()
    {
        $this->model = SuratTugasAuditor::class;
    }

    public function mount()
    {
        parent::mount();

        $this->studyPrograms = UnitKerja::optionByType([UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI], isOnlyActive: true);

        // Load data personil audit ketika edit
        if ($this->edit) {
            $this->tempAuditPeriod = $this->model::find($this->edit)->id_audit_periode;

            $this->renderPersons();

            // get data penilaian audit unit
            $this->tempPenilaianAudit = PenilaianAudit::where('id_audit_periode', $this->tempAuditPeriod)->distinct()->pluck('id_unit')->toArray();
        }
    }

    private function renderPersons() {
        $savedPersons = $this->service->showPersons($this->edit);

        foreach ($savedPersons as $key => $savedPerson) {
            $prefixTitle = trim($savedPerson['gelar_depan']);
            $suffixTitle = trim($savedPerson['gelar_belakang']);
            $name = $savedPerson['person_name'];

            $this->savedPersonData[] = [
                'person_id' => $savedPerson['id_personil'],
                'id_unit' => $savedPerson['id_unit'],
                'position' => $savedPerson['posisi'],
                'detail' => [
                    'person' => "{$prefixTitle}{$name}{$suffixTitle} ({$savedPerson['nip']})",
                    'study_program' => $savedPerson['nama_unit'],
                    'position' => SuratTugasAuditorPegawai::POSITIONS[$savedPerson['posisi']]
                ],
                'timestamp' => time() + ($key * 3600)
            ];
        }
    }


    protected function loadData()
    {
        if (!$this->isShowFormPerson) {
            $this->listPersonData = $this->mappedPersonByStudyProgram($this->savedPersonData);
        }

        parent::loadData();
    }

    protected function defineFormFields()
    {
        return [
            ['field' => 'id_audit_periode', 'wire:change.prevent' => 'onChangeAuditPeriode'],
            ['field' => 'nomor_surat_tugas', 'label' => 'Nomor ST Auditor'],
            ['field' => 'tanggal_surat_tugas', 'label' => 'Tanggal ST Diterbitkan'],
            ['field' => 'tanggal_mulai', 'label' => 'Tanggal Mulai Berlaku'],
            ['field' => 'tanggal_selesai', 'label' => 'Tanggal Selesai Berlaku'],
            ['field' => 'id_dokumen', 'label' => 'Dokumen ST Auditor'],
        ];
    }

    public function showFormPerson($isShowFormPerson = true)
    {
        // Ketika state tambah data personil diaktifkan, maka load data personil yang tersedia
        if ($isShowFormPerson) {
            $this->loadFormPersonData();
            $this->loadFormAuditeeData();

            // Jika kosong, maka tambahkan form personil default
            if (empty($this->addedFormPerson)) {
                $this->addNewPersonForm();
                $this->addNewPersonForm(1);
            }

            if (!$this->isEditForm) {
                $this->dispatch('scroll-to-bottom');
            }

            $this->isShowFormPerson = true;
            return;
        }

        // Jika mode edit maka trigger fungsi cancelEdit
        if ($this->isEditForm) {
            $this->cancelEdit();
        }

        // Ketika state tambah data personil dinonaktifkan, maka bersihkan data personil yang tersedia
        $this->isShowFormPerson = false;
        $this->cleaningFormPerson();
        return;
    }

    public function editPersonData($studyProgramId)
    {
        $this->isEditForm = true;

        // Otomatis pilih program studi yang akan diedit
        $this->selectedStudyProgram = $studyProgramId;

        // Ambil semua data yang tersimpan berdasarkan program studi
        $editData = array_filter($this->savedPersonData, function ($savedPerson) use ($studyProgramId) {
            $savedStudyProgramId = $this->selectValue($savedPerson['id_unit']);
            return $savedStudyProgramId == $studyProgramId;
        });
        // Pindahkan data personil yang tersimpan ke form personil
        $this->addedFormPerson = array_values($editData);

        // Pindahkan ke temporary agar bisa dikembalikan jika dibatalkan
        $this->temporarySavedPersonData = $this->addedFormPerson;

        // Hapus data personil tersimpan yang telah dipindahkan
        $this->savedPersonData = array_filter($this->savedPersonData, function ($savedPerson) use ($studyProgramId) {
            $savedStudyProgramId = $this->selectValue($savedPerson['id_unit']);
            return $savedStudyProgramId != $studyProgramId;
        });

        $this->savedPersonData = array_values($this->savedPersonData);

        // Tambahan form personil
        $this->showFormPerson();

        // Sort berdasarkan posisi
        usort($this->addedFormPerson, function ($a, $b) {
            return $a['position'] <=> $b['position'];
        });

        // Pindahkan data personil yang tersedia ke data personil yang dipilih
        foreach ($this->addedFormPerson as $i => $addedPerson) {
            $this->movePersonToSelected($i);
        }

        $this->listPersonData = $this->mappedPersonByStudyProgram(array_merge($this->savedPersonData, $this->addedFormPerson));
    }

    public function deletePersonDataByStudyProgram($studyProgramId)
    {
        // Hapus data personil yang tersimpan berdasarkan program studi
        $this->savedPersonData = array_filter($this->savedPersonData, function ($savedPerson) use ($studyProgramId) {
            $savedStudyProgramId = $this->selectValue($savedPerson['id_unit']);
            return $savedStudyProgramId != $studyProgramId;
        });

        $this->savedPersonData = array_values($this->savedPersonData);
    }

    public function cancelEdit()
    {
        $this->isEditForm = false;

        // Pindahkan data form personil ke data personil yang tersimpan, karena tidak jadi diedit
        $this->savedPersonData = array_merge($this->savedPersonData, $this->temporarySavedPersonData);
    }

    public function addNewPersonForm($isAuditee = 0)
    {

        $position = SuratTugasAuditorPegawai::POSITION_LEAD;
        if ($isAuditee){
            $position = SuratTugasAuditorPegawai::POSITION_AUDITEE;
        }

        // Pengecekan posisi ketua
        $isExistLeadPosition = !!array_filter($this->addedFormPerson, function ($addedPerson) use ($position) {
            return $addedPerson['position'] == $position;
        });

        if ($isExistLeadPosition){
            $position = $isAuditee ? SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE : SuratTugasAuditorPegawai::POSITION_MEMBER;
        }

        // Penambahan form personil baru
        $this->addedFormPerson[] = [
            'person_id' => null,
            'id_unit' => null,
            'position' => $position,
            'detail' => [
                'person' => null,
                'study_program' => null,
                'position' => null
            ],
            'timestamp' => time() + (count($this->savedPersonData) * 3600),
        ];
    }

    public function deletePersonForm($index)
    {
        $deletedPerson = $this->addedFormPerson[$index];
        $deletedPersonId = $this->selectValue($deletedPerson['person_id']);
        $this->movePersonToAvailable($deletedPersonId);
        unset($this->addedFormPerson[$index]);
        $this->addedFormPerson = array_values($this->addedFormPerson);
    }

    public function deletePersonSaved($id, $studyProgramId = null)
    {
        // Hapus data personil jika sudah tersimpan
        $this->savedPersonData = array_filter($this->savedPersonData, function ($addedPerson) use ($id, $studyProgramId) {
            $addedPersonId = $this->selectValue($addedPerson['person_id']);
            $addedStudyProgramId = $this->selectValue($addedPerson['id_unit']);
            return !(($addedPersonId == $id) && ($addedStudyProgramId == $studyProgramId));
        });

        $this->savedPersonData = array_values($this->savedPersonData);
        return;
    }

    public function onChangeAuditPeriode()
    {
        // Jika periode audit berubah, maka data personil yang tersedia juga berubah
        if ($this->isShowFormPerson) {
            $this->reset('selectedPersonOptions', 'availablePersonOptions', 'addedFormPerson');
            $this->showFormPerson(false);
        }

        $this->reset('savedPersonData');
    }

    public function updatedSelectedStudyProgram($value)
    {
        $studyProgramId = $this->selectValue($value);
        $auditPeriod = $this->selectValue($this->record['id_audit_periode']);

        $isRegisteredStudyProgram = $this->service->checkRegisteredStudyProgram($studyProgramId, $auditPeriod, $this->edit);

        if ($isRegisteredStudyProgram) {
            $this->isHasErrorStudyProgram = true;

            $this->throwValidationError(
                'study-program',
                "Unit Kerja ini sudah digunakan sebelumnya. Silakan pilih unit kerja lain."
            );
        }

        // Lanjutkan validasi di form yang belum tersimpan
        $savedStudyProgramIds = array_map(function ($savedPerson) {
            return $this->selectValue($savedPerson['id_unit']);
        }, $this->savedPersonData);

        $savedStudyProgramIds = array_unique($savedStudyProgramIds);

        if (in_array($studyProgramId, $savedStudyProgramIds)) {
            $this->isHasErrorStudyProgram = true;

            $this->throwValidationError(
                'study-program',
                "Unit kerja ini sudah digunakan sebelumnya. Silakan pilih unit kerja lain."
            );
        }

        $this->selectedStudyProgram = $studyProgramId;
        $this->isHasErrorStudyProgram = false;
    }

    public function updatedSelectedAuditee($value)
    {
        $auditeeId = $this->selectValue($value);
        $this->selectedAuditee = $auditeeId;
        $studyProgramId = $this->selectValue($this->selectedStudyProgram);

        $index = array_search(SuratTugasAuditorPegawai::POSITION_AUDITEE, array_column($this->addedFormPerson, 'position'));

        // Jika auditee sudah ada, maka hapus index auditee yang lama
        if ($index !== false) {
            $recentAuditeeId = $this->addedFormPerson[$index]['person_id'];
            unset($this->addedFormPerson[$index]);
            $this->addedFormPerson = array_values($this->addedFormPerson);
        }

        if (!empty($recentAuditeeId)) {
            $this->movePersonToAvailable($recentAuditeeId);
        }

        if (!empty($auditeeId)) {
            $this->addedFormPerson = [
                ...$this->addedFormPerson,
                [
                    'person_id' => $auditeeId,
                    'id_unit' => $studyProgramId,
                    'position' => SuratTugasAuditorPegawai::POSITION_AUDITEE,
                    'detail' => [
                        'person' => $this->availablePersonOptions[$auditeeId] ?? null,
                        'study_program' => $studyProgramId,
                        'position' => SuratTugasAuditorPegawai::POSITIONS[SuratTugasAuditorPegawai::POSITION_AUDITEE]
                    ],
                    'timestamp' => time() + (count($this->savedPersonData) * 3600),
                ]
            ];

            $newIndex = array_search(SuratTugasAuditorPegawai::POSITION_AUDITEE, array_column($this->addedFormPerson, 'position'));
            $this->movePersonToSelected($newIndex);
        }
    }

    public function savePersonData()
    {
        // Ambil semua program studi yang tersimpan
        $savedStudyProgramIds = array_map(function ($savedPerson) {
            return $this->selectValue($savedPerson['id_unit']);
        }, $this->savedPersonData);

        $savedStudyProgramIds = array_unique($savedStudyProgramIds);

        // Harus memilih program studi terlebih dahulu
        $studyProgramId = $this->selectValue($this->selectedStudyProgram);
        if (empty($studyProgramId)) {
            $this->throwValidationError(
                'study-program',
                'Mohon pilih unit kerja terlebih dahulu.'
            );
        }

        // Harus memilih auditee terlebih dahulu
        // $auditeeId = $this->selectValue($this->selectedAuditee);
        // if (empty($auditeeId)) {
        //     $this->throwValidationError(
        //         'auditee',
        //         'Mohon pilih auditee terlebih dahulu.'
        //     );
        // }

        // Cek Jika program studi sudah ada di data personil yang tersimpan, maka tampilkan error
        if (in_array($studyProgramId, $savedStudyProgramIds)) {
            $this->throwValidationError(
                'study-program',
                'Unit kerja ini sudah digunakan sebelumnya. Silakan pilih unit kerja lain.'
            );
        }

        $errors = [];

        $studyProgram = $this->studyPrograms[$studyProgramId];

        $leadPosition = 0;
        $leadAuditeePosition = 0;

        // Persiapan simpan data personil dan validasi isian
        foreach ($this->addedFormPerson as $k => $addedPerson) {
            $personId = $this->selectValue($addedPerson['person_id']);
            $positionKey = $this->selectValue($addedPerson['position']);
            $studyProgramId = $this->selectValue($this->selectedStudyProgram);

            // NOTE: Sementara dinonaktifkan, karena ketua prodi bisa di pilih di surat tugas
            // $isStudyProgramLead = !!array_filter($this->rawPersonOptions, function ($person) use ($personId, $studyProgramId) {
            //     return $person->id == $personId && $person->leader_study_program_id == $studyProgramId;
            // });

            // Jika ada data yang kosong, maka tampikan error
            if (empty($personId) || empty($positionKey)) {
                $errors["person-$k"] = 'Mohon lengkapi semua isian terlebih dahulu.';
                continue;
            }

            // Jika personil yang dipilih menjadi ketua program studi di program studi yang dipilih, maka tampilkan error
            // NOTE: Sementara dinonaktifkan, karena ketua prodi bisa di pilih di surat tugas
            // if ($isStudyProgramLead) {
            //     $errors["person-$k"] = "Personil ini sudah menjadi ketua program studi di program studi yang dipilih.";
            //     continue;
            // }

            $addedPerson['id_unit'] = $studyProgramId;

            $detail = [
                'person' => $this->selectedPersonOptions[$personId] ?? null,
                'study_program' => $this->studyPrograms[$studyProgramId] ?? null,
                'position' => $this->positionOptions[$positionKey] ?? null
            ];
            $addedPerson['detail'] = $detail;
            $addedPerson['position'] = $positionKey;
            $this->addedFormPerson[$k] = $addedPerson;

            if ($positionKey === SuratTugasAuditorPegawai::POSITION_LEAD) {
                $leadPosition++;
            }
            if ($positionKey === SuratTugasAuditorPegawai::POSITION_AUDITEE) {
                $leadAuditeePosition++;
            }

        }

        // Jika ada error, maka throw error
        if (!empty($errors)) {
            $this->throwValidationError(errors: $errors);
        }

        // Jika posisi ketua auditee > 1, maka tampilkan error
        if ($leadAuditeePosition > 1) {
            $this->throwValidationError(
                'study-program',
                'Ketua auditee tidak boleh lebih dari satu di unit kerja ini.'
            );
        }

        // Jika posisi ketua auditor > 1, maka tampilkan error
        if ($leadPosition > 1) {
            $this->throwValidationError(
                'study-program',
                'Ketua auditor tidak boleh lebih dari satu di unit kerja ini.'
            );
        }

        // Jika tidak ada posisi ketua auditee, maka tampilkan error
        if ($leadAuditeePosition == 0) {
            $this->throwValidationError(
                'study-program',
                'Harus ada satu ketua auditee di unit kerja ini.'
            );
        }

        // Jika tidak ada posisi ketua auditor, maka tampilkan error
        if ($leadPosition == 0) {
            $this->throwValidationError(
                'study-program',
                'Harus ada satu ketua auditor di unit kerja ini.'
            );
        }

        // Merge data personil yang tersimpan dengan data personil yang baru ditambahkan
        $this->savedPersonData = array_merge($this->savedPersonData, $this->addedFormPerson);

        // Set edit form menjadi false
        $this->isEditForm = false;
        $this->isHasUnsavedChange = true;

        $this->showFormPerson(false);
    }

    public function save()
    {
        // check change period
        if ($this->edit) {
            if ($this->tempAuditPeriod != $this->selectValue($this->record['id_audit_periode'])) {
                if (PenilaianAudit::where('id_audit_periode', $this->tempAuditPeriod)->exists()) {
                    $this->record['id_audit_periode'] = $this->tempAuditPeriod;

                    $this->renderPersons();

                    $this->throwValidationError('id_audit_periode', 'Perubahan data Surat Tugas gagal, data masih dijadikan referensi');
                }
            }
        }

        // Untuk validasi tambahan, Jika state tambah data personil aktif dan state edit
        // maka pindahkan data personil yang tersimpan ke data personil yang baru ditambahkan
        if ($this->isShowFormPerson) {
            $this->savedPersonData = array_merge($this->savedPersonData, $this->addedFormPerson);
        }

        // Merge data personil ke record ketika simpan form
        $this->mergeData['surat_tugas_auditor_pegawai'] = array_map(function ($item) {
            return [
                'person_id' => $this->selectValue($item['person_id']),
                'id_unit' => $this->selectValue($item['id_unit']),
                'position' => $this->selectValue($item['position']),
            ];
        }, $this->savedPersonData);

        parent::save();
    }

    public function onChangedPersonId($index, $recentPersonId = null)
    {
        // Trigger ketika data personil yang dipilih berubah
        // Pindahkan data personil yang tersedia ke data personil yang dipilih
        $this->movePersonToSelected($index);

        // Pindahkan data personil yang dipilih ke data personil yang tersedia
        $this->movePersonToAvailable($recentPersonId);
    }

    public function onChangedPosition($index)
    {
        $positionKey = $this->selectValue($this->addedFormPerson[$index]['position']);
        $this->addedFormPerson[$index]['position'] = $positionKey;
    }

    private function movePersonToSelected($index)
    {
        $changedPersonId = $this->selectValue($this->addedFormPerson[$index]['person_id']);

        // Jika value tidak kosong, maka pindahkan data personil yang tersedia ke data personil yang dipilih
        $availableOptions = $this->availablePersonOptions[$changedPersonId] ?? null;
        if (!empty($changedPersonId) && isset($availableOptions)) {
            $this->selectedPersonOptions[$changedPersonId] = $availableOptions;
            unset($this->availablePersonOptions[$changedPersonId]);
        }
    }

    private function movePersonToAvailable($recentPersonId)
    {
        // Jika value kosong, maka pindahkan data personil yang dipilih ke data personil yang tersedia
        $selectedOptions = $this->selectedPersonOptions[$recentPersonId] ?? null;
        if (empty($changedPersonId) && isset($selectedOptions)) {
            $this->availablePersonOptions[$recentPersonId] = $selectedOptions;
            unset($this->selectedPersonOptions[$recentPersonId]);
        }
    }

    private function loadFormAuditeeData()
    {
        // Ambil data person yang posisinya sebagai auditee
        $index = array_search(SuratTugasAuditorPegawai::POSITION_AUDITEE, array_column($this->addedFormPerson, 'position'));

        if ($index !== false) {
            $auditeePerson = $this->addedFormPerson[$index];
            $this->selectedAuditee = $auditeePerson['person_id'] ?? null;
        }
    }

    private function loadFormPersonData()
    {
        // Ambil periode audit yang dipilih
        $auditPeriod = $this->selectValue($this->record['id_audit_periode']);

        $availablePersonBySK = $this->service->showAvailablePersonBySK((int) $auditPeriod);
        $availablePerson = $this->service->showAvailablePerson();
        $this->rawPersonOptions = $availablePerson;
        $this->availablePersonBySKOptions = $availablePersonBySK;

        if (empty($availablePerson)) {
            $this->availablePersonOptions = [];
            return;
        }

        foreach ($availablePerson as $person) {
            $prefixTitle = trim($person->gelar_depan);
            $suffixTitle = trim($person->gelar_belakang);
            $nama = trim($person->nama);

            // Check if prefix title already ends with "."
            if (!empty($prefixTitle) && !str_ends_with($prefixTitle, '.')) {
                $prefixTitle .= '.';
            }

            // Check if suffix title already starts with "."
            if (!empty($suffixTitle)) {
                $suffixTitle = ', ' . $suffixTitle;
            }

            $prefixTitle .= ' ';

            $this->availablePersonOptions[$person->id] = "{$person->nip} - {$prefixTitle}{$nama}{$suffixTitle}";
        }
        return;
    }

    private function cleaningFormPerson()
    {
        // Bersihkan data
        $this->reset(
            'selectedStudyProgram',
            'availablePersonOptions',
            'selectedPersonOptions',
            'addedFormPerson',
            'isEditForm',
            'filteredStudyPrograms',
            'temporarySavedPersonData',
            'isHasErrorStudyProgram',
            'selectedAuditee'
        );
    }

    private function throwValidationError($column = null, $message = null, $errors = [])
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

    private function mappedPersonByStudyProgram($savedPersonData)
    {
        // Sort berdasarkan program studi
        usort($savedPersonData, function ($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });

        $mappedDataByStudyPrograms = [];

        // Mapping data berdasarkan program studi
        foreach ($savedPersonData as $item) {
            $studyProgramId = $this->selectValue($item['id_unit']);

            if (!isset($mappedDataByStudyPrograms[$studyProgramId])) {
                $mappedDataByStudyPrograms[$studyProgramId] = [
                    'id_unit' => $item['id_unit'],
                    'study_program' => $item['detail']['study_program'],
                    'isEdit' => $this->isEditForm && ($studyProgramId == $this->selectValue($this->selectedStudyProgram)),
                    'persons' => [],
                ];
            }

            $mappedDataByStudyPrograms[$studyProgramId]['persons'][] = [
                'person' => $item['detail']['person'],
                'position' => $item['detail']['position'],
                'position_key' => $item['position'],
            ];
        }

        $mappedDataByStudyPrograms = array_values($mappedDataByStudyPrograms);

        return $mappedDataByStudyPrograms;
    }
}
