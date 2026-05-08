@php
    use Modules\Litabmas\Models\PengajuanPendanaanStatus;
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
    if (empty($permission['post'])) {
        $canCreate = false;
    }
    if (empty($permission['delete'])) {
        $canDelete = false;
    }
    if (empty($permission['put'])) {
        $canUpdate = false;
    }

    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Daftar ' . $title;
    }
    // [END] Copy From component/layouts/list.blade.php

    // set static alert
    if (empty($isFromBimbingan)) { // jika bukan dari halaman bimbingan
        $staticAlert = [
            'title' => 'Upload file Laporan Kegiatan Penelitian di sini',
            'message' => '<ol>
                    <li>Laporan keuangan disusun dengan mengacu kepada SBM & SBK Kemenkeu yang berlaku pada tahun pelaksanaan.</li>
                    <li>Laporan keuangan memuat arus kas dan scan bukti transaksi, Laporan keuangan sementara dilaporkan dalam seminar luaran.</li>
                    <li>Laporan akhir merupakan laporan final berdasarkan hasil review seminar luaran, berisi Laporan Keuangan dan Laporan Akademik final.</li>
                </ol>',
            'type' => 'helper',
            'isHtml' => true,
            'dismissible' => false
        ];
    }

    // cek status penentuan pendanaan
    if ($statusPenentuanPendanaan === PengajuanPendanaanStatus::PENENTUAN_PENDANAAN_LOLOS_PENDANAAN) {
        $canUpdate = true;
    }

    $editUrl = \Modules\Core\Helpers\Page::buildURL(['edit' => true]) . '#biaya';
    $actionUpdateBiaya = route('litabmas.pengajuan-pendanaan.laporan-akhir.update-biaya-terpakai', $resourceId);
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
        <div class="card__body">
            <div class="card">
                <div class="card__header">
                    <div class="card__header-left" id="biaya">
                        <x-core::form method="PUT" action="{{ $actionUpdateBiaya }}"
                                      id="form-action-biaya">
                            <div class="grid custom-grid">
                                @if($isEdit)
                                    @php
                                        $fieldBiayaTerpakai['name'] ??= $fieldBiayaTerpakai['field'];
                                        unset($fieldBiayaTerpakai['field']);

                                        $fieldBiayaTerpakai['value'] = $biayaTerpakai;

                                        $attributes = Page::buildAttributes($fieldBiayaTerpakai);
                                    @endphp
                                    <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                                        <label class="row-data__name">Biaya Terpakai</label>
                                    </div>
                                    <div class="col-12 col-sm-8 col-md-9 col-lg-9 col-value">
                                        <span class="row-data__value">
                                            <span class="row-data__colon">:</span>
                                            <x-core::controls.form {{ $attributes }} />
                                        </span>
                                    </div>
                                @else
                                    <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                                        <label class="row-data__name">Biaya Terpakai</label>
                                    </div>
                                    <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                                        <span class="row-data__value">
                                            <span class="row-data__colon">:</span>
                                            @if(!empty($biayaTerpakai))
                                                {{ $biayaTerpakai }}
                                            @else
                                                -- Belum Ditambahkan --
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </x-core::form>
                    </div>
                    <div class="card__header-right">
                        <div class="util_d-flex">
                            @if(empty($isFromBimbingan))
                                @if(!$isEdit)
                                    <x-core::button href="{{ $editUrl }}" size="sm" :disabled="!$canUpdate">
                                        Edit Data
                                    </x-core::button>
                                @else
                                    <x-core::button
                                        href="{{ route('litabmas.pengajuan-pendanaan.laporan-akhir.index', $resourceId) }}"
                                        size="sm"
                                        variant="outline" class="util_mr-8">
                                        Batalkan
                                    </x-core::button>
                                    <x-core::button type="submit" size="sm" form="form-action-biaya">
                                        Simpan
                                    </x-core::button>
                                @endif
                            @endif
                        </div>
                    </div>
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
                    <x-core::handler :$isReference :$canCreate :title="$handlerTitle"/>
                @endif
            </x-core::table>
        </div>
    </div>

    @if($canUpdate && empty($isFromBimbingan))
        <x-litabmas::pages.pengajuan-pendanaan.laporan-akhir.modal-index-page/>
    @endif

    @pushonce('head')
        <style>
            .form-control .form-control__label {
                color: #364152 !important;
            }

            .form-control__text {
                color: #697586;
            }

            .box-table__content {
                border-top: none;
            }

            .card .card__header .card__header-left {
                flex-direction: column;
            }

            .custom-grid .col-value {
                display: flex;
                gap: 4px;
            }

            .custom-grid .col-value .row-data__value {
                width: 100%;
            }
        </style>
    @endpushonce
</x-core::layouts.main>
