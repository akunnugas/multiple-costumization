@php use Modules\Core\Helpers\Date; @endphp
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
    'menu' => [],
    'search' => null,
    'showNumber' => null, // bisa boolean atau string ex: 'No' utk dinamis
    'showDetail' => true,
    'showCheck' => false, // checkbox hanya untuk delete (untuk sekarang)
    'sort' => null,
    'sortDesc' => null,
    'submenu' => [],
    'navTab' => null,
    'subtitle' => null,
    'title' => null,
    'staticAlert' => [],
    'showDeleteChecked' => true, // tidak diperbolehkan menghapus checked, utk hapus per row sesuai kondisi $canDelete
    'isEditInline' => true,
    'isShowPagination' => true,
])

@php
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

    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Daftar ' . $title;
    }
    // [END] Copy From component/layouts/list.blade.php

    // set ulang navTab
    $navTab = \Modules\Core\Helpers\Page::defineMenu('litabmas', $navTab['items'], $navTab['activePath'] ?? null, checkPermission: false);

    // set static alert
    $tanggal = Date::formatDate($infoPengumpulanOutcome['waktu_selesai']);
    $staticAlert = [
        'message' => 'Outcome pada penelitian Anda adalah : '. $outcomeString . '.
            Anda wajib memasukkan sampai pada tanggal ('. $tanggal .'), jika lebih dari tanggal tersebut Anda akan diblokir pada pengajuan pendanaan periode selanjutnya',
        'type' => 'helper',
    ];
@endphp

{{--copy from templates/index--}}
<x-core::layouts.main :$menu :$title :$subtitle>
    @if ($submenu)
        <x-slot:sidebar>
            <x-core::layouts.outer.sidebar :data="$submenu"/>
        </x-slot:sidebar>
    @endif

    <div class="card card_details-primary">
        <div class="grid">
            <x-litabmas::layouts.detail.line :data="$infoHeader[0]['items']"/>
        </div>
    </div>

    @if(!empty($staticAlert))
        <x-core::layouts.html.alert :data="$staticAlert"/>
    @endif

    <x-core::layouts.html.alert/>

    <div class="card card_details-default">
        <div class="card__header">
            <div class="card__header-left">
                <div class="card__header-block">
                    <h2 class="header__title">Publikasi Penelitian</h2>
                    <span class="header__subtitle">
                    Publikasikan penelitian Anda dan berikan dampak positif melalui hasil penelitian Anda.
                </span>
                </div>
            </div>
            <div class="card__header-right">
                <div class="util_d-flex">
                    @if($canCreate)
                        <x-core::button href="{{ route('litabmas.pengajuan-pendanaan.publikasi-penelitian.create', $resourceId) }}"
                                        size="sm">
                            Tambah Publikasi
                        </x-core::button>
                    @else
                        <x-core::button size="sm" disabled>
                            Tambah Publikasi
                        </x-core::button>
                    @endif
                </div>
            </div>
        </div>
        <div>
            <x-core::table :navTab="$navTab">
                <x-core::table.data :$header :data="$data->items" :paginateInfo="$data" :sortable="true" :$sort
                                    :$sortDesc :$edit :$showCheck
                                    :can-create="$canCreate && $isReference && $create" :$canDelete :$canUpdate
                                    :$showDetail :$showNumber :$isEditInline :resourceTitle="$title"/>
                @if (!$create && empty($data->items))
                    @php
                        $handlerTitle = "Belum Ada Data $title";
                    @endphp
                    <x-core::handler :$isReference :$canCreate :title="$handlerTitle"/>
                @endif
            </x-core::table>
        </div>
    </div>

    @pushonce('head')
        <style>
            .box-table__header.header_tab {
                padding-bottom: unset;
            }
            .tab-pane.active {
                padding-top: unset;
            }
            .nav-tab .nav-tab__wrapper {
                border-bottom: unset;
            }
            .box-table__footer {
                padding: unset;
            }
        </style>
    @endpushonce
</x-core::layouts.main>
