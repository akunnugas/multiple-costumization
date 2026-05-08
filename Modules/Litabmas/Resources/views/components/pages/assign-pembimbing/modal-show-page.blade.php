@props([
    'dosenOption' => []
])

@php
    $fieldCreate = [
        ['field' => 'id_biodata', 'required' => true, 'label' => 'Pembimbing', 'options' => $dosenOption['validate'],
            'variant' => 'search'],
        ['field' => 'id_dokumen_sk', 'label' => 'Upload Dokumen Surat Keterangan (SK)',
            'file_type' => ['pdf'], 'max_size' => (1024 * 5)
        ],
    ];

    $fieldEdit = [
        ['field' => 'id_biodata', 'required' => true, 'label' => 'Pembimbing', 'options' => $dosenOption['validate'],
            'variant' => 'search'],
        ['field' => 'id_dokumen_sk', 'label' => 'Upload Dokumen Surat Keterangan (SK)',
            'file_type' => ['pdf'], 'max_size' => (1024 * 5)
        ],
    ];
@endphp

<x-core::modal title="Tambahkan Pembimbing" variant="primary" id="modal-tambah-pembimbing">
    <x-core::form method="POST" action="{{ route('litabmas.penentuan-pendanaan.assign-pembimbing.assign', $resourceId) }}">
        <x-core::modal.body>
            <div class="modal__content-input">
                @foreach ($fieldCreate as $item)
                    @php
                        $item['name'] ??= $item['field'];
                        unset($item['field']);

                        $attributes = Page::buildAttributes($item);
                    @endphp
                    <x-core::controls.form {{ $attributes }} />
                @endforeach
            </div>
            <x-slot:footer>
                <div class="grid cols-1 cols-sm-2">
                    <x-core::button variant="outline" data-dismiss="modal">
                        Batalkan
                    </x-core::button>
                    <x-core::button variant="primary" type="submit">
                        Simpan
                    </x-core::button>
                </div>
            </x-slot:footer>
        </x-core::modal.body>
    </x-core::form>
</x-core::modal>

<x-core::modal title="Edit Pembimbing" variant="primary" id="modal-edit-pembimbing">
    <x-core::form method="PUT" action="{{ route('litabmas.penentuan-pendanaan.assign-pembimbing.update', $resourceId) }}">
        <x-core::modal.body>
            <div class="modal__content-input">
                @foreach ($fieldEdit as $item)
                    @php
                        $item['name'] ??= $item['field'];
                        unset($item['field']);

                        $attributes = Page::buildAttributes($item);
                    @endphp
                    <x-core::controls.form {{ $attributes }} />
                @endforeach
                <input type="hidden" name="old_id_biodata" id="form-control-id">
            </div>
            <x-slot:footer>
                <div class="grid cols-1 cols-sm-2">
                    <x-core::button variant="outline" data-dismiss="modal">
                        Batalkan
                    </x-core::button>
                    <x-core::button variant="primary" type="submit">
                        Simpan
                    </x-core::button>
                </div>
            </x-slot:footer>
        </x-core::modal.body>
    </x-core::form>
</x-core::modal>

<x-core::modal.delete id="modal_delete" title="Hapus Pembimbing">
    <x-core::form method="DELETE">
        <x-core::modal.delete-body :resourceTitle="'Pembimbing'">
            <x-slot:button type="submit">
                Hapus
            </x-slot:button>
        </x-core::modal.delete-body>
    </x-core::form>
</x-core::modal.delete>

@pushonce('scripts')
    <script>
        function deleteRecord(encoded) {
            const url = '{{ route('litabmas.penentuan-pendanaan.assign-pembimbing.show', $resourceId) }}';
            List.deleteRecord(encoded, url, 'modal_delete');
        }

        const modalEdit = document.getElementById('modal-edit-pembimbing');
        const formEdit = modalEdit.querySelector('form');
        const elmPembimbingEdit = formEdit.querySelector('[name="id_biodata"]');
        const elmPembimbingEditChoices = new Choices(elmPembimbingEdit, {
            allowHTML: true,
            searchEnabled: true,
            removeItemButton: false,
            shouldSort: false,
        });

        function showModal(encoded) {
            const decoded = JSON.parse(atob(encoded));
            const opt = @json($dosenOption['validate']);
            const arrayOpt = Object.keys(opt).map((key) => {
                return {
                    value: parseInt(key),
                    label: opt[key],
                    selected: false
                };
            });

            // first, clear all option
            elmPembimbingEditChoices.clearStore();

            // merge decoded to arrayOpt, set paling atas
            arrayOpt.unshift({value: decoded.id_biodata, label: decoded.text, selected: true});
            elmPembimbingEditChoices.setChoices(arrayOpt, 'value', 'label', false);

            // set form-control-id
            formEdit.querySelector('#form-control-id').value = decoded.id_biodata;

            modalEdit.classList.add('is-visible');
        }
    </script>
@endpushonce
