@php
    $backSubFooter = route('spmi.jenis-standar.index');
    $title = $viewOnly ? 'Detail ' . $resourceTitle : null;
    
    $apakahDefault = array_filter($data, fn ($item) => $item['field'] === 'apakah_data_default');
    $isDefault = reset($apakahDefault)['value'] ?? false;

    if(!$viewOnly && $isDefault) {
        abort(404);
    }
@endphp

<x-core::livewire.layouts.create-edit :data="$data ?? []" :$routeName :$alert :backSubFooter="$backSubFooter" :confirmationModalTitle="'Apakah Anda Yakin Menerapkan Mapping Ini?'"
    :confirmationModalMessage="'Anda akan menerapkan mapping ke seluruh fakultas dan program studi yang telah dipilih pada halaman ini. Tindakan ini juga dapat mengubah mapping yang sebelumnya sudah ditentukan pada level fakultas atau program studi tertentu.'"
    :title="$title">
    @pushOnce('head')
        @vite('resources/scss/custom-utils.scss')
        @vite('Modules/SPMI/Resources/assets/sass/surat-tugas-auditor/create.scss')
    @endPushOnce

    <x-slot:customAction>
        @if (!$viewOnly)
            <div class="form-nav__button-wrapper">
                <button wire:click="save" type="button" class="btn btn_primary btn_sm">
                    Simpan
                </button>
            </div>
        @elseif (!$isDefault)
            <div class="form-nav__button-wrapper">
                <a class="btn btn_primary btn_sm" href="{{ route('spmi.jenis-standar.edit', ['jenis_standar' => $edit]) }}">
                    Ubah
                </a>
            </div>
        @endif
    </x-slot:customAction>

    <x-core::layouts.create.cards :$data />

    <div class="col-12" wire:ignore.self>
        <div class="card card_table">
            <div class="card__body">
                <div class="box-table">
                    <div class="box-table__content">
                        <div>

                            <h3 style="color: #344054;">
                                Daftar Butir Standar
                            </h3>
                            <div class="form-control__helper" style="margin-top: -5px;">
                                Tambahkan dan atur butir standar yang termasuk dalam jenis standar audit ini.
                            </div>
                            <table style="margin-top: 15px;">
                                <thead>
                                    <tr>
                                        <th>Kode Butir Standar</th>
                                        <th>Nama Butir Standar</th>
                                        @if (!$viewOnly)
                                            <th style="width: 50px !important; text-align: center;">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!$viewOnly)
                                        <tr>
                                            <td>
                                                <x-core::controls.input type="text" name="kode_butir" required
                                                    wire:model.defer="kode_butir" placeholder="Kode Butir Standar"
                                                    maxlength="5" />
                                                @error('kode_butir')
                                                    <div class="form-control__helper" style="color: var(--qn-danger);">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </td>
                                            <td>
                                                <x-core::controls.input type="text" name="nama_butir" required
                                                    wire:model.defer="nama_butir" placeholder="Nama Butir Standar"
                                                    maxlength="255" />
                                                @error('nama_butir')
                                                    <div class="form-control__helper" style="color: var(--qn-danger);">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </td>
                                            <td class="cell-action">
                                                <div class="dropdown-group"
                                                    style="display: flex; align-items: center; gap: 4px;">
                                                    <x-core::button leading-icon="check-circle" variant="primary"
                                                        size="xs" wire:click="addButir" />
                                                    <x-core::button leading-icon="pencil-solid" variant="outline"
                                                        size="xs" disabled />
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                    {{-- List data --}}
                                    @forelse($butirStandar as $index => $butir)
                                        @if ($editIndex === $index)
                                            <tr>
                                                <td>
                                                    <x-core::controls.input type="text" name="editKodeButir" required
                                                        wire:model.defer="editKodeButir"
                                                        placeholder="Kode Butir Standar" maxlength="5" />
                                                    @error('editKodeButir')
                                                        <div class="form-control__helper" style="color: var(--qn-danger);">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <x-core::controls.input type="text" name="editNamaButir" required
                                                        wire:model.defer="editNamaButir"
                                                        placeholder="Nama Butir Standar" maxlength="255" />
                                                    @error('editNamaButir')
                                                        <div class="form-control__helper" style="color: var(--qn-danger);">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </td>
                                                @if (!$viewOnly)
                                                    <td class="cell-action">
                                                        <div class="dropdown-group"
                                                            style="display: flex; align-items: center; gap: 4px;">
                                                            <x-core::button leading-icon="check-circle"
                                                                variant="primary" size="xs"
                                                                wire:click="saveInlineEdit" />
                                                            <x-core::button leading-icon="x-circle"
                                                                variant="destructive" size="xs"
                                                                wire:click="cancelInlineEdit" />
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @else
                                            <tr>
                                                <td>{{ $butir['kode_butir'] }}</td>
                                                <td>{{ $butir['nama_butir'] }}</td>
                                                @if (!$viewOnly)
                                                    <td class="cell-action">
                                                        <div class="dropdown-group"
                                                            style="display: flex; align-items: center; gap: 4px;">
                                                            <x-core::button leading-icon="pencil-solid"
                                                                variant="primary" size="xs"
                                                                wire:click="startInlineEdit({{ $index }})" />
                                                            <x-core::button leading-icon="trash-solid"
                                                                variant="destructive" size="xs"
                                                                wire:click="deleteButir({{ $index }})" />
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="3" style="text-align: center;">Belum ada data butir standar.
                                            </td>
                                        </tr>
                                    @endforelse
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
