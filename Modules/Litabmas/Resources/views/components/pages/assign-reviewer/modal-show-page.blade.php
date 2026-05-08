@props([
    'dosenOption' => []
])

@php
    $fieldCreate = [
        ['field' => 'id_biodata', 'required' => true, 'label' => 'Reviewer', 'options' => $dosenOption['validate'],
            'variant' => 'search'],
        ['field' => 'id_dokumen_sk', 'label' => 'Upload Dokumen Surat Keterangan (SK)',
            'file_type' => ['pdf'], 'max_size' => (1024 * 5)
        ],
    ];

    $fieldEdit = [
        ['field' => 'id_biodata', 'required' => true, 'label' => 'Reviewer', 'options' => $dosenOption['validate'],
            'variant' => 'search'],
        ['field' => 'id_dokumen_sk', 'label' => 'Upload Dokumen Surat Keterangan (SK)',
            'file_type' => ['pdf'], 'max_size' => (1024 * 5)
        ],
    ];
@endphp

<x-core::modal title="Tambahkan Reviewer" variant="primary" id="modal-tambah-reviewer">
    <x-core::form method="POST" action="{{ route('litabmas.penilaian-administrasi.assign-reviewer.assign', $resourceId) }}">
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

<x-core::modal title="Edit Reviewer" variant="primary" id="modal-edit-reviewer">
    <x-core::form method="PUT" action="{{ route('litabmas.penilaian-administrasi.assign-reviewer.update', $resourceId) }}">
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

<x-core::modal.delete id="modal_delete_reviewer" title="Hapus Reviewer">
    <x-core::form method="DELETE">
        <x-core::modal.delete-body :resourceTitle="'Reviewer'">
            <x-slot:button type="submit">
                Hapus
            </x-slot:button>
        </x-core::modal.delete-body>
    </x-core::form>
</x-core::modal.delete>

@pushonce('scripts')
    <script>
        function deleteRecord(encoded) {
            const url = '{{ route('litabmas.penilaian-administrasi.assign-reviewer.show', $resourceId) }}';
            List.deleteRecord(encoded, url, 'modal_delete_reviewer');
        }

        const modalEdit = document.getElementById('modal-edit-reviewer');
        const formEdit = modalEdit.querySelector('form');
        const elmReviewerEdit = formEdit.querySelector('[name="id_biodata"]');
        const elmReviewerEditChoices = new Choices(elmReviewerEdit, {
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
            elmReviewerEditChoices.clearStore();

            // merge decoded to arrayOpt, set paling atas
            arrayOpt.unshift({value: decoded.id_biodata, label: decoded.text, selected: true});
            elmReviewerEditChoices.setChoices(arrayOpt, 'value', 'label', false);

            // set form-control-id
            formEdit.querySelector('#form-control-id').value = decoded.id_biodata;

            modalEdit.classList.add('is-visible');
        }
    </script>
@endpushonce
