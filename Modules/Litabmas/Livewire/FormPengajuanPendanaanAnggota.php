<?php

namespace Modules\Litabmas\Livewire;

use Illuminate\Support\Str;
use Livewire\Component;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\Mahasiswa;
use Modules\Core\Models\Pegawai;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\PerguruanTinggi;
use Modules\Core\Services\MahasiswaManagementService;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\Litabmas\Models\DosenEksternal;
use Modules\Litabmas\Models\PengajuanPendanaanAnggota;
use Modules\Litabmas\Models\SumberPendanaan;
use Modules\Litabmas\Services\DosenEksternalManagementService;
use Modules\Litabmas\Services\PengajuanPendanaanAnggotaService;
use Modules\Litabmas\Services\PengajuanPendanaanReviewerService;

class FormPengajuanPendanaanAnggota extends Component
{
    public $recordSavedAnggota;
    public $memberType = null;
    public array $state = [
        'add' => false,
        'edit' => false,
        'onEditBiodataId' => null,
        'memberType' => null
    ];

    public $apakahButuhApproveSemuaAnggota;
    public $maksimalAnggota;

    public $dosenOpt;
    public $mahasiswaOpt;

    public $staticAlertAnggota;

    public $record;
    public $fields = [];
    public $idPengajuanPendanaan;
    public $otherBiodataIds = [];
    public $idProdi = null;
    public $fieldDosenEksternal = [];
    public $selectedIdProdi;
    public $searchResultsProdi = [];
    public $searchResultsDosen = [];
    public $searchResultsDosenExternal = [];
    public $searchResultsPerguruanTinggi = [];
    public $searchResultsMahasiswa = [];
    public $externalDosenHaveAccount = null;
    public $dosenEksternalData = [];
    protected $listeners = ['autocomplete'];

    /**
     * Dipanggil sekali ketika component terbuat.
     *
     * @return void
     */
    public function mount()
    {
        $idSumberPendanaan = $this->selectValue($this->record['id_sumber_pendanaan']);

        $sumberPendanaan = SumberPendanaan::find($idSumberPendanaan);

        // yang tidak memiliki sisa kuota mendaftar
        $biodataIdsAnggotaTidakMemilikiKuota = (new PengajuanPendanaanAnggotaService())->getDaftarIdBiodataAnggotaByKuotaMendaftar($sumberPendanaan->id_periode_pendanaan, $this->idPengajuanPendanaan, false);

        // reviewer proposal lain
        $reviewerAdministrasiBiodataIds = (new PengajuanPendanaanReviewerService())->getDaftarIdBiodataReviewerBySumberPendanaan($idSumberPendanaan);

        // merge jadi 'tdk bisa menjadi anggota'
        $this->otherBiodataIds = array_merge($biodataIdsAnggotaTidakMemilikiKuota, $reviewerAdministrasiBiodataIds);
    }

    /**
     * Dipanggil ketika setiap awal request.
     *
     * @return void
     */
    public function boot()
    {
        $this->staticAlertAnggota = null;
    }

    public function render()
    {
        $this->reOrderRecordSavedAnggota();

        $this->dispatch('hide-loading');

        $this->fields = $this->defineFormFields();

        $this->initForm();

        return view('litabmas::livewire.pengajuan-pendanaan.form-anggota');
    }

    public function updatingMemberType($value)
    {
        $this->memberType = $value;
        $this->dispatch('hide-loading');
    }
    public function updatedIdProdi($value)
    {
        $this->dispatch('show-loading');

        $value = $this->selectValue($value);
        $this->idProdi = !empty($value) ? $value : null;
    }

