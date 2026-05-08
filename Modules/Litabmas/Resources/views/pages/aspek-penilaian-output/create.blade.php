@pushOnce('head')
    @vite('resources/scss/custom-utils.scss')
    <style>
        .card .card__body {
            padding-top: 0 !important;
        }
        .card__body .box-table__content {
            padding: 0 !important;
            border-top: none !important;
        }

        .form-nav~.container {
            max-width: 45.875rem !important;
        }
    </style>
@endPushOnce

@php
    $alertInfo = [
        'message' => 'Anda hanya dapat menambahkan 2 atau 4 jawaban untuk setiap pertanyaan.',
        'type' => 'helper',
    ];
    $backSubFooter = route('litabmas.aspek-penilaian-output.index');
    $showCollapseInSection = true;
@endphp

<x-core::livewire.layouts.create-edit :data="$data ?? []" :$routeName :$alert :$backSubFooter>
    <x-core::layouts.create.card :$showCollapseInSection :title="'Kriteria Penilaian Luaran'" :icon="'cube'" :data="[]">
        <x-core::layouts.html.alert :data="$alertInfo"/>

        @foreach ($fields as $item)
            @php
                $item['name'] ??= $item['field'];
                unset($item['field']);

                $attributes = Page::buildAttributes($item);
            @endphp
            @if(!empty($item['is_opsi_jawaban']))
                <x-core::controls.form label="{{ $item['label'] }}" required="{{ true }}"
                                       name="{{ $item['name'] }}">
                    <div class="util_d-flex col-12 util_flex-between">
                        <div class="col-12" style="width: {{ $item['no'] > 2 ? 90 : 100  }}%">
                            <x-core::controls.input {{ $attributes }} />
                        </div>

                        {{-- Karena min 2, maka no 1 dan 2 tidak boleh dihapus --}}
                        @if($item['no'] > 2)
                            <x-core::button leading-icon="trash" variant="destructive"
                                            class="btn__delete-opsional btn__delete"
                                            data-toggle="modal"
                                            data-target="#modal-confirm-delete-opsional"
                                            data-event="deleteOpsiJawaban({{ $item['no'] }})" />
                        @endif
                    </div>
                </x-core::controls.form>
            @else
                <x-core::controls.form {{ $attributes }} />
            @endif
        @endforeach

        @if($jumlahOpsiJawaban < $maxOpsiJawaban)
            <x-core::controls.form :showHelper="false" :showLabel="false">
                <x-core::button variant="ghost" wire:click="addOpsiJawaban" class="util_p-0">
                    <i class="icon icon-plus-mini"></i>
                    Tambah Opsi Jawaban
                </x-core::button>
            </x-core::controls.form>
        @endif
    </x-core::layouts.create.card>

    <x-core::modal title="Hapus Anggota Peneliti" variant="error" id="modal-confirm-delete-opsional">
        <x-core::modal.body>
            Apakah anda yakin ingin menghapus opsi jawaban ini?
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
</x-core::livewire.layouts.create-edit>

@pushonce('scripts')
    <script>
        // Konfirmasi hapus member
        document.body.addEventListener('click', (e) => {
            if (e.target.closest('.btn__delete-opsional') != null) {
                const targetBtnElem = e.target.closest('.btn__delete-opsional');
                const modal = document.querySelector('#modal-confirm-delete-opsional');
                modal.querySelector('.confirm-delete').setAttribute('wire:click', targetBtnElem.getAttribute(
                    'data-event'));
            }
        });
    </script>
@endpushonce
