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
    // custom
    $isShowPagination = true;
    $isReference = false;
    $sortable = true;

    // [Start] Copy From component/layouts/list.blade.php
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
        $subtitle = 'Daftar ' . $title;
    }

    // form
    $action = $method = null;
    if (!empty($edit)) {
        $method = 'PUT';
        $action = Page::detailURL($edit);
    }
    // [END] Copy From component/layouts/list.blade.php

    // set static alert
    $sudahMasukPelaksanaan = !empty($infoPelaksanaanPenelitian['sudah_masuk_masa_pelaksanaan_penelitian']);
    $tanggalPelaksanaan = \Modules\Core\Helpers\Date::formatDateRange($infoPelaksanaanPenelitian['waktu_mulai'], $infoPelaksanaanPenelitian['waktu_selesai'], isoFormatMonth: 'MMMM');

    if (!$sudahMasukPelaksanaan) {
        $staticAlert = [
            'message' => 'Management aktivitas penelitian hanya dapat dilakukan pada tanggal ' . $tanggalPelaksanaan . '.',
            'type' => 'warning',
            'dismissible' => false
        ];
        $canCreate = false;
        $canUpdate = false;
        $canDelete = false;
    }
@endphp

{{--copy from templates/index--}}
<x-core::layouts.main :$menu :$title :$subtitle>
    @if ($submenu)
        <x-slot:sidebar>
            <x-core::layouts.outer.sidebar :data="$submenu" />
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

    <x-core::layouts.html.alert />

    <div class="card card_details-default">
        <div class="card__header">
            <div class="card__header-left">
                <div class="card__header-block">
                    <h2 class="header__title">Aktivitas Peneliti</h2>
                    <span class="header__subtitle">
                        Catatan tertulis yang untuk mendokumentasikan setiap tahap dan aktivitas dalam proses penelitian maupun pengabdian.
                    </span>
                </div>
            </div>
            <div class="card__header-right">
                <div class="util_d-flex">
                    @if($canCreate)
                        <x-core::button href="{{ route('litabmas.pengajuan-pendanaan.aktivitas-penelitian.create', $resourceId) }}" size="sm">
                            Tambahkan Aktivitas
                        </x-core::button>
                    @else
                        <x-core::button href="#" disabled size="sm">
                            Tambahkan Aktivitas
                        </x-core::button>
                    @endif
                </div>
            </div>
        </div>
        <div>
            <x-core::table>
                <x-core::table.data :$header :data="$data->items" :paginateInfo="$data" :sortable="true" :$sort :$sortDesc :$edit
                                    :can-create="$canCreate && $isReference && $create" :$canDelete :$canUpdate :$showCheck
                                    :$showDetail :$showNumber :$isEditInline :resourceTitle="$title" />
                @if (!$create && empty($data->items))
                    @php
                        $handlerTitle = "Belum Ada Data $title";
                        $handleSubtitle = "Silakan tambahkan data ".strtolower($title)." dengan cara klik tombol tambah data";
                    @endphp
                    <x-core::handler :$isReference :$canCreate :title="$handlerTitle" :subtitle="$handleSubtitle" />
                @endif
            </x-core::table>
        </div>
    </div>
</x-core::layouts.main>
