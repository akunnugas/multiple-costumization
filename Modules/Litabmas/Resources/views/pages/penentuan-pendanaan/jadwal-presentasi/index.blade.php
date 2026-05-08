@php use Modules\Litabmas\Models\PengajuanPendanaanStatus; @endphp
@props([
    'canCreate' => true,
    'canDelete' => true,
    'canUpdate' => true,
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
    'showDetail' => false,
    'showCheck' => false,
    'sort' => null,
    'sortDesc' => null,
    'submenu' => [],
    'navTab' => null,
    'subtitle' => null,
    'title' => null,
    'staticAlert' => [],
    'showDeleteChecked' => true, // tidak diperbolehkan menghapus checked, utk hapus per row sesuai kondisi $canDelete
    'isEditInline' => false,
    'isShowPagination' => true,
])

@php
    // custom
    $isShowPagination = true;
    $isReference = false;
    $sortable = true;

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

    // form
    $action = $method = null;
    if (!empty($edit)) {
        $method = 'PUT';
        $action = Page::detailURL($edit);
    }
    // [END] Copy From component/layouts/list.blade.php

    // cek jika sudah memiliki semua agenda presentasi
    $sudahMembuatSemuaAgendaPresentasi = false;
    $sudahMembuatPresentasiProgres = false;
    $sudahMembuatPresentasiOutput = false;
    if (!empty($data->items)) {
        foreach ($data->items as $item) {
            if ($item['tipe_presentasi'] === \Modules\Litabmas\Models\PengajuanPendanaanJadwalPresentasi::TIPE_PRESENTASI_OUTPUT) {
                $sudahMembuatPresentasiOutput = true;
            } elseif ($item['tipe_presentasi'] === \Modules\Litabmas\Models\PengajuanPendanaanJadwalPresentasi::TIPE_PRESENTASI_PROGRESS_REPORT) {
                $sudahMembuatPresentasiProgres = true;
            }
        }
    }
    // bandingkan apakah memiliki jadwal presentasi progress dan output
    $sudahMembuatSemuaAgendaPresentasi = $sudahMembuatPresentasiProgres && $sudahMembuatPresentasiOutput;

    // set static alert
    if (!$sumberPendanaanMemilikiPresentasiProgress && !$sumberPendanaanMemilikiPresentasiOutput) {
        // tidak memiliki agenda presentasi progress maupun output
        $staticAlert = [
            'message' => 'Sumber pendanaan proposal ini tidak menggunakan agenda presentasi progress report maupun presentasi output.',
            'type' => 'warning',
            'dismissible' => false
        ];
    } elseif (!$sumberPendanaanMemilikiPresentasiProgress && $sumberPendanaanMemilikiPresentasiOutput) {
        // hanya memiliki agenda presentasi output
        if ($sudahMembuatPresentasiOutput) {
            $staticAlert = [
                'message' => 'Sumber Pendanaan proposal ini hanya menggunakan presentasi output. Anda telah membuat jadwal presentasi output.',
                'type' => 'helper',
                'dismissible' => false
            ];
        } else {
            $staticAlert = [
                'message' => 'Sumber Pendanaan proposal ini tidak menggunakan agenda presentasi progress report,
                    hanya menggunakan presentasi output. Silahkan masukkan jadwal presentasi.',
                'type' => 'helper',
                'dismissible' => false
            ];
        }
    } elseif ($sumberPendanaanMemilikiPresentasiProgress && !$sumberPendanaanMemilikiPresentasiOutput) {
        // hanya memiliki agenda presentasi progress
        if ($sudahMembuatPresentasiProgres) {
            $staticAlert = [
                'message' => 'Sumber Pendanaan proposal ini hanya menggunakan presentasi progress report. Anda telah membuat jadwal presentasi progress.',
                'type' => 'helper',
                'dismissible' => false
            ];
        } else {
            $staticAlert = [
                'message' => 'Sumber Pendanaan proposal ini tidak menggunakan agenda presentasi output,
                    hanya menggunakan presentasi progress report. Silahkan masukkan jadwal presentasi.',
                'type' => 'helper',
                'dismissible' => false
            ];
        }
    } elseif ($sumberPendanaanMemilikiPresentasiProgress && $sumberPendanaanMemilikiPresentasiOutput && $sudahMembuatSemuaAgendaPresentasi) {
        // memiliki agenda presentasi progress dan output
        $staticAlert = [
            'message' => 'Anda telah membuat semua agenda presentasi progress report dan output.',
            'type' => 'helper',
            'dismissible' => false
        ];
    } else {
        $staticAlert = [
            'message' => 'Hanya proposal yang telah mendapatkan pendanaan dan telah ditetapkan reviewer-nya yang dapat dijadwalkan untuk presentasi.',
            'type' => 'helper',
            'dismissible' => false
        ];
    }

    // akses create, update, delete
    if ($statusPenentuanPendanaan !== PengajuanPendanaanStatus::PENENTUAN_PENDANAAN_LOLOS_PENDANAAN || // belum lolos pendanaan
        !$hasReviewerKegiatan || // belum ditetapkan reviewer
        (!$sumberPendanaanMemilikiPresentasiProgress && !$sumberPendanaanMemilikiPresentasiOutput) // tidak memiliki agenda presentasi progress maupun output
    ) {
        $canCreate = false;
        $canUpdate = false;
        $canDelete = false;
    }

    // validasi create aja
    if (empty($sumberPendanaanMemilikiPresentasiProgress) && $sumberPendanaanMemilikiPresentasiOutput && $sudahMembuatPresentasiOutput) {
        $canCreate = false;
    } elseif (empty($sumberPendanaanMemilikiPresentasiOutput) && $sumberPendanaanMemilikiPresentasiProgress && $sudahMembuatPresentasiProgres) {
        $canCreate = false;
    } elseif ($sumberPendanaanMemilikiPresentasiProgress && $sumberPendanaanMemilikiPresentasiOutput && $sudahMembuatSemuaAgendaPresentasi) {
        $canCreate = false;
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
            <x-litabmas::layouts.detail.line :data="$primaryData"/>
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
                    <h2 class="header__title">Jadwal Presentasi</h2>
                    <span class="header__subtitle">
                        Silahkan tentukan jadwal presentasi progress dan ouput pada proposal ini.
                    </span>
                </div>
            </div>
            <div class="card__header-right">
                <div class="util_d-flex">
                    @if($canCreate)
                        <x-core::button href="{{ route('litabmas.penentuan-pendanaan.jadwal-presentasi.create', $resourceId) }}" size="sm">
                            Buat Jadwal Presentasi
                        </x-core::button>
                    @else
                        <x-core::button href="#" disabled size="sm">
                            Buat Jadwal Presentasi
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
                        $handleSubtitle = "Silahkan tentukan jadwal presentasi progress & output untuk proposal ini.";
                    @endphp
                    <x-core::handler :$isReference :$canCreate :title="$handlerTitle" :subtitle="$handleSubtitle" />
                @endif
            </x-core::table>
        </div>
    </div>
</x-core::layouts.main>
