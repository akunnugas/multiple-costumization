@props([
    'data' => [],
    'header' => [],
    'menu' => [],
    'submenu' => [],
    'subtitle' => null,
    'title' => null,
    'action' => null,
])
@php
    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }
@endphp

<x-core::layouts.main :$menu :$title :$subtitle :$action>
    <x-slot:sidebar>
        <x-core::layouts.outer.sidebar :data="$submenu"/>
    </x-slot:sidebar>

    <x-core::layouts.html.alert/>

    <div class="card card_details-primary">
        <div class="grid">
            <x-litabmas::layouts.detail.line :data="$infoHeader[0]['items']"/>
        </div>
    </div>

    <x-litabmas::layouts.detail.card title="Daftar Reviewer" customClassBody="util_d-flex util_flex-column util_gap-1">
        <div class="box-table__content" id="daftar-reviewer">
            <x-core::table>
                @php
                    $showCheck = $showDetail = $canCreate = false;
                    $create = $edit = $isReference = $isEditInline = false;
                    $showNumber = true;
                    $canDelete = $canUpdate = false;
                    $showAction = false
                @endphp
                <x-core::table.data :header="$headerReviewer" :data="$daftarReviewer" :paginateInfo="$data"
                                    :$edit :sortable="false" :sort="null" :sortDesc="null" :$showAction
                                    :can-create="$canCreate && $isReference && $create" :$canDelete :$canUpdate
                                    :$showCheck :$showDetail :$showNumber :$isEditInline :resourceTitle="$title"/>
            </x-core::table>
        </div>
    </x-litabmas::layouts.detail.card>

    @pushonce('head')
        <style>
            .box-table__content#daftar-reviewer {
                border-top: none;
            }
            .box-table__content#daftar-reviewer .box-table__content {
                border-top: none;
                padding: 0;
            }
        </style>
    @endpushonce
</x-core::layouts.main>