    public function autocomplete($id, $searchTerm)
    {
        // get current biodata id from record
        $currentBiodataIdFromRecord = array_map(function ($item) {
            return $item['id_biodata'];
        }, $this->recordSavedAnggota);
        $otherBiodataIds = array_merge($this->otherBiodataIds, $currentBiodataIdFromRecord);

        // remove selected biodata id from other biodata ids
        if ($this->state['edit']) { // agar bisa mendapatkan value dan namanya
            $otherBiodataIds = array_diff($otherBiodataIds, [$this->state['onEditBiodataId']]);
        }

        // add diri sendiri (di exclude)
        $otherBiodataIds = array_merge($otherBiodataIds, [auth()->user()?->biodata?->id]);

        if ($id == 'idProdi') {
            $this->searchResultsProdi = $this->searchProdi($searchTerm);
            $this->dispatch('searchResults', searchResults: $this->searchResultsProdi);
        }
        if ($id == 'idDosen') {
            //dispatch searchResults
            $this->searchResultsDosen = $this->searchDosen($searchTerm, excludeId: $otherBiodataIds);
            $this->dispatch('searchResults', searchResults: $this->searchResultsDosen);
        }
        if ($id == 'idPerguruanTinggi') {
            $this->searchResultsPerguruanTinggi = $this->searchPerguruanTinggi($searchTerm);
            $this->dispatch('searchResults', searchResults: $this->searchResultsPerguruanTinggi);
        }
        if ($id == 'idMahasiswa') {
            $this->searchResultsMahasiswa = $this->searchMahasiswa($searchTerm, excludeId: $otherBiodataIds);
            $this->dispatch('searchResults', searchResults: $this->searchResultsMahasiswa);
        }
        if ($id == 'idDosenEksternal') {
            //dispatch searchResults
            $this->searchResultsDosen = $this->searchDosen($searchTerm, excludeId: $otherBiodataIds, isExternal: true);
            $this->dispatch('searchResults', searchResults: $this->searchResultsDosen);
        }
    }

    public function stateAddAnggota()
    {
        $this->dispatch('show-loading');

        // validasi maksimal anggota
        if (count($this->recordSavedAnggota) >= $this->maksimalAnggota) {
            $this->staticAlertAnggota = [
                'type' => 'warning',
                'message' => 'Maksimal anggota yang bisa ditambahkan adalah ' . $this->maksimalAnggota . ' orang,
                    sesuai aturan Klaster Pendanaan yang dipilih.'
            ];
            return;
        }

        $this->state['add'] = true;
        $this->state['edit'] = false;
        $this->state['onEditBiodataId'] = null;
        $this->selectedIdProdi = null;
        $this->memberType = null;
        $this->record['member_type'] = null;

        $this->fields = $this->defineFormFields();

        $this->dispatch('add-member');
    }

    public function stateEditAnggota($index)
    {
        $this->dispatch('show-loading');

        $this->state['add'] = false;
        $this->state['edit'] = true;
        $this->state['onEditBiodataId'] = $this->recordSavedAnggota[$index]['id_biodata'] ?? null;

        $this->memberType = $this->recordSavedAnggota[$index]['memberType'] ?? null;
        $this->record['_temp_anggota']['id_biodata'] = $this->state['onEditBiodataId'];
        $this->record['member_type'] = $this->memberType;

        $biodata = Biodata::find($this->state['onEditBiodataId']);
        if ($this->memberType == PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL) {
            $biodata = Biodata::where('id', $biodata->id)->first();
            $pegawai = Pegawai::where('id_biodata', $biodata->id)->first();
            $this->selectedIdProdi = $pegawai->id_homebase_dosen ?? $pegawai->id_unit_kerja;
        } elseif ($this->memberType == PengajuanPendanaanAnggota::JENIS_MAHASISWA) {
            $this->selectedIdProdi = Mahasiswa::where('nim', $this->state['onEditBiodataId'])->first()?->id_unit;
            if (empty($this->selectedIdProdi)) {
                $biodata = Biodata::find($this->state['onEditBiodataId']);
                $mahasiswa = Mahasiswa::where('nim', $biodata->ref_key_mahasiswa)->first();
                $this->selectedIdProdi = $mahasiswa->id_unit;
            }
        }

        $this->fields = $this->defineFormFields();

        $this->dispatch('add-member');
    }

