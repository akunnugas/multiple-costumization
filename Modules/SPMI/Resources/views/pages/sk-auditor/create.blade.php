<x-core::livewire.layouts.create-edit :data="$data ?? []" :$routeName :$alert>
    @pushOnce('head')
        @vite('resources/scss/custom-utils.scss')
        @vite('Modules/SPMI/Resources/assets/sass/sk-auditor/create.scss')
    @endPushOnce

    <x-core::layouts.create.cards :$data />

    <div class="col-12" wire:ignore.self>
        <div class="card util_w-100" wire:ignore.self>
            <div class="card__header" wire:ignore>
                <div class="card__header-left">
                    <div class="card__header-block">
                        <h2 class="header__title" style="font-size: .875rem; line-height: 24px">Daftar Pegawai</h2>
                        <p class="header__subtitle" style="font-weight: normal">Tambahkan data auditor yang bertugas
                            sesuai dengan surat keputusan yang telah diunggah</p>
                    </div>
                </div>
            </div>
            <div class="card__body" wire:ignore.self>
                <div class="util_d-flex util_w-100" style="padding-bottom: 16px">
                    <div class="util_w-75">
                        <x-core::controls.form name="employee" label="Pegawai" :showLabel="false" purpose="form" id="employee-select" :disabled="$editEmployeeId ? true : false"
                            :options="$employeeOptions" control="select" variant="search" wire:model="selectedEmployee" />
                    </div>
                    <div class="util_w-25">
                        <x-core::button variant="primary" size="xs" wire:click="saveEmployee" style="margin-left: 10px; margin-top: 5px;" :disabled="$editEmployeeId ? true : false">
                            Tambah Pegawai
                        </x-core::button>
                    </div>
                </div>
                @if (!empty($savedEmployees))
                    @foreach ($savedEmployees as $item)
                        <div class="grid" style="padding-bottom: 16px">
                            @if (isset($editEmployeeId) && $editEmployeeId == $item['id'])
                                @php
                                    $isError = $errors?->has('employee');
                                    if ($isError) {
                                        $helper = $errors->first('employee');
                                    }
                                @endphp
                                <div class="grid col-12">
                                    <div class="util_d-flex col-12 util_flex-between">
                                        <div class="util_d-flex util_flex-middle" style="width: 80%; gap: 12px">
                                            <span style="font-size: 12px;font-weight: 500;">
                                                {{ $loop->index + 1 }}.
                                            </span>
                                            <div class="util_w-100">
                                                <x-core::controls.select label="Pegawai" name="employee" purpose="form"
                                                    id="employee-select" :options="$employeeOptions" variant="search"
                                                    wire:model="selectedEmployee" :selected="$item['id']" />
                                            </div>
                                        </div>
                                        <div class="util_d-flex util_flex-middle" style="gap: 8px">
                                            <x-core::button leading-icon="check" variant="outline" size="xs"
                                                wire:click="saveEditEmployee({{ $item['id'] }})" />
                                            <x-core::button leading-icon="x-mark" variant="outline" size="xs"
                                                wire:click="editEmployee(null)" />
                                        </div>
                                    </div>
                                    @if ($isError)
                                        <div @class(['form-control__helper', 'col-12', 'error' => $isError]) style="padding:0 20px">
                                            {{ $helper ?? null }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="util_d-flex col-12 util_flex-between">
                                    <div class="util_d-flex util_flex-middle" style="gap: 12px">
                                        <span style="font-size: 12px;font-weight: 500;">
                                            {{ $loop->index + 1 }}.
                                        </span>
                                        <div class="util_d-flex util_flex-column" style="gap: 4px">
                                            <h5 style="font-size: 12px;font-weight: 500;">{{ $item['nama'] }}</h5>
                                            <p style="font-size: 12px;font-weight: normal;color: #9aa4b2">
                                                {{ $item['nip'] }}
                                            </p>
                                        </div>
                                    </div>
                                    @if (!in_array($item['id'], $tempEmployeesAuditor))
                                        <div class="util_d-flex util_flex-middle" style="gap: 8px">
                                            <x-core::button leading-icon="pencil-solid" variant="outline" size="xs"
                                                wire:click="editEmployee({{ $item['id'] }})" />
                                            <x-core::button leading-icon="trash" variant="outline" size="xs"
                                                wire:click="deleteEmployee({{ $item['id'] }})" />
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    @pushOnce('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.hook('element.init', ({
                    el
                }) => {
                    if (el.tagName == "SELECT" && el.id == 'employee-select') {
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
            });
        </script>
    @endPushOnce
</x-core::livewire.layouts.create-edit>
