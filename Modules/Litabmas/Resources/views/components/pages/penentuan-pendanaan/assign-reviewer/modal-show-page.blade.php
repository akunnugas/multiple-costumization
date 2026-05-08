@props([
    'dosenOption' => [],
])

@php
    use Modules\Litabmas\Models\PengajuanPendanaanReviewerKegiatan;

    $fieldCreate = [
        [
            'field' => 'id_biodata',
            'required' => true,
            'label' => 'Reviewer',
            'options' => $dosenOption['validate'],
            'variant' => 'search',
        ],
        [
            'field' => 'tipe_reviewer',
            'label' => 'Bertugas Sebagai',
            'options' => PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_OPTIONS,
            'required' => true
        ],
        [
            'field' => 'id_dokumen_sk',
            'label' => 'Upload Dokumen Surat Keterangan (SK)',
            'file_type' => ['pdf'],
            'max_size' => 1024 * 5,
        ],
    ];

    $fieldEdit = [
        [
            'field' => 'id_biodata',
            'required' => true,
            'label' => 'Reviewer',
            'options' => $dosenOption['validate'],
            'variant' => 'search',
        ],
        [
            'field' => 'tipe_reviewer',
            'label' => 'Bertugas Sebagai',
            'options' => PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_OPTIONS,
            'required' => true
        ],
        [
            'field' => 'id_dokumen_sk',
            'label' => 'Upload Dokumen Surat Keterangan (SK)',
            'file_type' => ['pdf'],
            'max_size' => 1024 * 5,
        ],
    ];
@endphp

<x-core::modal title="Tambahkan Reviewer" variant="primary" id="modal-tambah-reviewer">
    <x-core::form method="POST" action="{{ route('litabmas.penentuan-pendanaan.assign-reviewer.assign', $resourceId) }}">
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
    <x-core::form method="PUT"
        action="{{ route('litabmas.penentuan-pendanaan.assign-reviewer.update', $resourceId) }}">
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
            const url = '{{ route('litabmas.penentuan-pendanaan.assign-reviewer.show', $resourceId) }}';
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
        const elmTipeReviewerEdit = formEdit.querySelector('[name="tipe_reviewer"]');
        const elmTipeReviewerEditChoices = new Choices(elmTipeReviewerEdit, {
            allowHTML: false,
            searchEnabled: false,
            removeItemButton: false,
            shouldSort: false,
        });

        function showModal(encoded) {
            // setup option dosen & tipe reviewer
            const decoded = JSON.parse(atob(encoded));
            const optDosen = @json($dosenOption['validate']);
            const arrayOptDosen = Object.keys(optDosen).map((key) => {
                return {
                    value: parseInt(key),
                    label: optDosen[key],
                    selected: false
                };
            });
            const optTipeReviewer = @json(PengajuanPendanaanReviewerKegiatan::TIPE_REVIEWER_OPTIONS);
            const arrayOptTipeReviewer = Object.keys(optTipeReviewer).map((key) => {
                return {
                    value: key,
                    label: optTipeReviewer[key],
                    selected: false
                };
            });

            // first, clear all option
            elmReviewerEditChoices.clearStore();
            elmTipeReviewerEditChoices.clearStore();

            // merge decoded to arrayOptDosen, set paling atas
            arrayOptDosen.unshift({
                value: decoded.id_biodata,
                label: decoded.text,
                selected: true
            });
            elmReviewerEditChoices.setChoices(arrayOptDosen, 'value', 'label', false);

            // tipe reviewer
            arrayOptTipeReviewer.forEach((item) => {
                if (item.value === decoded.tipe_reviewer) {
                    item.selected = true;
                }
            });
            elmTipeReviewerEditChoices.setChoices(arrayOptTipeReviewer, 'value', 'label', false);

            // set form-control-id
            formEdit.querySelector('#form-control-id').value = decoded.id_biodata;

            modalEdit.classList.add('is-visible');
        }
    </script>
@endpushonce