    public function saveAnggota($index = null)
    {
        // reset value
        $biodataId = $name = null;
        $isError = false;
        $isEdit = isset($index);

        $valueBiodataId = $this->selectValue($this->record['_temp_anggota']['id_biodata']);
        $biodataId = !empty($valueBiodataId) ? $valueBiodataId : null;

        $prodi = UnitKerja::find($this->selectedIdProdi);
        $jenjang = $prodi ? JenjangPendidikan::find($prodi->id_jenjang_pendidikan) : null;

        // set value sesuai Jenis Anggotanya
        if ($this->memberType == PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL) {
            // Dosen
            $isError = $this->validateDosen($index, $biodataId);

            //name get from search result where value is biodataId name is the label
            $name = $this->searchResultsDosen[$biodataId] ?? null;
            if ($jenjang && $prodi) {
                $name .= ' (' . $jenjang->kode_jenjang . '-' . $prodi->nama_unit . ')';
            }
        }

        if ($this->memberType == PengajuanPendanaanAnggota::JENIS_MAHASISWA) {
            // Mahasiswa
            $isError = $this->validateMahasiswa($index, $biodataId);

            // name get from search result where value is biodataId name is the label
            $name = $this->searchResultsMahasiswa[$biodataId] ?? null;
            if ($jenjang && $prodi) {
                $name .= ' (' . $jenjang->kode_jenjang . '-' . $prodi->nama_unit . ')';
            }

            // sync single mhs to db v2
            (new MahasiswaManagementService())->syncSingleMahasiswaFromSiakadv1([$biodataId]);
        }

        if ($this->memberType == PengajuanPendanaanAnggota::JENIS_DOSEN_EKSTERNAL) {
            if ($this->externalDosenHaveAccount == 'sudah' || $isEdit) {
                // Dosen
                $isError = $this->validateDosen($index, $biodataId);

                //name get from search result where value is biodataId name is the label
                $name = $this->searchResultsDosen[$biodataId] ?? null;
            }
            if ($this->externalDosenHaveAccount == 'belum') {
                $requiredField = ['nama_user', 'email_user', 'nip'];
                foreach ($requiredField as $field) {
                    if (empty($this->dosenEksternalData[$field])) {
                        $isError = true;
                        $this->addError($field, 'Field harus diisi');
                    }
                }

                if (!$isError) {
                    // mapping dosenEksternalData
                    $recordDosenEksternal = [
                        'nama_user' => $this->dosenEksternalData['nama_user'],
                        'email_user' => $this->dosenEksternalData['email_user'],
                        'nip' => $this->dosenEksternalData['nip'],
                        'id_biodata_pengusul' => auth()->user()?->biodata?->id,
                    ];

                    // Tambahkan id_perguruan_tinggi_luar hanya jika diisi
                    if (!empty($this->dosenEksternalData['id_perguruan_tinggi_luar']['value'])) {
                        $recordDosenEksternal['id_perguruan_tinggi_luar'] = $this->dosenEksternalData['id_perguruan_tinggi_luar']['value'];
                    }
                    // usulkan dosen eksternal
                    $usulanDosen = (new DosenEksternalManagementService)->usulkanDosenEksternal($recordDosenEksternal);
                    if ($usulanDosen instanceof \Modules\Core\Helpers\Error) {
                        $isError = true;
                        $this->addError('member_type', $usulanDosen->message);
                    } else {
                        $biodataId = $usulanDosen->id_biodata;
                        $name = $recordDosenEksternal['nip'] . ' - ' . $recordDosenEksternal['nama_user'];
                    }
                }
            }
        }

        // validasi maksimal anggota
        if (!$isEdit && count($this->recordSavedAnggota) >= $this->maksimalAnggota) {
            $isError = true;
            $this->staticAlertAnggota = [
                'type' => 'warning',
                'message' => 'Maksimal anggota yang bisa ditambahkan adalah ' . $this->maksimalAnggota . ' orang,
                    sesuai aturan Klaster Pendanaan yang Anda pilih.'
            ];
        }

        // kalo ada error
        if ($isError) {
            return;
        }

        // unset data lama kalo edit
        if ($isEdit) {
            $apakahUndanganDiterima = $this->recordSavedAnggota[$index]['apakah_undangan_diterima'];
            unset($this->recordSavedAnggota[$index]);
        } else {
            $apakahUndanganDiterima = null;
        }

        // set data baru
        $this->recordSavedAnggota[$biodataId] = [
            'memberType' => $this->memberType,
            'id_biodata' => $biodataId,
            'name' => $name,
            'apakah_undangan_diterima' => $apakahUndanganDiterima
        ];

        // dispatch to parent
        $this->dispatch(
            'save-member-from-child',
            newRecord: $this->recordSavedAnggota
        );

        // after dispatch set state
        $this->state['add'] = false;
        $this->state['edit'] = false;
        $this->state['onEditBiodataId'] = null;
        $this->selectedIdProdi = null;

        $this->memberType = null;
        $this->record['_temp_anggota'] = [];
    }

