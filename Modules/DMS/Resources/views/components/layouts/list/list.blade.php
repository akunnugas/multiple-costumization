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
    'menu' => [],
    'search' => null,
    'showDetail' => true,
    'sort' => null,
    'sortDesc' => null,
    'submenu' => [],
    'subtitle' => null,
    'title' => null,
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

    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Daftar ' . $title;
    }

    // form
    $action = $method = null;
    if (!empty($edit)) {
        $method = 'PUT';
        $action = Page::detailURL($edit);
    }
@endphp

<x-core::table>
    <x-core::layouts.html.alert style="margin-bottom:1rem" />
    <x-core::form id="form_list" :with-upload="false" :$action :$method>
        <x-core::table.data :$header :data="$data->items" :sortable="true" :$sort :$sortDesc :$edit
                            :can-create="$canCreate && $isReference && $create" :$canDelete :$canUpdate :$showCheck :$showDetail />
    </x-core::form>
    @if (!empty($data->items))
        <x-slot:footer>
            <x-core::table.navigation :$data />
        </x-slot:footer>
    @endif
    @if (!$create && empty($data->items))
        @if(empty($handler))
            <x-core::handler title="Belum Ada Data {{ $title }}"
                            subtitle="Silakan tambahkan data {{ strtolower($title) }} dengan cara klik tombol tambah data" />
        @else
            {!! $handler !!}
        @endif
    @endif
</x-core::table>

@if ($canDelete)
    <x-core::modal.check id="modal_alert" />
    <x-core::modal.delete title="Yakin ingin mengarsipkan file/folder?" id="modal_delete_checked">
        <x-core::modal.delete-body>
            <x-slot:message>
                Pastikan file sudah tidak lagi digunakan. File yang diarsipkan akan dipindah ke folder arsip dan tidak lagi dapat diakses oleh siapapun.
            </x-slot:message>

            <x-slot:button id="btn_delete_checked">
                Arsipkan
            </x-slot:button>
        </x-core::modal.delete-body>
    </x-core::modal.delete>
    @pushOnce('scripts')
        <script type="module">
            List.eventDeleteChecked(false, "{{ route('dms.index') }}");
        </script>
    @endPushOnce
@endif
