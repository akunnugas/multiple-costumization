@php
    use Modules\Core\Helpers\Date;
@endphp

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
    // custom
    // [Start] Copy From component/layouts/list.blade.php
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

    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Daftar ' . $title;
    }
    // [END] Copy From component/layouts/list.blade.php

    // set static alert
    if (empty($isFromBimbingan)) { // jika bukan dari halaman bimbingan
        $sudahMasaPengumpulan = !empty($infoPengumpulanProgres['sudah_masuk_masa_pengumpulan_progres']);

        if (!$sudahMasaPengumpulan) {
            $tanggal = Date::formatDateRange($infoPengumpulanProgres['waktu_mulai'], $infoPengumpulanProgres['waktu_selesai'], isoFormatMonth: 'MMMM');
            $staticAlert = [
                'message' => 'Anda tidak dapat mengupload laporan karena bukan dalam waktu pengumpulan progress report.
                    Anda hanya dapat mengupload laporan pada tanggal ' . $tanggal . '.',
                'type' => 'warning',
                'dismissible' => false
            ];
        } else {
            $staticAlert = [
                'title' => 'Upload file Laporan Kegiatan Penelitian di sini',
                'message' => '<ol>
                        <li>File hanya dalam format PDF dengan ukuran maksimum 10 MB.</li>
                        <li>Laporan Antara/Progres berisi laporan perkembangan penelitian hingga penyajian data dan revisi
                            proposal berdasarkan masukan reviewer (Bab 1-3).</li>
                    </ol>',
                'type' => 'helper',
                'isHtml' => true,
                'dismissible' => false
            ];
        }
    }

    if (!empty($sudahMasaPengumpulan)) {
        $canUpdate = true;
    }
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
                    <h2 class="header__title">Progress Report</h2>
                    <span class="header__subtitle">
                        Catatan progress report untuk pelaporan seminar progress.
                    </span>
                </div>
            </div>
        </div>
        <div>
            <x-core::table>
                @php
                    $showAction = empty($isFromBimbingan); // tampilan bimbingan tidak ada aksi
                @endphp
                <x-core::table.data :$header :data="$data->items" :paginateInfo="$data" :sortable="true" :$sort
                                    :$sortDesc :$edit
                                    :can-create="$canCreate && $isReference && $create" :$canDelete :$canUpdate
                                    :$showCheck :$showAction
                                    :$showDetail :$showNumber :$isEditInline :resourceTitle="$title"/>
                @if (!$create && empty($data->items))
                    @php
                        $handlerTitle = "Belum Ada Data $title";
                    @endphp
                    <x-core::handler :$isReference :$canCreate :title="$handlerTitle" />
                @endif
            </x-core::table>
        </div>
        <div class="card__body">
            <div class="form-control">
                <label class="form-control__label">Komentar Umum</label>
                <div class="form-control__text">
                    @if(!empty($dataKomentarUmum))
                        @foreach ($dataKomentarUmum as $komentar)
                            {{ $komentar }}<br>
                        @endforeach
                    @else
                        -- Belum ada komentar umum --
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($canUpdate && empty($isFromBimbingan))
        <x-litabmas::pages.pengajuan-pendanaan.laporan-progres.modal-show-page/>
    @endif

    @pushonce('head')
        <style>
            .form-control .form-control__label {
                color: #364152 !important;
            }

            .form-control__text {
                color: #697586;
            }
        </style>
    @endpushonce
</x-core::layouts.main>