    private function reOrderRecordSavedAnggota()
    {
        // order by memberType lalu name
        $this->recordSavedAnggota = collect($this->recordSavedAnggota)
            ->sortBy('name')
            ->sortBy('memberType')
            ->all();
    }

    public function deleteAnggota($index)
    {
        unset($this->recordSavedAnggota[$index]);

        // dispatch to parent
        $this->dispatch(
            'save-member-from-child',
            newRecord: $this->recordSavedAnggota
        );
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

            if (!empty($item['withoutWireModel'])) {
                $item['wire:model'] = null;
            }

            // cek jika wire:model memiliki _temp_anggota
            $isTempMember = false;
            if (!empty($item['wire:model']) && Str::contains($item['wire:model'], '_temp_anggota')) {
                $isTempMember = true;
                $item['wire:model'] = 'record._temp_anggota.' . $item['field'];
            }

            if (!$isTempMember) {
                $record[$item['field']] = $record[$item['field']] ?? null;
            } else {
                $record['_temp_anggota'][$item['field']] = $record['_temp_anggota'][$item['field']] ?? null;
            }

            // TODO: Sementara masih menggunakan cara untuk mengakali choices
            if (isset($item['options'])) {
                $selectedValue = $this->selectValue(
                    $isTempMember ? $record['_temp_anggota'][$item['field']] : $record[$item['field']]
                );
                if ($selectedValue !== null && !is_array($selectedValue)) {
                    $item = array_merge($item, ['selected' => $selectedValue, 'value' => $selectedValue]);
                }
            }

            return $item;
        };

