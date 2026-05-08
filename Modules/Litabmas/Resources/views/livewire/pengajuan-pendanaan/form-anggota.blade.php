@props([
    'state' => [],
    'fields' => [],
    'staticAlertAnggota' => [],
    'memberType' => null,
])

<div>
    @php
        use Modules\Litabmas\Models\PengajuanPendanaanAnggota;

        $hasMember = !empty($recordSavedAnggota);
        $showAllAnggota = $hasMember;
        if (empty($staticAlertAnggota) && !empty($apakahButuhApproveSemuaAnggota) && $showAllAnggota) {
            $staticAlertAnggota = [
                'type' => 'helper',
                'message' => 'Proposal penelitian dapat berhasil diajukan setelah semua calon anggota dosen menerima tawaran.
                    Status "Menunggu Persetujuan" menandakan belum adanya konfirmasi dari calon anggota penelitian.',
            ];
        }
    @endphp
    @if (!empty($staticAlertAnggota))
        <x-core::layouts.html.alert :data="$staticAlertAnggota" class="util_mb-8" />
    @endif

    <div class="grid cols-1">
        @if ($showAllAnggota)
            @foreach ($recordSavedAnggota as $index => $item)
                @php
                    $isJenisAnggotaMhs = $item['memberType'] == PengajuanPendanaanAnggota::JENIS_MAHASISWA;
                    $statusAnggotaMenunggu = $statusAnggotaDisetujui = $statusAnggotaDitolak = false;
                    if (is_null($item['apakah_undangan_diterima'])) {
                        $statusAnggotaMenunggu = true;
                    } else {
                        $statusAnggotaDisetujui = $item['apakah_undangan_diterima'] === true;
                        $statusAnggotaDitolak = $item['apakah_undangan_diterima'] === false;
                    }
                @endphp
                <div class="util_d-flex col-12 util_flex-between">
                    <div class="util_d-flex util_flex-middle" style="gap: 12px">
                        <span style="font-size: 12px;font-weight: 500;">
                            {{ $loop->iteration }}.
                        </span>
                        <div class="util_d-flex util_flex-column" style="gap: 4px">
                            <div class="util_d-flex">
                                <h5 style="font-size: 12px;font-weight: 500;">
                                    {{ $item['name'] }}
                                </h5>
                                @if ($apakahButuhApproveSemuaAnggota && !$isJenisAnggotaMhs)
                                    @if ($statusAnggotaMenunggu)
                                        <x-core::badge variant="warning" type="outline" size="sm"
                                            class="util_ml-8">
                                            Menunggu Persetujuan
                                        </x-core::badge>
                                    @elseif ($statusAnggotaDitolak)
                                        <x-core::badge variant="danger" type="outline" size="sm" class="util_ml-8">
                                            Ditolak
                                        </x-core::badge>
                                    @elseif ($statusAnggotaDisetujui)
                                        <x-core::badge variant="success" type="outline" size="sm"
                                            class="util_ml-8">
                                            Diterima
                                        </x-core::badge>
                                    @endif
                                @endif
                            </div>
                            <p style="font-size: 12px;font-weight: normal;color: #9aa4b2">
                                @if ($item['memberType'] == PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL)
                                    Dosen Internal
                                @elseif ($item['memberType'] == PengajuanPendanaanAnggota::JENIS_DOSEN_EKSTERNAL)
                                    {{-- Dosen Eksternal --}}
                                    Dosen Eksternal
                                @elseif ($item['memberType'] == PengajuanPendanaanAnggota::JENIS_MAHASISWA)
                                    {{-- Mahasiswa --}}
                                    Mahasiswa
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="util_d-flex util_flex-middle" style="gap: 8px">
                        @if (!$statusAnggotaDisetujui || $isJenisAnggotaMhs)
                            <x-core::button leading-icon="pencil-solid" variant="outline" size="xs"
                                wire:click="stateEditAnggota({{ $index }})" />
                        @endif
                        <x-core::button leading-icon="trash" variant="outline" size="xs"
                            class="btn__delete-member btn__delete" data-toggle="modal"
                            data-target="#modal-confirm-delete" data-index="{{ $index }}" />
                    </div>
                </div>
            @endforeach
            <div class="form-control">
                <x-core::button variant="primary" wire:click="stateAddAnggota()">
                    Tambahkan Anggota
                </x-core::button>
            </div>
        @else
            <div class="form-control">
                <label for="form-control-agenda-kegiatan" class="form-control__label" style="padding-bottom: 0;">
                    Daftar Anggota
                    <span class="important">*</span>
                </label>
                <div class="form-control__helper util_pt-0">
                    Tambahkan data anggota yang untuk tim penelitian Anda
                </div>
                <x-core::button variant="primary" wire:click="stateAddAnggota()" class="util_mt-12">
                    Tambahkan Anggota
                </x-core::button>
            </div>
        @endif

        @if (!empty($recordSavedAnggota))
            <x-core::modal title="Hapus Anggota Peneliti" variant="error" id="modal-confirm-delete">
                <x-core::modal.body>
                    Apakah anda yakin ingin menghapus anggota peneliti ini?
                    Karena data yang telah dihapus tidak dapat dikembalikan lagi.
                    <x-slot:footer>
                        <div class="grid cols-1 cols-sm-2">
                            <x-core::button variant="outline" data-dismiss="modal">
                                Batal
                            </x-core::button>
                            <x-core::button id="btn-modal-delete" class="confirm-delete" variant="destructive">
                                Hapus
                            </x-core::button>
                        </div>
                    </x-slot:footer>
                </x-core::modal.body>
            </x-core::modal>
        @endif
    </div>
    {{-- Modal Tambah Anggota --}}
    @php
        $isEdit = !empty($state['edit']);
    @endphp
    <x-core::modal title="{{ $isEdit ? 'Ubah' : 'Tambah' }} anggota" variant="primary" id="modal-add-anggota" width="600px" wire:ignore.self>
        <x-core::form method="POST">
            @if ($state['add'] || $state['edit'])
                <x-core::modal.body>
                    {{-- MemberType Option --}}
                    @foreach ($fields as $item)
                        @php
                            $item['name'] ??= $item['field'];
                            unset($item['field']);

                            $attributes = Page::buildAttributes($item);
                        @endphp
                        <x-core::controls.form {{ $attributes }} />
                    @endforeach
                    {{-- End MemberType Option --}}

                    @if ($memberType == PengajuanPendanaanAnggota::JENIS_DOSEN_EKSTERNAL)
                        @if ($externalDosenHaveAccount == 'belum')
                            <div class="form-control" wire:ignore>
                                <label class="form-control__label">Asal Institusi</label>
                                <div class="form-control__group">
                                    <select data-select="autocomplete" id="idPerguruanTinggi"
                                        name="id_perguruan_tinggi_luar"
                                        wire:model="dosenEksternalData.id_perguruan_tinggi_luar">
                                        <option value="">Pilih Asal Institusi</option>
                                        @foreach (Modules\Core\Models\PerguruanTinggi::options() as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-control__helper">
                                </div>
                            </div>
                        @endif
                    @endif

                    <x-slot:footer>
                        <div class="grid cols-1 cols-sm-2">
                            <x-core::button variant="outline" data-dismiss="modal">
                                Batalkan
                            </x-core::button>

                            <x-core::button variant="primary" class="util_ml-8" :disabled="empty($memberType)"
                                wire:click="saveAnggota({{ $isEdit ? $state['onEditBiodataId'] : null }})">
                                Simpan Anggota
                            </x-core::button>
                        </div>
                    </x-slot:footer>
                </x-core::modal.body>
            @endif
        </x-core::form>
    </x-core::modal>
</div>

@pushonce('scripts')
    @script
        <script>
            const initChoices = (el) => {

                // Store search results for each Choices instance
                let choicesInstances = {};
                const elmChoices = new Choices(el, {
                    allowHTML: true,
                    shouldSort: false,
                    searchEnabled: true,
                    removeItemButton: false,
                    searchResultLimit: 5,
                    searchFields: ['label'],
                    placeholder: true,
                    searchPlaceholderValue: 'Cari...',
                });

                // Assign the instance to the element ID
                choicesInstances[el.id] = elmChoices;

                let debounceTimer;
                el.addEventListener("search", function(event) {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        const searchQuery = event.detail.value;

                        // Reset the choices before making a new search
                        if (choicesInstances[el.id]) {
                            choicesInstances[el.id].clearChoices();
                        }

                        selectDispatchAutocomplete(el.id, searchQuery);
                    }, 200); // Debounce for 200ms
                });

                // Listen for search results from Livewire and update the specific Choices instance
                Livewire.on('searchResults', (results) => {
                    if (results.searchResults.length > 0) {
                        const instance = choicesInstances[el.id];
                        if (instance) {
                            instance.setChoices(results.searchResults, 'value', 'label', true);
                        }
                    }
                });

                // Clear search results after a choice is made
                el.addEventListener('choice', function(event) {
                    selectDispatchAutocomplete('', '');
                });
            };

            const selectDispatchAutocomplete = (elementId, search) => {
                Livewire.dispatch('autocomplete', {
                    id: elementId,
                    searchTerm: search
                });
            }

            Livewire.hook('element.init', ({
                component,
                el
            }) => {
                if (!el.hasAttribute("data-select")) {
                    return true;
                }

                initChoices(el);
            });

            //show modal
            Livewire.on('add-member', () => {
                setTimeout(() => {
                    document.getElementById("modal-add-anggota").classList.add("is-visible");
                }, 250);
            });

            index = null;
            //if btn__delete-member clicked get data-index
            document.querySelectorAll('.btn__delete').forEach(function(item) {
                item.addEventListener('click', function() {
                    index = this.getAttribute('data-index');
                    //set deleteAnggota($index) in confirm delete button
                    document.getElementById('btn-modal-delete').setAttribute('wire:click', 'deleteAnggota(' +
                        index + ')');
                });
            });
        </script>
    @endscript
@endpushonce
