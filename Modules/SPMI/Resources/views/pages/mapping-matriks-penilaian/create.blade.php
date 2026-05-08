@php
    $backSubFooter = "spmi/mapping-matriks-penilaian/$unitId/$auditPeriodId";
@endphp

<x-core::livewire.layouts.create-edit :data="$data ?? []" :$routeName :$alert :backSubFooter="$backSubFooter" :withConfirmationModal="!$isProdi"
    :confirmationModalTitle="'Apakah Anda Yakin Menerapkan Mapping Ini?'" :confirmationModalMessage="'Anda akan menerapkan mapping ke seluruh fakultas dan program studi yang telah dipilih pada halaman ini. Tindakan ini juga dapat mengubah mapping yang sebelumnya sudah ditentukan pada level fakultas atau program studi tertentu.'">
    @pushOnce('head')
        @vite('resources/scss/custom-utils.scss')
        @vite('Modules/SPMI/Resources/assets/sass/surat-tugas-auditor/create.scss')
    @endPushOnce

    <x-core::layouts.create.cards :$data />

    <div class="col-12" wire:ignore.self>
        <div class="card card_table">
            <div class="card__body">
                <div class="box-table">
                    <div class="box-table__content">
                        @if (!empty($listProdiFinalisasi) || $isProdiDisabled)
                            @php
                                if ($isProdiDisabled) {
                                    $alertCustomStatic = [
                                        'title' => 'Perhatian',
                                        'dismissible' => false,
                                        'type' => 'warning',
                                        'isHtml' => true,
                                        'message' =>
                                            'Panduan penilaian yang dipilih telah difinalisasi pada unit kerja ini. Mapping tidak dapat diubah kembali.',
                                    ];
                                } else {
                                    $alertCustomStatic = [
                                        'title' => 'Perhatian',
                                        'dismissible' => false,
                                        'type' => 'warning',
                                        'isHtml' => true,
                                        'message' =>
                                            'Beberapa unit kerja telah melakukan finalisasi pada panduan penilaian yang dipilih. Mapping yang Anda lakukan hanya akan diterapkan pada unit kerja yang belum melakukan finalisasi. Daftar unit kerja yang telah melakukan finalisasi: <ul>' .
                                            collect($listProdiFinalisasi)
                                                ->map(fn($item) => "<li>{$item->nama_unit}</li>")
                                                ->implode('') .
                                            '</ul>',
                                    ];
                                }
                            @endphp
                            <x-core::layouts.html.alert :data="$alertCustomStatic" class="util_mb-20" />
                        @endif
                        <div class="table-max table-max_absolute">
                            <script>
                                function checkAll(element) {
                                    const isChecked = element.checked;
                                    const allCheckboxes = document.querySelectorAll('tbody input[type="checkbox"]');
                                    const changedData = {};

                                    allCheckboxes.forEach(checkbox => {
                                        checkbox.checked = isChecked;
                                        const butirId = checkbox.closest('tr').dataset.id;
                                        if (butirId) {
                                            changedData[butirId] = isChecked;
                                        }
                                    });

                                    @this.call('updateBulkMapping', changedData);
                                }

                                function handleCheckboxChange(element, butirId) {
                                    const isChecked = element.checked;
                                    const currentRow = element.closest('tr');
                                    const isParent = currentRow.querySelector('b') !== null;
                                    const changedData = {
                                        [butirId]: isChecked
                                    };

                                    if (isChecked) {
                                        let currentLevel = parseInt(currentRow.dataset.level, 10);
                                        let previousRow = currentRow.previousElementSibling;

                                        while (previousRow && currentLevel > 0) {
                                            const prevLevel = parseInt(previousRow.dataset.level, 10);
                                            if (prevLevel < currentLevel) {
                                                const parentCheckbox = previousRow.querySelector('input[type="checkbox"]');
                                                if (parentCheckbox && !parentCheckbox.checked) {
                                                    parentCheckbox.checked = true;
                                                    const parentId = previousRow.dataset.id;
                                                    if (parentId) {
                                                        changedData[parentId] = true;
                                                    }
                                                }
                                                currentLevel = prevLevel;
                                            }
                                            previousRow = previousRow.previousElementSibling;
                                        }
                                    }

                                    // NOTE: ketika child, uncheck untuk yang di bawahnya
                                    const currentLevel = parseInt(currentRow.dataset.level, 10);
                                    let nextRow = currentRow.nextElementSibling;

                                    while (nextRow) {
                                        const nextLevel = parseInt(nextRow.dataset.level, 10);
                                        if (nextLevel > currentLevel) {
                                            const childCheckbox = nextRow.querySelector('input[type="checkbox"]');
                                            const childId = nextRow.dataset.id;

                                            if (childCheckbox && childId && childCheckbox.checked !== isChecked) {
                                                childCheckbox.checked = isChecked;
                                                changedData[childId] = isChecked;
                                            }
                                        } else {
                                            break;
                                        }
                                        nextRow = nextRow.nextElementSibling;
                                    }

                                    @this.call('updateBulkMapping', changedData);
                                }
                            </script>

                            @php
                                // Check if all IKU items are checked
                                $allIkuChecked = true;
                                foreach ($listMatriksPenilaian as $butir) {
                                    if (!empty($butir['apakah_data_default']) && !isset($checkedButir[$butir['id']])) {
                                        $allIkuChecked = false;
                                        break;
                                    }
                                }
                            @endphp

                            @if (!$allIkuChecked && count($listMatriksPenilaian) > 0)
                                @php
                                    $alertIkuWarning = [
                                        'title' => 'Perhatian',
                                        'dismissible' => false,
                                        'type' => 'warning',
                                        'isHtml' => false,
                                        'message' => 'Sebagian butir IAPS 5.1 belum dimapping. Hal ini dapat menyebabkan hasil akhir simulasi skor SPME tidak valid.',
                                    ];
                                @endphp
                                <x-core::layouts.html.alert :data="$alertIkuWarning" class="util_mb-20" />
                            @endif

                            <h3 style="color: #344054;">
                                Data Mapping Matriks Penilaian
                            </h3>
                            <div class="form-control__helper" style="margin-top: -5px;">
                                Pilih matriks penilaian yang akan dimapping ke kategori indikator.
                            </div>
                            <table style="margin-top: 15px;">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Matriks Penilaian</th>
                                        <th>Kategori</th>
                                        <td style="width: 50px; text-align: center;">
                                            <input type="checkbox" id="check-all" @if($isProdiDisabled) disabled @else onclick="checkAll(this)" @endif />
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($listMatriksPenilaian as $index => $butir)
                                        <tr wire:key="indikator-butir-{{ $butir['id'] }}" data-id="{{ $butir['id'] }}"
                                            data-level="{{ $butir['info_level'] }}">
                                            <td>{{ $butir['nomor_penilaian'] }}</td>
                                            <td>
                                                <p style="margin-left: {{ $butir['info_level'] * 18 }}px">
                                                    @if (!$butir['id_parent'])
                                                        <b>{!! $butir['pertanyaan_penilaian'] !!}</b>
                                                    @else
                                                        {!! $butir['pertanyaan_penilaian'] !!}
                                                    @endif
                                                </p>
                                            </td>
                                            <td>
                                                <x-core::badge :variant="empty($butir['apakah_data_default']) ? 'warning' : 'success'" :decoration="true" :type="'secondary'">
                                                    {{ empty($butir['apakah_data_default']) ? 'Indikator Kinerja Tambahan' : 'Indikator Kinerja Utama' }}
                                                </x-core::badge>
                                            </td>
                                            <td style="width: 50px; text-align: center;">
                                                <input type="checkbox" @if($isProdiDisabled) disabled @else onchange="handleCheckboxChange(this, {{ $butir['id'] }}, {{ !$butir['id_parent'] ? 'true' : 'false' }})" @endif
                                                    @if (isset($checkedButir[$butir['id']])) checked @endif />
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if (count($listMatriksPenilaian) == 0)
                                        <tr>
                                            <td colspan="3" style="text-align: center;">
                                                Silakan pilih panduan penilaian terlebih dahulu.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>
    </div>

    <div class="modal modal_confirmation-primary @if ($showModal) is-visible @endif"
        id="upload-penilaian-ikt">
        <div class="modal__overlay" wire:click="toggleModal"></div>

        <div class="modal__wrapper">
            <div class="modal__header">
                <div class="modal__header-wrapper">
                    <h3 class="modal__title">Konfirmasi Mapping Belum Lengkap</h3>
                </div>
                <span class="icon icon-x-mark-mini" wire:click="toggleModal"></span>
            </div>

            <div class="modal__body">
                <p>Beberapa butir yang Anda centang di Mapping Penilaian Matriks belum sepenuhnya dipetakan pada
                    Mapping
                    Laporan Kinerja.</p>
                <p>Untuk melanjutkan, sistem akan otomatis menambahkan butir tersebut ke dalam Mapping Laporan
                    Kinerja
                    agar tetap konsisten. Apakah Anda yakin ingin melanjutkan proses ini?</p>
            </div>
            <div class="modal__footer">
                <div class="grid cols-1 cols-sm-2">
                    <button class="btn btn_outline" type="button" data-dismiss="modal" id="batalBtn"
                        wire:click="toggleModal">
                        Batal
                    </button>
                    <button class="btn btn_primary" wire:click='forceSave'>Ya, Lanjutkan Mapping</button>
                </div>
            </div>
        </div>
    </div>


    @pushOnce('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('scroll-to-alert-custom', () => {
                    setTimeout(() => {
                        const alertCustom = document.querySelector('.alert-custom');
                        window.scrollTo({
                            top: alertCustom.offsetTop - 20,
                            behavior: 'smooth'
                        });
                    }, 200);
                });
            });
        </script>
    @endPushOnce
</x-core::livewire.layouts.create-edit>
