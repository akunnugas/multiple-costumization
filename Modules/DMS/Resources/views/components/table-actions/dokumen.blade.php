@php
    use Modules\DMS\Models\Dokumen;

    $isFolder = isset($data['kode_folder']);
    $canUpdate = $canUpdate ?? $canCreate ?? false;

    if (!$isFolder) {
        $downloadUrl = route('dms.files.download', $data['slug']);
        $url = route('dms.files.preview', $data['slug']);
    } else {
        $url = route('dms.folder.show', $data['kode_folder']);
    }
@endphp

<div class="cell-action" style="display: flex; align-items: center; gap: 4px;">
    <x-core::button leading-icon="eye-solid" variant="outline" size="xs" :href="$url" />

    @php
        $items = [];

        if (!$isFolder) {
            if ($canUpdate) {
                $items[] = [
                    'label' => 'Ubah',
                    'icon' => 'pencil',
                    'attributes' => [
                        'data-toggle' => 'modal',
                        'data-target' => '#edit-' . $data['slug'],
                    ],
                ];
            }

            $items[] = [
                'label' => 'Bagikan Link',
                'icon' => 'share',
                'attributes' => [
                    'data-toggle' => 'modal',
                    'data-target' => '#sharing-' . $data['slug'],
                ],
            ];

            $items[] = [
                'label' => 'Download',
                'href' => $downloadUrl,
                'icon' => 'arrow-down-tray',
            ];
        }

        if ($canDelete) {
            $encoded = base64_encode(
                json_encode([
                    'id' => $data['id'],
                    'text' => $data['text'] ?? null,
                ]),
            );

            $funcName = ucfirst($data['datatype'] ?? 'file');
            $items[] = [
                'label' => 'Pindahkan ke arsip',
                'href' => "javascript:delete$funcName('$encoded')",
                'icon' => 'archive-box',
                'attributes' => [
                    'style' => 'color: red',
                ],
            ];
        }
    @endphp

    @if(!empty($items))
        <x-dms::dropdown :items="$items" position="right">
            <x-core::button leading-icon="ellipsis-horizontal" variant="outline" size="xs" data-toggle="dropdown" />
        </x-dms::dropdown>
    @endif

    @if ($canDelete ?? false)
        @pushOnce('scripts')
            <script>
                // NOTE: cant use destroy cuz need id
                function deleteFile(encoded) {
                    return List.deleteRecord(encoded, "{{ route('dms.files.store') }}");
                }

                function deleteFolder(encoded) {
                    return List.deleteRecord(encoded, "{{ route('dms.folder.store') }}");
                }
            </script>
        @endpushOnce
    @endif
</div>

@if (!$isFolder)
    @pushOnce('end')
        @php
            $editTitle = "Ubah file \"{$data['nama_dokumen']}.{$data['extension_versi_terbaru']}\"";
            $sharingTitle = "Bagikan link \"{$data['nama_dokumen']}.{$data['extension_versi_terbaru']}\"";

            $parents = array_combine(
                array_column($folders, 'id'),
                array_map(function($item) {
                    $depth = $item->depth - 1;
                    $indent = str_repeat('&nbsp;', $depth * 6);

                    return $indent . $item->nama_folder;
                }, $folders),
            );
        @endphp

        <x-core::modal :title="$editTitle" class="sharing-modal" variant="primary" id="edit-{{ $data['slug'] }}">
            <x-core::form :action="route('dms.files.update',$data['id'])" method="PATCH">
                <x-core::modal.body>
                    <div class="grid cols-1">
                        <div class="form-control">
                            <label for="id_folder" class="form-control__label">Folder<span
                                    class="important">*</span></label>
                            <x-core::controls.input label="folder"
                                                    name="id_folder" :options="$parents" value="{{ $data['id_folder'] }}" required />
                        </div>
                    </div>

                    <x-slot:footer>
                        <div style="display: flex; justify-content: end; gap: 12px">
                            <x-core::button data-dismiss="modal" variant="outline" type="button">
                                Batal
                            </x-core::button>

                            <x-core::button variant="primary" type="submit" data-dismiss="modal">
                                Simpan
                            </x-core::button>
                        </div>
                    </x-slot:footer>
                </x-core::modal.body>
            </x-core::form>
        </x-core::modal>

        <x-core::modal :title="$sharingTitle" class="sharing-modal" variant="primary" id="sharing-{{ $data['slug'] }}">
            <x-core::form :action="route('dms.folder.store')" method="POST">
                <x-core::modal.body>
                    <div class="access-list">
                        <h3 class="access-list__title">Orang yang memiliki akses</h3>

                        @if ($data['dibuat_oleh'])
                            <div class="owner-info__container">
                                <div class="owner-info__inner">
                                    <span class="header__user-avatar">
                                        <span class="avatar avatar_sm">
                                            <img src="{{ Page::quantumAsset('images/example-profile.jpg') }}"
                                                 alt="User Avatar" width="44">
                                        </span>
                                    </span>

                                    <div class="owner-info__meta">
                                        <span class="owner__name">{{ $data['created_by_name'] }}</span>
                                        <span class="owner__email">{{ $data['created_by_email'] }}</span>
                                    </div>
                                </div>

                                <span class="owner-info__status">Pemilik</span>
                            </div>
                        @endif
                    </div>

                    <hr />

                    <div class="visibility-option">
                        <span class="globe-icon">
                            <x-core::icon type="globe-americas-solid" />
                        </span>

                        <div class="visibility-option__content">
                            <div class="visibility-option__text">
                                <h4>Jadikan file publik</h4>
                                <p>Semua orang yang memiliki link bisa mengakses file ini</p>
                            </div>

                            <div class="form-control switch-container">
                                <x-core::controls.input control="switch" id="visibility-{{ $data['slug'] }}"
                                                        value="{{ $data['visibilitas'] === Dokumen::VISIBILITY_PUBLIC }}" name="visibility"
                                                        :disabled="!$canUpdate" />
                            </div>
                        </div>
                    </div>

                    <x-slot:footer>
                        <div style="display: flex; justify-content: end; gap: 12px">
                            <x-core::button id="copy-{{ $data['slug'] }}" variant="outline" type="button">
                                <x-core::icon type="link-mini" /> Salin Link
                            </x-core::button>

                            <x-core::button variant="primary" type="submit" data-dismiss="modal">
                                Simpan
                            </x-core::button>
                        </div>
                    </x-slot:footer>
                </x-core::modal.body>
            </x-core::form>
        </x-core::modal>

        <script type="module">
            const switchEl = Dokumen.querySelector('#visibility-{{ $data['slug'] }}');
            switchEl.addEventListener('change', async (ev) => {
                await fetch(@js(route('dms.files.update', $data['id'])), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        _token: @js(csrf_token()),
                        visibility: ev.target.checked ? @js(Dokumen::VISIBILITY_PUBLIC) : @js(Dokumen::VISIBILITY_PRIVATE)
                    })
                });
            });

            const copyBtn = Dokumen.querySelector('#copy-{{ $data['slug'] }}');
            copyBtn.addEventListener('click', async () => {
                const url = @js(route('dms.files.raw', $data['slug']));
                await navigator.clipboard.writeText(url);

                toast({
                    html: `<x-core::icon type="check-circle-solid" /> Berhasil salin link file`
                });
            });
        </script>
    @endpushonce
@endif
