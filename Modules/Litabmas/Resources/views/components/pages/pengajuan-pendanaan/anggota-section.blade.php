@props([
    'title' => null,
    'icon' => null,
    'showCollapseInSection' => false,
    'fields' => [],
    'recordSavedAnggota' => [],
    'alertAnggota' => [],
    'apakahAdaKlasterPendanaan' => false,
    'apakahAdaSumberPendanaan' => false,
    'record' => [],
    'apakahButuhApproveSemuaAnggota' => false,
    'kategoriKlaster' => null,
    'minimalAnggota' => null,
    'maksimalAnggota' => null,
    'idPengajuanPendanaan' => null,
    'headerInfoKlaster' => [],
])

<x-core::layouts.create.card :$title :$icon :$showCollapseInSection>
    <x-core::layouts.html.alert :data="$alertAnggota"/>

    @include('litabmas::components.pages.pengajuan-pendanaan.header.header-pengajuan')

    @if(empty($apakahAdaKlasterPendanaan) || empty($apakahAdaSumberPendanaan))
        <x-core::layouts.html.alert :data="[
                'type' => 'warning',
                'message' => 'Pilih Sumber & Klaster Pendanaan terlebih dahulu untuk menentukan ' . $title . '.',
            ]"
        />
    @else
        @foreach($fields as $item)
            @php
                $item['name'] ??= $item['field'];
                unset($item['field']);

                $attributes = Page::buildAttributes($item);
            @endphp
            <x-core::controls.form {{ $attributes }} />
        @endforeach

        <hr class="dashed"/>
        @if($kategoriKlaster === \Modules\Litabmas\Models\KlasterPendanaan::KATEGORI_INDIVIDU)
            <x-core::layouts.html.alert :data="[
                    'type' => 'helper',
                    'message' => 'Klaster yang Anda pilih kategorinya adalah Individu, sehingga tidak memerlukan anggota.',
                ]"
            />
        @elseif($kategoriKlaster === \Modules\Litabmas\Models\KlasterPendanaan::KATEGORI_KELOMPOK)
            @php
                // harus dikasih key agar ketika parentnya diupdate, childnya diupdate juga
                // akan render ulang ketika key berubah
                $key = time();
            @endphp
            <livewire:litabmas::form-pengajuan-pendanaan-anggota :$record :$recordSavedAnggota :$maksimalAnggota :$key
                                                                 :$idPengajuanPendanaan :$apakahButuhApproveSemuaAnggota />
        @endif
    @endif

    @pushonce('scripts')
        <script>
            // Konfirmasi hapus member
            document.body.addEventListener('click', (e) => {
                if (e.target.closest('.btn__delete-member') != null) {
                    const targetBtnElem = e.target.closest('.btn__delete-member');
                    const modal = document.querySelector('#modal-confirm-delete');
                    modal.querySelector('.confirm-delete').setAttribute('wire:click', targetBtnElem.getAttribute(
                        'data-event'));
                }
            });
        </script>
    @endpushonce
</x-core::layouts.create.card>
