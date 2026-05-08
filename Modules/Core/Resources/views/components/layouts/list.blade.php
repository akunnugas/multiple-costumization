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
    'syncLabel' => null,
    'syncMessage' => null,
    'syncTitle' => null,
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
    'customCreateUrl' => null,
    'staticAlert' => [],
    'emptyState' => [],
    'showDeleteChecked' => true, // tidak diperbolehkan menghapus checked, utk hapus per row sesuai kondisi $canDelete
    'isEditInline' => true,
    'withCustomAction' => true,
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
    $title ??= $resourceTitle;
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

    // FIX: Remove Subtitle
    $subtitle = null;

    $isEditInline ??= true;
@endphp
<x-core::layouts.main :$menu :$title :$subtitle>
    @if ($submenu)
        <x-slot:sidebar>
            <x-core::layouts.outer.sidebar :data="$submenu" />
        </x-slot:sidebar>
    @endif
    <x-slot:action>
        @if (!empty($customAction) && $withCustomAction)
            {{ $customAction }}
        @elseif (!empty($customAction) && !$withCustomAction)
            {{ $customAction }}
            <x-core::layouts.list.action :$isReference :$canCreate :$canDelete :$withSync :$showDeleteChecked
                :syncLabel="$syncLabel" :createLabel="$createLabel" :customCreateUrl="$customCreateUrl" />
        @else
            <x-core::layouts.list.action :$isReference :$canCreate :$canDelete :$withSync :$showDeleteChecked
                :syncLabel="$syncLabel" :createLabel="$createLabel" :customCreateUrl="$customCreateUrl" />
        @endif
    </x-slot:action>

    @if (!empty($staticAlert))
        <x-core::alert :title="$staticAlert['title'] ?? null" :variant="$staticAlert['variant'] ?? 'helper'">
            {{ $staticAlert['message'] }}
        </x-core::alert>
    @endif

    <div class="card card_table">
        <div class="card__body">
            @if ($slot->isEmpty())
                <x-core::table :$navTab>
                    <x-slot:header>
                        <x-core::layouts.list.header :$title :$filter :$search />
                    </x-slot:header>
                    <x-core::layouts.html.alert style="margin-bottom:1rem" />
                    @if (!empty($tableHeader))
                        {{ $tableHeader }}
                    @endif
                    <x-core::form id="form_list" :with-upload="false" :$action :$method>
                        <x-core::table.data :$header :data="$data->items" :paginateInfo="$data" :sortable="true" :$sort
                            :$sortDesc :$edit :can-create="$canCreate && $isReference && $create" :$canDelete :$canUpdate :$showCheck :$showDetail
                            :$showNumber :$isEditInline />
                    </x-core::form>
                    @if (!empty($data->items))
                        <x-slot:footer>
                            <x-core::table.navigation :$data />
                        </x-slot:footer>
                    @endif
                    @if (!$create && empty($data->items))
                        @php
                            $emptyTitle = !empty($emptyState) ? $emptyState['title'] : "Belum Ada Data $title";
                            $emptySubtitle = !empty($emptyState)
                                ? $emptyState['subtitle']
                                : 'Silakan tambahkan data ' .
                                    strtolower($title) .
                                    ' dengan cara klik tombol tambah data';
                        @endphp
                        <x-core::handler :$isReference :$canCreate :title="$emptyTitle" :subtitle="$emptySubtitle" :$createLabel :customCreateUrl="$customCreateUrl" />
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
                List.eventDeleteChecked(false, "{{ Page::indexURL() }}");
            </script>
        @endPushOnce
    @endif
    @if ($navTab)
        @pushonce('head')
            <style>
                .box-table__header.header_tab {
                    padding: 0;
                }

                .nav-tab {
                    border-radius: 12px 12px 0 0;
                    border-bottom: 1px solid #e3e8ef;
                }

                .nav-tab .nav-tab__wrapper {
                    padding: 0.25rem 1rem 0;
                }

                .nav-tab .nav-tab__item {
                    padding: 0.75rem 1rem;
                }

                .tab-pane {
                    padding: 0;
                }
            </style>
        @endpushonce
    @endif
    @if ($withSync)
        @pushonce('head')
            <style>
                .util_d-none {
                    display: none !important;
                }

                .full-page-loader {
                    display: flex;
                    position: fixed;
                    left: 0;
                    top: 0;
                    justify-content: center;
                    width: 100%;
                    height: 100%;
                    background: #ffffff90;
                    z-index: 9999;
                }
            </style>
        @endpushonce
        <x-core::modal.sync id="modal_sync" :$syncMessage :$syncTitle />
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

    @if (!empty($outer))
        {{ $outer }}
    @endif
</x-core::layouts.main>
