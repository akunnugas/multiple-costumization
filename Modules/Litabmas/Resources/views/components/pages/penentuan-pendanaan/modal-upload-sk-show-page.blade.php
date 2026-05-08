@props(['idPengajuanPendanaan', 'data' => [], 'dokumen' => []])
@php
    use Modules\Litabmas\Models\PengajuanPendanaan;

    $fieldCreate = [
        [
            'field' => 'id_dokumen_sk_peneliti',
            'label' => 'Upload Dokumen Surat Keterangan (SK)',
            'required' => true,
            'file_type' => PengajuanPendanaan::FILE_TYPE_DOKUMEN_SK_PENELITI,
            'max_size' => PengajuanPendanaan::MAX_SIZE_FILE_DOKUMEN_SK_PENELITI,
        ],
    ];

@endphp

<x-core::modal title="Laporan Akhir" variant="primary" id="modal-upload-sk">
    <x-core::form method="PUT" action="{{ route('litabmas.penentuan-pendanaan.upload-sk', $resourceId) }}">
        <x-core::modal.body>
            <div class="card card-modal-penentuan-pendanaan">
                <div class="grid">
                    <x-litabmas::layouts.detail.line :data="$data" />
                </div>
            </div>
            <div class="modal__content-input" style="margin: 0.7rem 0 0.3rem 0">
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

{{--  Modal delete dokumen --}}
@if (!empty($dokumen['id']))
    <x-core::modal.delete id="modal-destroy-sk" title="Hapus Dokumen SK Peneliti">
        <x-core::form method="DELETE"
            action="{{ route('litabmas.penentuan-pendanaan.destroy-sk', [$resourceId, $dokumen['id']]) }}">
            <x-core::modal.delete-body message="Apakah Anda yakin ingin menghapus dokumen ini?">
                <x-slot:button type="submit">
                    Hapus
                </x-slot:button>
            </x-core::modal.delete-body>
        </x-core::form>
    </x-core::modal.delete>
@endif
