@props([
    'title' => null,
    'icon' => null,
    'showCollapseInSection' => false,
    'isDisableEdit' => false,
    'state' => [],
    'fieldsBidangIlmuDanTemaKegiatan' => [],
    'recordSavedBidangIlmuTemaKegiatan' => [],
    'usedBidangIlmuTemaKegiatan'=> [],
    'alertBidangIlmuTemaKegiatan' => [],
])
<x-core::layouts.create.card :$title :$icon :$showCollapseInSection>
    <x-core::layouts.html.alert :data="$alertBidangIlmuTemaKegiatan"/>

    @if(!empty($state['addBidangIlmuDanTemaKegiatan']) || !empty($state['editBidangIlmuDanTemaKegiatan']))
        {{--State ketika create/edit--}}
        @php
            $isEdit = !empty($state['editBidangIlmuDanTemaKegiatan']);
        @endphp
        @foreach($fieldsBidangIlmuDanTemaKegiatan as $item)
            @php
                $item['name'] ??= $item['field'];
                unset($item['field']);

                $attributes = Page::buildAttributes($item);
            @endphp
            <x-core::controls.form {{ $attributes }} />
        @endforeach
        <div class="util_d-flex">
            <x-core::button variant="ghost"
                wire:click="stateBackBidangIlmuDanTemaKegiatan('{{ $isEdit ? 'edit' : 'add' }}')">
                Batalkan
            </x-core::button>
            <x-core::button variant="primary" class="util_ml-8"
                wire:click="saveBidangIlmuDanTemaKegiatan({{ $state['onEditIdBidangIlmu'] ?? null }})"> Simpan Data
            </x-core::button>
        </div>
    @elseif(!empty($recordSavedBidangIlmuTemaKegiatan))
        {{--State view ketika sudah memiliki min satu bidang ilmu dan tema kegiatan--}}
        <div class="form-control">
            <label for="form-control-bidang-ilmu-dan-tema-kegiatan" class="form-control__label">
                Bidang Ilmu dan Tema
                <span class="important">*</span>
            </label>
            <div class="form-control__helper util_pt-0">
                <x-core::table>
                    <div class="box-table">
                        <div class="box-table__content">
                            <div class="table-max">
                                <table>
                                    <thead>
                                    <tr>
                                        <th class="cell-check cell-center">No</th>
                                        <th>Nama Bidang Ilmu</th>
                                        <th>Tema</th>
                                        @if (!$isDisableEdit)
                                            <th class="cell-action">Aksi</th>
                                        @endif
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $bidangIlmuOptions = $fieldsBidangIlmuDanTemaKegiatan[0]['options'];
                                        $temaKegiatanOptions = $fieldsBidangIlmuDanTemaKegiatan[1]['options'];
                                    @endphp
                                    @foreach($recordSavedBidangIlmuTemaKegiatan as $idBidangIlmu => $temaKegiatans)
                                        @php
                                            $namaBidangIlmu = $bidangIlmuOptions[$idBidangIlmu] ?? $idBidangIlmu;

                                            // get item pertama
                                            $temaKegiatan = $temaKegiatans[0];
                                            $namaTemaKegiatan = $temaKegiatanOptions[$temaKegiatan] ?? $temaKegiatan;

                                            // hitung sisa item selain item pertama
                                            $count = count($temaKegiatans) - 1;
                                            if ($count > 0) {
                                                $namaTemaKegiatan = $namaTemaKegiatan . ' & ' . $count . ' lainnya';
                                            }

                                            $hideDelete = isset($usedBidangIlmuTemaKegiatan[$idBidangIlmu])? true : false;
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $namaBidangIlmu }}</td>
                                            <td>{{ $namaTemaKegiatan }}</td>
                                            @if (!$isDisableEdit)
                                                <td class="cell-check cell-center">
                                                    <div class="dropdown-group"
                                                        style="display: flex; align-items: center; gap: 4px;">
                                                            <x-core::button leading-icon="trash" variant="outline"
                                                                            size="xs"
                                                                            class="btn__delete-bidang-ilmu-tema-kegiatan btn__delete"
                                                                            data-toggle="modal"
                                                                            data-target="#modal-confirm-delete"
                                                                            data-event="deleteBidangIlmuDanTemaKegiatan({{ $idBidangIlmu }})"
                                                                            disabled="{{ $hideDelete }}"
                                                            />
                                                        <x-core::button leading-icon="pencil" variant="outline"
                                                                        size="xs"
                                                                        wire:click="stateEditBidangIlmuDanTemaKegiatan({{ $idBidangIlmu }})"
                                                                        disabled="{{ $hideDelete }}"
                                                        />
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </x-core::table>
            </div>
            @if (!$isDisableEdit)
                <x-core::button variant="primary" wire:click="stateAddBidangIlmuDanTemaKegiatan()" class="util_mt-4">
                    Tambahkan Data
                </x-core::button>
            @endif
        </div>
    @else
        {{--State view ketika tidak memiliki satupun bidang ilmu dan tema kegiatan--}}
        <div class="form-control">
            <label for="form-control-bidang-ilmu-dan-tema-kegiatan" class="form-control__label">
                Tambahkan Bidang Ilmu dan Tema
                <span class="important">*</span>
            </label>
            <div class="form-control__helper util_pt-0">
                Silahkan Tambahkan data bidang ilmu dan tema sesuai klaster
            </div>
            <x-core::button variant="primary" wire:click="stateAddBidangIlmuDanTemaKegiatan()" class="util_mt-8">
                Tambahkan Data
            </x-core::button>
        </div>
    @endif

    @if(!empty($recordSavedBidangIlmuTemaKegiatan))
        <x-core::modal title="Hapus Bidang Ilmu & Tema" variant="error" id="modal-confirm-delete">
            <x-core::modal.body>
                Apakah anda yakin ingin menghapus bidang ilmu & tema ini?
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

    @pushonce('scripts')
        <script>
            // Konfirmasi hapus bidang ilmu dan tema kegiatan
            document.body.addEventListener('click', (e) => {
                if (e.target.closest('.btn__delete-bidang-ilmu-tema-kegiatan') != null) {
                    const targetBtnElem = e.target.closest('.btn__delete-bidang-ilmu-tema-kegiatan');
                    const modal = document.querySelector('#modal-confirm-delete');
                    modal.querySelector('.confirm-delete').setAttribute('wire:click', targetBtnElem.getAttribute(
                        'data-event'));
                }
            });
        </script>
    @endpushonce
</x-core::layouts.create.card>
