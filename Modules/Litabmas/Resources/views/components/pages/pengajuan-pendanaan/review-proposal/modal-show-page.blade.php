@php
    use Modules\Litabmas\Models\PengajuanPendanaan;

    $field = ['field' => 'id_dokumen', 'label' => 'Upload Dokumen Perbaikan Proposal', 'required' => true,
        'file_type' => PengajuanPendanaan::FILE_TYPE_PROPOSAL,
        'max_size' => PengajuanPendanaan::MAX_SIZE_FILE_PROPOSAL];
@endphp

<x-core::modal title="Perbaikan Proposal" variant="primary" id="modal-upload-revisian-proposal">
    <x-core::form method="POST" action="{{ route('litabmas.pengajuan-pendanaan.review-proposal.store', $resourceId) }}">
        <x-core::modal.body>
            <div class="modal__content-input">
                @php
                    $field['name'] ??= $field['field'];
                    unset($field['field']);

                    $attributes = Page::buildAttributes($field);
                @endphp
                <x-core::controls.form {{ $attributes }} />
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

<x-core::modal title="Perbaikan Proposal" variant="primary" id="modal-edit-revisian-proposal">
    <x-core::form method="PUT" action="">
        <x-core::modal.body>
            <div class="modal__content-input">
                @php
                    $field['name'] ??= $field['field'];
                    unset($field['field']);

                    $attributes = Page::buildAttributes($field);
                @endphp
                <x-core::controls.form {{ $attributes }} />
                <div class="dl-item" style="display: flex; flex-direction: column;" id="id_dokumen-preview">
                    <div class="attachment attachment_loading">
                        <div class="attachment__wrapper">
                            <div class="attachment__wrapper-icon">
                                <img src="{{ asset("images/pdf-solid.svg") }}">
                            </div>
                            <div class="attachment__wrapper-text">
                                <div class="attachment__title">
                                    <h3 class="attachment__heading util_pb-0"></h3>
                                    <span class="attachment__description"></span>
                                </div>
                            </div>
                            <div class="attachment__wrapper-action">
                                <a class="btn btn_icon btn_outline btn_xs" href="" download="foo" target="_blank">
                                    <span class="icon icon-eye"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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
        const url = '{{ route('litabmas.pengajuan-pendanaan.review-proposal.index', $resourceId) }}';

        function editRecord(encoded) {
            const modalEdit = document.getElementById('modal-edit-revisian-proposal');
            const decoded = JSON.parse(atob(encoded));

            modalEdit.querySelector('form').action = url + '/' + decoded.id;
            modalEdit.querySelector('.attachment__heading').textContent = decoded.text;
            modalEdit.querySelector('.attachment__description').textContent = decoded.last_version_size;
            modalEdit.querySelector('.attachment__wrapper-action a').href = decoded.temp_url;
            modalEdit.classList.add('is-visible');
        }
    </script>
@endpushonce
