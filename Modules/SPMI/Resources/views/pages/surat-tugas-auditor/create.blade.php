<x-core::livewire.layouts.create-edit :data="$data ?? []" :$routeName :$alert>
    @pushOnce('head')
        @vite('Modules/SPMI/Resources/assets/sass/surat-tugas-auditor/create.scss')
    @endPushOnce
    @push('head')
    <style>
        /* .form-nav {
            z-index: 1201 !important;
        } */
    </style>
    @endpush
    <x-core::layouts.create.cards :$data />

    <div class="col-12 audit-person" wire:ignore.self>
        <div class="card util_w-100" wire:ignore.self>
            <div class="card__body" wire:ignore.self>
                <div class="header-section">
                    <div class="header-section__title">
                        <h2 class="header-section__title_text">Daftar Pegawai <span class="important">*</span></h2>
                        @if (!$isShowFormPerson)
                            {{-- Loading ketika menampilkan form tambah --}}
                            <div class="loader" wire:loading wire:target="showFormPerson">
                                <span class="loader__spinner"></span>
                            </div>

                            {{-- Loading ketika menampilkan form edit --}}
                            <div class="loader" wire:loading wire:target="editPersonData">
                                <span class="loader__spinner"></span>
                            </div>

                            {{-- Loading ketika menampilkan hapus data --}}
                            <div class="loader" wire:loading wire:target="deletePersonDataByStudyProgram">
                                <span class="loader__spinner"></span>
                            </div>
                        @endif
                    </div>
                </div>

                <div @class([
                    'alert alert',
                    'alert_helper alert-custom' => !$isHasUnsavedChange,
                    'alert_helper' => $isHasUnsavedChange,
                ]) style="margin-top: 16px">
                    <div class="alert__content">
                        @if (!$isHasUnsavedChange)
                            <p>
                                Silakan petakan unit kerja dan auditor yang bertugas.
                            </p>
                        @else
                            <p>
                                Terdapat data yang belum tersimpan, silahkan simpan jika sudah selesai.
                                <a class="link" type="button" wire:click="save">Simpan Data</a>
                            </p>
                        @endif
                    </div>
                </div>

                @if (!empty($listPersonData))
                    {{-- Section daftar personil  --}}
                    <div class="grid auditor-list-section">
                        @foreach ($listPersonData as $person)
                            @php
                                $isRemoveProdi = !in_array($person['id_unit'], $tempPenilaianAudit);
                            @endphp
                            <div class="grid col-12 auditor-list" @style([
                                'row-gap: 0' => $isEditForm,
                            ])>
                                @if (!($person['isEdit'] && $isEditForm))
                                    <div class="col-12 auditor-list__program-study">
                                        <h3>{{ $person['study_program'] }}</h3>
                                        <div class="action">
                                            <x-core::button class="btn__edit" leading-icon="pencil" variant="outline"
                                                size="xs" wire:click="editPersonData({{ $person['id_unit'] }})" />
                                            @if ($isRemoveProdi)
                                                <x-core::button class="btn__delete-person btn__delete" leading-icon="trash"
                                                    variant="outline" size="xs" data-toggle="modal"
                                                    data-target="#modal-confirmation-delete"
                                                    data-event="deletePersonDataByStudyProgram({{ $person['id_unit'] }})" />
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                <div class="table-max col-12">
                                    <table>
                                        <tbody>
                                            @php
                                                usort($person['persons'], function ($a, $b) use ($positionAuditee) {
                                                    if ($a['position_key'] == $positionAuditee) {
                                                        return -1;
                                                    }
                                                    if ($b['position_key'] == $positionAuditee) {
                                                        return 1;
                                                    }

                                                    return $a['position_key'] <=> $b['position_key'];
                                                });
                                            @endphp
                                            @if ($person['isEdit'] && $isEditForm)
                                                <x-spmi::pages.surat-tugas-auditor.form-person :$isRemoveProdi :$availablePersonOptions :$availablePersonBySKOptions
                                                    :$selectedPersonOptions :$positionOptions :$studyPrograms
                                                    :$isHasErrorStudyProgram :$selectedStudyProgram :$addedFormPerson
                                                    :isCreate="false" :$selectedAuditee :$positionAuditee :$positionMemberAuditee
                                                    @style(['padding:0!important']) />
                                            @else
                                                @foreach ($person['persons'] as $i => $item)
                                                    <tr>
                                                        <td>{{ $loop->index + 1 }}</td>
                                                        <td>{{ $item['person'] }}</td>
                                                        <td>{{ $item['position'] }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if (!$isShowFormPerson && !$isEditForm)
                    <x-core::button variant="ghost" size="xs" wire:click="showFormPerson" @style(['margin-top: 12px'])>
                        Tambah Data
                    </x-core::button>
                @endif

                @if ($isShowFormPerson && !$isEditForm)
                    <x-spmi::pages.surat-tugas-auditor.form-person title="Tambah Data Auditor" :isRemoveProdi="true" :$availablePersonOptions :$availablePersonBySKOptions
                        :$isHasErrorStudyProgram :$selectedPersonOptions :$positionOptions :$studyPrograms
                        :$selectedStudyProgram :$addedFormPerson :$selectedAuditee :$positionAuditee :$positionMemberAuditee 
                        @style(['margin-top: 24px']) />
                @endif
            </div>
        </div>
    </div>

    @if ($isShowFormPerson)
        <x-core::modal title="Batalkan {{ $isEditForm ? 'Edit' : 'Tambah' }} Data" variant="primary"
            id="modal-confirmation">
            <x-core::modal.body>
                Apakah anda yakin ingin membatalkan {{ $isEditForm ? 'edit' : 'tambah' }} data?
                Jika dibatalkan maka perubahan yang telah dilakukan tidak akan disimpan.
                <x-slot:footer>
                    <div class="grid cols-1 cols-sm-2">
                        <x-core::button variant="outline" data-dismiss="modal">
                            Batal
                        </x-core::button>
                        <x-core::button variant="primary" wire:click="showFormPerson(false)">
                            Oke
                        </x-core::button>
                    </div>
                </x-slot:footer>
            </x-core::modal.body>
        </x-core::modal>
    @else
        <x-core::modal title="Hapus Unit Kerja" variant="error" id="modal-confirmation-delete">
            <x-core::modal.body>
                Apakah anda yakin ingin menghapus unit kerja ini?
                Karena data yang telah dihapus tidak dapat dikembalikan lagi.
                <x-slot:footer>
                    <div class="grid cols-1 cols-sm-2">
                        <x-core::button variant="outline" data-dismiss="modal">
                            Batal
                        </x-core::button>
                        <x-core::button class="confirm-delete" variant="destructive">
                            Hapus
                        </x-core::button>
                    </div>
                </x-slot:footer>
            </x-core::modal.body>
        </x-core::modal>
    @endif

    @pushOnce('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.hook('element.init', ({
                    el
                }) => {
                    if (el.tagName == "SELECT" && el.name != 'id_audit_periode') {
                        new Choices(el, {
                            allowHTML: true,
                            shouldSort: false,
                            searchEnabled: true,
                            searchChoices: true,
                            searchFloor: 1,
                            searchResultLimit: 4,
                            searchFields: ['label', 'value'],
                        });
                    }
                });

                Livewire.on('scroll-to-bottom', () => {
                    setTimeout(() => {
                        window.scrollTo({
                            top: document.body.scrollHeight,
                            behavior: 'smooth'
                        });
                    }, 200);
                });
            });

            // Konfirmasi hapus prodi
            document.body.addEventListener('click', (e) => {
                if (e.target.closest('.btn__delete-person') != null) {
                    const targetBtnElem = e.target.closest('.btn__delete-person');
                    const modal = document.querySelector('#modal-confirmation-delete');
                    modal.querySelector('.confirm-delete').setAttribute('wire:click', targetBtnElem.getAttribute(
                        'data-event'));
                }
            });
        </script>
    @endPushOnce
</x-core::livewire.layouts.create-edit>
