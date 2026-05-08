@php
    use Modules\Litabmas\Models\PengajuanPendanaanLaporanAkhir;

    $fieldCreate = [
        ['field' => 'id_dokumen_laporan_akhir', 'label' => 'Upload Dokumen',  'required' => true,
            'file_type' => PengajuanPendanaanLaporanAkhir::FILE_TYPE,
            'max_size' => PengajuanPendanaanLaporanAkhir::MAX_SIZE_FILE],
    ];
@endphp

<x-core::modal title="Laporan Akhir" variant="primary" id="modal-upload-laporan">
    <x-core::form method="PUT" action="{{ route('litabmas.pengajuan-pendanaan.laporan-akhir.upload', $resourceId) }}">
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
                <input type="hidden" name="jenis_laporan_akhir" id="jenis-laporan">
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
            formEdit.querySelector('label[for="form-control-id_dokumen_laporan_akhir"]')
                .textContent = 'Upload Dokumen ' + decoded.nama_jenis_laporan;
            formEdit.querySelector('label[for="form-control-id_dokumen_laporan_akhir"]')
                .insertAdjacentHTML('beforeend', '<span class="important">*</span>');

            // set jenis laporan
            formEdit.querySelector('#jenis-laporan').value = decoded.jenis_laporan;

            modalEdit.classList.add('is-visible');
        }
    </script>
@endpushonce
