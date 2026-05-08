@php
    $backSubFooter = "spmi/mapping-indikator-butir/$unitId/$auditPeriodId";

    if (request()->has('jenis_edisi') && request()->query('jenis_edisi') === \Modules\SPMI\Models\AkreditasiBuku::SELF_EVALUATION) {
        $backSubFooter .= '?tab=' . \Modules\SPMI\Models\AkreditasiBuku::SELF_EVALUATION;
    }
@endphp

<x-core::livewire.layouts.create-edit :data="$data ?? []" :$routeName :$alert :backSubFooter="$backSubFooter"
    :withConfirmationModal="!$isProdi"
    :confirmationModalTitle="'Apakah Anda Yakin Menerapkan Mapping Ini?'"
    :confirmationModalMessage="'Anda akan menerapkan mapping ke seluruh fakultas dan program studi yang telah dipilih pada halaman ini. Tindakan ini juga dapat mengubah mapping yang sebelumnya sudah ditentukan pada level fakultas atau program studi tertentu.'">
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

                            <h3 style="color: #344054;">
                                Data Mapping Laporan Kinerja
                            </h3>
                            <div class="form-control__helper" style="margin-top: -5px;">
                                Pilih butir laporan kinerja yang akan dimapping ke kategori indikator.
                            </div>
                            <table style="margin-top: 15px;">
                                <thead>
                                    <tr>
                                        <th>Nama Butir</th>
                                        <th>Kategori</th>
                                        <td style="width: 50px; text-align: center;">
                                            <input type="checkbox" id="check-all" onclick="checkAll(this)" />
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($listButirIndikator as $index => $butir)
                                        <tr wire:key="indikator-butir-{{ $butir['id'] }}" data-id="{{ $butir['id'] }}"
                                            data-level="{{ $butir['info_level'] }}">
                                            <td>
                                                <p style="margin-left: {{ $butir['info_level'] * 18 }}px">
                                                    @if ($butir['apakah_parent'])
                                                        <b>{!! $butir['nama_indikator_laporan_kinerja'] ?? $butir['nama_indikator_evaluasi_diri'] !!}</b>
                                                    @else
                                                        {!! $butir['nama_indikator_laporan_kinerja'] ?? $butir['nama_indikator_evaluasi_diri'] !!}
                                                    @endif
                                                </p>
                                            </td>
                                            <td>
                                                <x-core::badge :variant="empty($butir['apakah_data_default']) ? 'warning' : 'success'" :decoration="true" :type="'secondary'">
                                                    {{ empty($butir['apakah_data_default']) ? 'Indikator Kinerja Tambahan' : 'Indikator Kinerja Utama' }}
                                                </x-core::badge>
                                            </td>
                                            <td style="width: 50px; text-align: center;">
                                                <input type="checkbox"
                                                    onchange="handleCheckboxChange(this, {{ $butir['id'] }}, {{ $butir['apakah_parent'] ? 'true' : 'false' }})"
                                                    @if (isset($checkedButir[$butir['id']])) checked @endif />
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if (count($listButirIndikator) == 0)
                                        <tr>
                                            <td colspan="3" style="text-align: center;">
                                                Silakan pilih panduan pengisian terlebih dahulu.
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
