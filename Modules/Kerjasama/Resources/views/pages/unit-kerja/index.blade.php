@props([
    'canCreate' => true,
    'canDelete' => true,
    'canUpdate' => false,
    'create' => false,
    'data' => [],
    'edit' => null,
    'filter' => [],
    'header' => [],
    'isReference' => false,
    'withSync' => false,
    'withExport' => false,
    'syncLabel' => null,
    'withImport' => false,
    'menu' => [],
    'search' => null,
    'showNumber' => null, // bisa boolean atau string ex: 'No' utk dinamis
    'showDetail' => true,
    'sort' => null,
    'sortDesc' => null,
    'submenu' => [],
    'navTab' => null,
    'subtitle' => null,
    'title' => null,
    'createLabel' => null,
    'staticAlert' => [],
    'emptyState' => [],
    'showDeleteChecked' => true, // tidak diperbolehkan menghapus checked, utk hapus per row sesuai kondisi $canDelete
    'isEditInline' => true,
    'customCreateLink' => null
])
@php
    // referensi
    if ($isReference) {
        $canUpdate = true;
        $showDetail = false;
    } else {
        $create = false;
        $edit = null;
    }

    // hak akses
    $permission = request()->permission;
    if ($canCreate && empty($permission['post'])) {
        $canCreate = false;
    }
    if ($canDelete && empty($permission['delete'])) {
        $canDelete = false;
    }
    if ($canUpdate && empty($permission['put'])) {
        $canUpdate = false;
    }
    if ($showDetail && empty($permission['get'])) {
        $showDetail = false;
    }

    // checkbox hanya untuk delete (untuk sekarang)
    $showCheck = $canDelete;
    if (!$showDeleteChecked) {
        $showCheck = false;
    }

    // default title
    $title ??= 'Daftar ' . $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        if ($subtitle === null) {
            $subtitle = 'Daftar ' . $title;
        }
    }

    // form
    $action = $method = null;
    if (!empty($edit)) {
        $method = 'PUT';
        $action = Page::detailURL($edit);
    }

    $isEditInline ??= true;
@endphp

@push('head')
    <style>
    .full-page-loader {
        position: fixed;
        z-index: 9999;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255,255,255,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .full-page-loader.util_d-none {
        display: none !important;
    }
    .loader {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .loader__spinner {
        width: 48px;
        height: 48px;
        border: 6px solid #e0e0e0;
        border-top: 6px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        display: inline-block;
    }
    @keyframes spin {
        0% { transform: rotate(0deg);}
        100% { transform: rotate(360deg);}
    }
</style>
@endpush
<x-core::quantum-3.layouts.main :$menu :$title :$subtitle :withContainer="true">
    @if ($submenu)
        <x-slot:sidebar>
            <x-core::quantum-3.layouts.outer.sidebar :data="$submenu" />
        </x-slot:sidebar>
    @endif

    <x-slot:action>
        @if (!empty($customAction))
            {{ $customAction }}
        @else
            <x-core::quantum-3.layouts.list.action :$isReference :$canCreate :$canDelete :$withSync :$withExport :$withImport :$showDeleteChecked :$syncLabel
                :createLabel="$createLabel" />
        @endif
    </x-slot:action>

    @if (!empty($staticAlert))
        <x-core::quantum-3.alert :title="$staticAlert['title'] ?? null" :type="$staticAlert['type']" :variant="$staticAlert['variant'] ?? 'helper'">
            {!! $staticAlert['message'] !!}
        </x-core::quantum-3.alert>
    @endif

     <x-core::quantum-3.alert :title="''" :variant="'primary'" :dismissable="false">
    Data Sumber diambil dari <b>Modul Kepegawaian (Unit Kerja Non Akademik) dan Modul Akademik
        (Program Studi untuk Unit Akademik)</b> <br> Data yang ditampilkan hanya data program studi dan unit kerja non akademik
</x-core::quantum-3.alert>    <x-core::quantum-3.layouts.html.alert :title="$resourceTitle" />
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <x-core::quantum-3.table :$navTab>
                <x-slot:header>
                    <x-core::quantum-3.layouts.list.header :$title :$filter :$search />
                </x-slot:header>
                <x-core::quantum-3.form id="form_list" :with-upload="false" :$action :$method>
                    <x-core::quantum-3.table.data :$header :data="$data->items" :paginateInfo="$data" :sortable="true" :$sort
                        :$sortDesc :$edit :can-create="$canCreate && $isReference && $create" :$canDelete :$canUpdate :$showCheck :$showDetail
                        :$showNumber :$isEditInline />
                </x-core::quantum-3.form>
                    @if (!empty($data->items))
                    <x-slot:footer>
                        <x-core::quantum-3.table.navigation :$data />
                    </x-slot:footer>
                @endif
                @if (!$create && empty($data->items))
                    @php
                        $title = !empty($emptyState) ? $emptyState['title'] : "Belum Ada Data $title";
                        $subtitle = !empty($emptyState)
                            ? $emptyState['subtitle']
                            : 'Silakan tambah data ' .
                                ' dengan cara klik tombol tambah data';
                    @endphp
                    <x-core::quantum-3.handler :$isReference :$canCreate :$title :$subtitle :$createLabel />
                @endif
            </x-core::quantum-3.table>
        </div>
    </div>
    @if ($canDelete)
        @pushOnce('end')
        {{-- modal delete checked --}}
        <x-core::quantum-3.modal.checked id="modal_checked" />
        <x-core::quantum-3.modal.delete id="modal_delete_checked" />
        {{-- modal delete row --}}
        <x-core::quantum-3.modal.delete-row id="modal_delete_row" />
            
        @endPushOnce
    
        @pushOnce('scripts')
            <script type="module">
                List.eventDeleteChecked(false, "{{ Page::indexURL() }}");
            </script>
        @endPushOnce
    @endif

      @if ($withSync)
        <x-core::quantum-3.modal.sync id="modal_sync"/>
        @pushOnce('scripts')
            <div class="full-page-loader util_d-none">
                <div class="loader">
                    <span class="loader__spinner"></span>
                </div>
            </div>
            <script type="module">
                List.eventSync("{{ Page::indexURL() }}", "{{ route('core.redirectto') }}", "{!! csrf_token() !!}");
            </script>
        @endPushOnce
    @endif
</x-core::quantum-3.layouts.main>