        foreach ($this->fields as $i => $item) {
            $this->fields[$i] = $processItem($item, $this->record);
        }
    }

    protected function defineFormFields()
    {
        $isEdit = $this->state['edit'];

        $fields = [
            [
                'field' => 'member_type',
                'label' => 'Jenis Anggota',
                'control' => 'radio',
                'selected' => $this->memberType ?? null,
                'required' => true,
                'inline' => false,
                'options' => [
                    PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL => 'Dosen Internal',
                    PengajuanPendanaanAnggota::JENIS_DOSEN_EKSTERNAL => 'Dosen Eksternal',
                    PengajuanPendanaanAnggota::JENIS_MAHASISWA => 'Mahasiswa',
                ],
                'wire:change' => 'memberType_change($event.target.value)',
            ],
        ];

        if ($this->memberType == PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL || $this->memberType == PengajuanPendanaanAnggota::JENIS_MAHASISWA) {
            $fields[] = [
                'field' => 'selectedIdProdi',
                'label' => 'Program Studi',
                'selected' => $this->selectedIdProdi ?? null,
                'options' => UnitKerja::optionByType([UnitKerja::STUDY_PROGRAM], false, false, true),
                'required' => true,
                'variant' => 'search',
                'wire:change' => 'unitKerja_change($event.target.value)',
            ];
        }

        if ($this->selectedIdProdi) {
            if ($this->memberType == PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL) {
                $this->searchResultsDosen = Biodata::getDosenInternal($this->selectedIdProdi ?? null, true);

                // hapus diri sendiri
                unset($this->searchResultsDosen[auth()->user()?->biodata?->id]);

                // hapus yang sudah dipilih
                $ignoreSelected = array_keys($this->recordSavedAnggota);

                if ($isEdit) {
                    $ignoreSelected = array_diff($ignoreSelected, [$this->state['onEditBiodataId']]);
                }

                foreach ($ignoreSelected as $key) {
                    unset($this->searchResultsDosen[$key]);
                }

                $fields[] = [
                    'field' => 'id_biodata',
                    'label' => 'Dosen',
                    'selected' => $this->state['onEditBiodataId'] ?? null,
                    'options' => $this->searchResultsDosen,
                    'required' => true,
                    'variant' => 'search',
                    'wire:model.change' => 'record._temp_anggota.id_biodata',
                ];
            }
            if ($this->memberType == PengajuanPendanaanAnggota::JENIS_MAHASISWA) {
                $prodi = UnitKerja::find($this->selectedIdProdi);

                $this->searchResultsMahasiswa = Mahasiswa::searchOptionV1(idunit: $prodi->kode_unit);

                // hapus yang sudah dipilih
                $ignoreSelected = array_keys($this->recordSavedAnggota);

                if ($isEdit) {
                    $ignoreSelected = array_diff($ignoreSelected, [$this->state['onEditBiodataId']]);
                }

                foreach ($ignoreSelected as $key) {
                    unset($this->searchResultsMahasiswa[$key]);
                }

                $fields[] = [
                    'field' => 'id_biodata',
                    'label' => 'Mahasiswa',
                    'selected' => $this->state['onEditBiodataId'] ?? null,
                    'options' => $this->searchResultsMahasiswa,
                    'required' => true,
                    'variant' => 'search',
                    'wire:model.change' => 'record._temp_anggota.id_biodata',
                ];
            }
        }
        if ($this->memberType == PengajuanPendanaanAnggota::JENIS_DOSEN_EKSTERNAL) {
            if (!$isEdit) {
                $fields[] = [
                    'field' => 'external_dosen_have_account',
                    'label' => 'Apakah sudah memiliki akun?',
                    'control' => 'radio',
                    'required' => true,
                    'inline' => true,
                    'withoutWireModel' => true,
                    'wire:model.change' => 'externalDosenHaveAccount',
                    'options' => [
                        'sudah' => 'Sudah',
                        'belum' => 'Belum',
                    ],
                ];
            }

            if ($this->externalDosenHaveAccount == 'belum') {
                $pengajuanDosenEksternalField = [
                    ['field' => 'nama_user', 'label' => 'Nama', 'required' => true, 'wire:model' => 'dosenEksternalData.nama_user'],
                    ['field' => 'email_user', 'label' => 'Email', 'type' => 'email', 'required' => true, 'wire:model' => 'dosenEksternalData.email_user'],
                    ['field' => 'nip', 'label' => 'NIP', 'type' => 'number', 'required' => true, 'wire:model' => 'dosenEksternalData.nip'],
                ];

                //merge
                $fields = array_merge($fields, $pengajuanDosenEksternalField);
            }

            if ($this->externalDosenHaveAccount == 'sudah' || $isEdit) {
                $this->searchResultsDosen = DosenEksternal::optionDosen();

                // hapus yang sudah dipilih
                $ignoreSelected = array_keys($this->recordSavedAnggota);

                if ($isEdit) {
                    $ignoreSelected = array_diff($ignoreSelected, [$this->state['onEditBiodataId']]);
                }

                foreach ($ignoreSelected as $key) {
                    unset($this->searchResultsDosen[$key]);
                }

                $fields[] = [
                    'field' => 'id_biodata',
                    'label' => 'Dosen',
                    'selected' => $this->state['onEditBiodataId'] ?? null,
                    'options' => $this->searchResultsDosen,
                    'required' => true,
                    'variant' => 'search',
                    'wire:model.change' => 'record._temp_anggota.id_biodata',
                ];
            }

        }

        return $fields;
    }

    public function memberType_change($value)
    {
        $this->dispatch('show-loading');
        $this->memberType = !empty($value) ? $value : null;
        $this->selectedIdProdi = null;
        $this->record['_temp_anggota']['id_biodata'] = null;
        $this->initForm();
    }

    public function unitKerja_change($value)
    {
        $this->dispatch('show-loading');
        $this->selectedIdProdi = !empty($value) ? $value : null;
        $this->initForm();
    }

    protected function selectValue($value)
    {
        if (is_array($value) && array_key_exists('value', $value)) {
            return $value['value'];
        }

        return $value ?? null;
    }


    private function searchProdi($searchTerm)
    {
        return (new UnitKerjaManagementService())->optionProdi(searchTerm: $searchTerm);
    }

    private function searchDosen($searchTerm, $excludeId = [], $isExternal = false)
    {
        if ($isExternal) {
            return DosenEksternal::optionDosen($searchTerm, 5, null, excludeId: $excludeId);
        } else {
            //ini get internal sebenernya
            return DosenEksternal::optionDosen($searchTerm, 5, $this->selectedIdProdi, excludeId: $excludeId);
        }
    }

    private function searchPerguruanTinggi($searchTerm)
    {
        return PerguruanTinggi::searchOption($searchTerm);
    }

    private function searchMahasiswa($searchTerm, $excludeId = [])
    {
        $prodi = UnitKerja::find($this->selectedIdProdi);

        return Mahasiswa::searchOptionV1($searchTerm, $prodi->kode_unit, 5, $excludeId);
    }

    private function validateDosen($index = null, $biodataId = null)
    {
        $isError = false;
        $isEdit = isset($index);

        // validate required
        if (empty($biodataId)) {
            $isError = true;
            $this->addError('id_biodata', __('validation.required', [
                'attribute' => 'Nama Dosen'
            ]));
        }

        // validasi dosen sudah ada sebelumnya
        if ($isEdit) {
            $isExist = !!array_filter($this->recordSavedAnggota, function ($item) use ($biodataId, $index) {
                if ($item['memberType'] == PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL) {
                    return ($item['id_biodata'] == $biodataId)
                        && ($item['id_biodata'] != $this->recordSavedAnggota[$index]['id_biodata']);
                }
            });
        } else {
            $isExist = !!array_filter($this->recordSavedAnggota, function ($item) use ($biodataId) {
                if ($item['memberType'] == PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL) {
                    return $item['id_biodata'] == $biodataId;
                }
            });
        }

        if ($isExist) {
            $isError = true;
            $this->addError('id_biodata', __('validation.unique', [
                'attribute' => 'Nama Dosen'
            ]));
        }

        return $isError;
    }

    private function validateMahasiswa($index = null, $biodataId = null)
    {
        $isError = false;
        $isEdit = !empty($index);

        // validate required
        if (empty($biodataId)) {
            $isError = true;
            $this->addError('id_biodata', __('validation.required', [
                'attribute' => 'Nama Mahasiswa'
            ]));
        }

        // validasi mahasiswa sudah ada sebelumnya
        if ($isEdit) {
            $isExist = !!array_filter($this->recordSavedAnggota, function ($item) use ($biodataId, $index) {
                if ($item['memberType'] == PengajuanPendanaanAnggota::JENIS_MAHASISWA) {
                    return ($item['id_biodata'] == $biodataId)
                        && ($item['id_biodata'] != $this->recordSavedAnggota[$index]['id_biodata']);
                }
            });
        } else {
            $isExist = !!array_filter($this->recordSavedAnggota, function ($item) use ($biodataId) {
                if ($item['memberType'] == PengajuanPendanaanAnggota::JENIS_MAHASISWA) {
                    return $item['id_biodata'] == $biodataId;
                }
            });
        }

        if ($isExist) {
            $isError = true;
            $this->addError('id_biodata', __('validation.unique', [
                'attribute' => 'Nama Mahasiswa'
            ]));
        }

        return $isError;
    }
}