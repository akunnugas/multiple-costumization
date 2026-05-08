<x-core::livewire.layouts.create-edit :data="$data ?? []" :$routeName :$alert>
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


                            <x-core::layouts.html.alert :data="$alertCustom" class="util_mb-20 alert-custom" />
                            <table>
                                <thead>
                                    <tr>
                                        <th>Unit Kerja</th>
                                        <th>Panduan Pengisian</th>
                                        <th>Panduan Penilaian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($units as $idUnit => $label)
                                        <tr wire:key="unit-{{ $idUnit }}">
                                            <td>
                                                {{ $label }}
                                            </td>
                                            <td wire:ignore>
                                                <div x-data x-init="const el = $refs.selectPengisian;
                                                el.addEventListener('change', (event) => {
                                                    @this.set('selectedPengisian.{{ $idUnit }}', event.detail.value);
                                                });">
                                                    <x-core::controls.form name="lkps.{{ $idUnit }}"
                                                        label="Panduan Pengisian" :showLabel="false" :value="$selectedPengisian[$idUnit] ?? null"
                                                        :options="$listPanduanPengisian" control="select" x-ref="selectPengisian" />
                                                </div>
                                            </td>
                                            <td>
                                                <x-core::controls.form name="penilaian.{{ $idUnit }}"
                                                    label="Panduan Penilaian" :showLabel="false"
                                                    wire:change="updatePenilaian($event.target.value, '{{ $idUnit }}')"
                                                    :selected="$selectedPenilaian[$idUnit] ?? null" :options="$listPenilaianOptions[$idUnit] ?? []" control="select"
                                                    variant="search" id="penilaian-select-{{ $idUnit }}" />
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if (count($units) == 0)
                                        <tr>
                                            <td colspan="3" style="text-align: center;">
                                                Silakan pilih periode audit terlebih dahulu untuk menampilkan daftar
                                                program studi yang dapat dipilih.
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
