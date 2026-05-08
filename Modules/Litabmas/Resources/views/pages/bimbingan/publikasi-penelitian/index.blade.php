@props([
    'canCreate' => false,
    'canDelete' => false,
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
    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Daftar ' . $title;
    }
    // [END] Copy From component/layouts/list.blade.php

    // set ulang navTab
    $navTab = \Modules\Core\Helpers\Page::defineMenu('litabmas', $navTab['items'], $navTab['activePath'] ?? null, checkPermission: false);
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

    <x-core::layouts.html.alert/>

    <div class="card card_details-default">
        <x-core::table :navTab="$navTab">
            <x-core::table.data :$header :data="$data->items" :paginateInfo="$data" :sortable="true" :$sort
                                :$sortDesc :$edit :$showCheck :showAction="false"
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
