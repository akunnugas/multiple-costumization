@php
    $fieldCreate = [
        ['field' => 'id_dokumen_output', 'label' => 'Upload Dokumen', 'file_type' => ['pdf'],
            'max_size' => (1024 * 10), 'required' => true],
    ];
@endphp

<x-core::modal title="Output Penelitian" variant="primary" id="modal-upload-output">
    <x-core::form method="PUT" action="{{ route('litabmas.pengajuan-pendanaan.output-penelitian.upload', $resourceId) }}">
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
                <input type="hidden" name="id_jenis_output_penelitian" id="jenis-output-penelitian">
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

@pushonce('scripts')
    <script>
        function showModalUpload(encoded) {
            const modalEdit = document.getElementById('modal-upload-output');
            const formEdit = modalEdit.querySelector('form');
            const decoded = JSON.parse(atob(encoded));

            // set label
            formEdit.querySelector('label[for="form-control-id_dokumen_output"]')
                .textContent = 'Upload File ' + decoded.nama_output;
            formEdit.querySelector('label[for="form-control-id_dokumen_output"]')
                .insertAdjacentHTML('beforeend', '<span class="important">*</span>');

            // set jenis laporan
            formEdit.querySelector('#jenis-output-penelitian').value = decoded.id_jenis_output_penelitian;

            modalEdit.classList.add('is-visible');
        }
    </script>
@endpushonce
