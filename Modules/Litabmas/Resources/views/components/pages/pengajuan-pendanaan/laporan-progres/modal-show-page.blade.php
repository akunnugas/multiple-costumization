@php
    $fieldCreate = [
        ['field' => 'id_dokumen_laporan_progres', 'label' => 'Upload Dokumen', 'file_type' => ['pdf'],
            'max_size' => (1024 * 10), 'required' => true],
    ];
@endphp

<x-core::modal title="Progress Report" variant="primary" id="modal-upload-laporan">
    <x-core::form method="PUT" action="{{ route('litabmas.pengajuan-pendanaan.laporan-progres.upload', $resourceId) }}">
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
                <input type="hidden" name="jenis_laporan_progres" id="jenis-laporan">
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
            const modalEdit = document.getElementById('modal-upload-laporan');
            const formEdit = modalEdit.querySelector('form');
            const decoded = JSON.parse(atob(encoded));

            // set label
            formEdit.querySelector('label[for="form-control-id_dokumen_laporan_progres"]')
                .textContent = 'Upload Dokumen ' + decoded.nama_jenis_laporan;
            formEdit.querySelector('label[for="form-control-id_dokumen_laporan_progres"]')
                .insertAdjacentHTML('beforeend', '<span class="important">*</span>');

            // set jenis laporan
            formEdit.querySelector('#jenis-laporan').value = decoded.jenis_laporan;

            modalEdit.classList.add('is-visible');
        }
    </script>
@endpushonce
