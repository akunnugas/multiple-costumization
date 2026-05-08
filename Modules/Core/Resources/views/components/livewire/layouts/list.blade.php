@props([
    'alert' => null,
    'canCreate' => true,
    'canDelete' => true,
    'canUpdate' => false,
    'create' => false,
    'data' => [],
    'edit' => null,
    'header' => [],
    'isReference' => false,
    'menu' => [],
    'order' => [],
    'search' => null,
    'selectFilter' => [],
    'showDetail' => true,
    'subtitle' => null,
    'title' => null,
    'updateURL' => null,
])
@php
    // berbeda dengan non livewire
    $filter = $selectFilter;
    $sort = $order['no'] ?? 0;
    $sortDesc = $order['desc'] ?? 0;

    // referensi
    if ($isReference) {
        $canUpdate = true;
        $showDetail = false;
    } else {
        $create = false;
        $edit = null;
    }

    // hak akses
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
        $action = $updateURL;
    }
@endphp
<x-core::layouts.main.container :$menu :$title :$subtitle>
    <x-slot:action>
        <x-core::layouts.list.action :$isReference :$canCreate :$canDelete />
    </x-slot:action>
    <div class="card card_table">
        <div class="card__body">
            @if ($slot->isEmpty())
                <x-core::table>
                    <x-slot:header>
                        <x-core::layouts.list.header :$title :$filter :$search />
                    </x-slot:header>
                    @if (!empty($data->items))
                        @if (!empty($alert))
                            <x-core::layouts.html.alert :data="$alert" style="margin-bottom:1rem" />
                        @endif
                        <x-core::form id="form_list" :with-upload="false" :$action :$method wire:submit="save">
                            <x-core::table.data :$header :data="$data->items" :sortable="true" :$sort :$sortDesc :$edit
                                :can-create="$canCreate && $isReference && $create" :$canDelete :$canUpdate :$showCheck :$showDetail />
                        </x-core::form>
                        <x-slot:footer>
                            <x-core::table.navigation :$data />
                        </x-slot:footer>
                    @else
                        <x-core::handler title="Belum Ada Data {{ $title }}"
                            subtitle="Silakan tambahkan data {{ strtolower($title) }} dengan cara klik tombol tambah data" />
                    @endif
                </x-core::table>
            @else
                {{ $slot }}
            @endif
        </div>
    </div>
    @if ($canDelete)
        <x-core::modal.check id="modal_alert" />
        <x-core::modal.delete id="modal_delete_checked">
            <x-core::modal.delete-body>
                yang dipilih
                <x-slot:button id="btn_delete_checked">
                    Hapus
                </x-slot:button>
            </x-core::modal.delete-body>
        </x-core::modal.delete>
        @pushOnce('scripts')
            <script type="module">
                List.eventDeleteChecked(true);
            </script>
        @endPushOnce
    @endif
</x-core::layouts.main.container>
