@php
    use Modules\Gate\Models\Modul;

    $isInFolder = isset($folder);
    $isUserCreated = $isInFolder && $folder->dibuat_oleh !== null;

    $backUrl = $isUserCreated ? route('dms.user-files.index') : route('dms.index');

    $shared = [
        'canCreate' => $canCreate ?? false,
        'canDelete' => $canDelete ?? false,
        'folders' => $folders ?? null,
    ];

    if ($isInFolder) {
        if ($folder->kode_parent !== null && $folder->kode_parent !== Modul::CODE_DMS) {
            $backUrl = route('dms.folder.show', $folder->kode_parent);
        }

        $fullPath = array_map(function ($item) {
            return [
                'label' => $item['nama_folder'],
                'path' => route('dms.folder.show', $item['kode_folder'], false),
                'active' => true,
                'showLink' => true,
            ];
        }, $folder->fullFolderPathArray(true));

        $breadcrumb['items'] = [
            [
                'label' => $isUserCreated ? __('dms::pages.my_files') : 'DMS',
                'path' => $isUserCreated ? 'dms/my-files' : 'dms',
                'active' => 10,
                'showLink' => true,
            ],
            ...$fullPath,
        ];

        $shared['breadcrumb'] = $breadcrumb;
        $shared['folderId'] = $folder->id;
    }

    View::share($shared);
@endphp

@pushOnce('head')
    @vite('Modules/DMS/Resources/assets/sass/folder-view.scss')
@endPushOnce

<x-dms::layouts.dashboard :$title :$backUrl>
    <x-dms::toast headless />

    <x-slot:action>
        @if ($canDelete ?? false)
            <x-core::button id="btn_delete" variant="destructive" leading-icon="trash-solid" class="btn_vr-right"
                :icon="true" />
        @endif

        @if (!empty($canCreate))
            <x-core::button leadingIcon="folder-plus-solid" data-toggle="modal" data-target="#new-folder">Tambah
                Folder</x-core::button>

            <x-core::modal title="Buat folder baru" variant="primary" id="new-folder">
                <x-core::form :action="route('dms.folder.store')" method="POST">
                    <x-core::modal.body>
                        @if (isset($folder))
                            <input type="hidden" name="id_parent" value="{{ $folder->id }}" />
                        @endif

                        <div class="grid cols-1">
                            <x-core::controls.form label="Nama folder" name="nama_folder" placeholder="Masukkan nama folder"
                                required />
                            {{-- <x-core::controls.form label="Unit pengelola folder" name="role" placeholder="Pilih unit Perguruan Tinggi yang bisa akses & kelola folder" :options="[]" required /> --}}

                            <div class="form-control">
                                <label for="" class="form-control__label">Unit pengelola folder<span
                                        class="important">*</span></label>
                                <x-core::controls.input label="unit Perguruan Tinggi yang bisa akses & kelola folder"
                                    name="id_unit_kerja" :options="$organizations" required />
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="grid cols-5">
                                <x-core::button variant="outline" type="button" class="col-start-3"
                                    data-dismiss="modal">
                                    Batalkan
                                </x-core::button>

                                <x-core::button variant="primary" type="submit" class="col-start-4">
                                    Simpan
                                </x-core::button>
                            </div>
                        </x-slot:footer>
                    </x-core::modal.body>
                </x-core::form>
            </x-core::modal>

            @if ($isUserCreated)
                <x-core::button leadingIcon="Dokumen-plus-solid" data-toggle="modal" data-target="#new-file">Upload
                    file</x-core::button>

                <x-core::modal title="Upload file baru" variant="primary" id="new-file">
                    <x-core::form :action="route('dms.files.store')" method="POST">
                        <x-core::modal.body>
                            <input type="hidden" name="kode_folder" value="{{ @$folder->kode_folder }}" />

                            <div class="grid cols-1">
                                <x-core::controls.form label="Nama" name="name" placeholder="Masukkan nama file"
                                    required />
                                <x-core::controls.form label="File" name="file" type="file" :file_type="['jpg', 'png', 'pdf', 'doc']"
                                    required />
                            </div>

                            <x-slot:footer>
                                <div class="grid cols-5">
                                    <x-core::button variant="outline" type="button" class="col-start-3"
                                        data-dismiss="modal">
                                        Batalkan
                                    </x-core::button>

                                    <x-core::button variant="primary" type="submit" class="col-start-4">
                                        Simpan
                                    </x-core::button>
                                </div>
                            </x-slot:footer>
                        </x-core::modal.body>
                    </x-core::form>
                </x-core::modal>
            @endif
        @endif
    </x-slot:action>

    <div class="card items">
        @if (!empty($filter))
            <div class="box-table__header">
                <x-dms::layouts.list.filter :data="$filter" />
            </div>
        @endif

        <div class="card__body">
            <div class="files">
                <x-dms::layouts.list.list :$data :$header :$order :$filter :canDelete="$canDelete ?? false" :sort="$order['no']"
                    :sortDesc="$order['desc']">
                    <x-slot:handler>
                        <x-core::handler title="Belum Ada Data" :subtitle="!empty($canCreate)
                            ? 'Silakan tambahkan data dengan cara klik tombol tambah data'
                            : null" />
                    </x-slot:handler>
                </x-dms::layouts.list.list>
            </div>
        </div>
    </div>
</x-dms::layouts.dashboard>
