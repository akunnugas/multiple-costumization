@php
    use Modules\Core\Helpers\Date;
    use Modules\Litabmas\Models\PengajuanPendanaanReviewerKegiatan;
@endphp
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
    'showNumber' => true, // bisa boolean atau string ex: 'No' utk dinamis
    'showDetail' => false,
    'showCheck' => false, // checkbox hanya untuk delete (untuk sekarang)
    'sort' => null,
    'sortDesc' => null,
    'submenu' => [],
    'navTab' => null,
    'subtitle' => null,
    'title' => null,
    'staticAlert' => [],
    'showDeleteChecked' => false, // tidak diperbolehkan menghapus checked, utk hapus per row sesuai kondisi $canDelete
    'isEditInline' => false,
    'isShowPagination' => false,
])

@php
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
    $sudahMasaPenilaian = !empty($infoPengumpulanOutput['sudah_masuk_masa']);

    if (!$isReviewer) {
        $staticAlert = [
            'message' => 'Anda tidak memberikan penilaian karena bukan reviewer dari proposal ini.',
            'type' => 'warning',
            'dismissible' => false
        ];
    } elseif (!$sudahMasaPenilaian) {
        $tanggal = Date::formatDateRange($infoPengumpulanOutput['waktu_mulai'], $infoPengumpulanOutput['waktu_selesai'], isoFormatMonth: 'MMMM');
        $staticAlert = [
            'message' => 'Anda tidak dapat memberikan penilaian karena bukan dalam waktu nya.
                Anda dapat memberikan penilaian pada tanggal ' . $tanggal . '.',
            'type' => 'warning',
            'dismissible' => false
        ];
    } elseif ($isEdit) {
        $staticAlert = [
            'message' => 'Anda hanya dapat memberikan feedback yang sudah memiliki File Laporan.',
            'type' => 'helper',
            'dismissible' => true
        ];
    } else {
        $staticAlert = [
            'message' => 'Harap berikan penilaian output terlebih dahulu. Setelah itu, Anda dapat memberikan penilaian akhir output bersama.',
            'type' => 'helper',
            'false'
        ];
    }

    // output bersama
    $outputBersamaSudahDinilai = $infoOutputBersama['status_penilaian_output_bersama'] === PengajuanPendanaanReviewerKegiatan::STATUS_PENILAIAN_SUDAH_DINILAI;
    $infoStatusOutputBersama = PengajuanPendanaanReviewerKegiatan::STATUS_PENILAIAN[$infoOutputBersama['status_penilaian_output_bersama']];

    // hak akses update
    $canUpdate = ($isReviewer ?? false) && $sudahMasaPenilaian;
    $canUpdateOutputBersama = $canUpdate && ($statusPenilaianOutput !== PengajuanPendanaanReviewerKegiatan::STATUS_PENILAIAN_BELUM_DINILAI);
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
                    <h2 class="header__title">Berikan Penilaian Presentasi Output</h2>
                    <span class="header__subtitle">
                        Silakan review dan berikan penilaian presentasi pada proposal ini.
                    </span>
                </div>
            </div>
            <div class="card__header-right">
                <div class="util_d-flex">
                    @if($isReviewer)
                        @if(!$isEdit)
                            <x-core::button
                                    href="{{ ($canUpdate) ? \Modules\Core\Helpers\Page::buildURL(['edit' => true]) : '#' }}"
                                    :disabled="!$canUpdate" size="sm" leading-icon="pencil-square-solid">
                                Edit Data
                            </x-core::button>
                        @else
                            <x-core::button href="{{ route('litabmas.penilaian-output.show', $resourceId) }}"
                                            size="sm" variant="outline" class="util_mr-8">
                                Batalkan
                            </x-core::button>
                            <x-core::button type="submit" size="sm" form="form-action" :disabled="!$canUpdate">
                                Simpan
                            </x-core::button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        <div>
            <x-core::form method="PUT" action="{{ route('litabmas.penilaian-output.update', $resourceId) }}"
                          id="form-action">
                @if($isReviewer && $isEdit && !empty($data->items))
                    <x-core::table>
                        <div class="table-max">
                            <table>
                                <thead>
                                <tr>
                                    <th class="cell-check cell-center">
                                        No
                                    </th>
                                    @foreach ($header as $i => $item)
                                        @php
                                            if (!empty($item['type']) && $item['type'] === 'hidden') {
                                                continue;
                                            }

                                            $no = $i + 1;
                                            $attributes = Page::buildAttributes($item['attributes'] ?? null);

                                            $label = $item['label'] ?? null;
                                            if (empty($label) && !empty($item['field'])) {
                                                $label = Page::defineLabelByField($item['field'], $urlInfo ?? null);
                                            }

                                            $param = [];
                                            if (empty($isLivewire)) {
                                                $param['data-no'] = $no;
                                            }
                                        @endphp
                                        <th {{ $attributes->merge($param) }}>
                                            {{ $label }}
                                        </th>
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($data->items as $row)
                                    @php
                                        $row['_numberRow'] = !empty($data)
                                            ? ($data->firstItem + $loop->index)
                                            : $loop->iteration;
                                    @endphp
                                    <tr>
                                        @if(!empty($showNumber))
                                            <td class="cell-check cell-center">{{ $row['_numberRow'] ?? null }}</td>
                                        @endif
                                        @foreach ($header as $item)
                                            @if (!empty($item['type']) && $item['type'] === 'hidden')
                                                @continue
                                            @endif
                                            @empty($item['readonly'])
                                                @php
                                                    $activeField = $item['name'] ?? $item['field'];
                                                    if (isset($item['key_field'])) {
                                                        $activeField = $item['key_field'];
                                                    }

                                                    $customName = $item['field']  . '[' . $row['id_jenis_output_penelitian'] . ']';
                                                    $oldValue = old($activeField) ?? null;
                                                    $oldValue = $oldValue[$row['id_jenis_output_penelitian']] ?? null;

                                                    $item += [
                                                        'name' => $customName,
                                                        'value' => $row[$activeField] ?? $oldValue ?? null,
                                                    ];

                                                    // pengecekan khusus feedback & status jadi disabled jika tdk ada file laporan
                                                    if (($item['field'] === 'feedback_output' || $item['field'] === 'status_penilaian_output')
                                                        && empty($row['id_dokumen_output'])
                                                    ) {
                                                        $item['disabled'] = true;
                                                    }

                                                    $attributes = Page::buildAttributes($item);
                                                @endphp
                                                <td @style(['padding-top:14px'])>
                                                    <x-core::controls.form {{ $attributes }} :show-label="false"/>
                                                </td>
                                            @else
                                                <x-core::table.cell :data="$row" :header="$item"/>
                                            @endempty
                                        @endforeach
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-core::table>
                @else
                    @php
                        $canUpdateNonEdit = false; // force, karena edit dri tombol berikan penilaian
                    @endphp
                    <x-core::table>
                        <x-core::table.data :$header :data="$data->items" :paginateInfo="$data" :$sort
                                            :$sortDesc :$edit :$canCreate :$canDelete :canUpdate="$canUpdateNonEdit"
                                            :$showCheck
                                            :$showDetail :$showNumber :$isEditInline :resourceTitle="$title"/>
                        @if (!$create && empty($data->items))
                            @php
                                $handlerTitle = "Belum Ada Data Yang Bisa Dinilai";
                                $handleSubtitle = "Silakan konfirmasikan kepada peneliti untuk mengunggah file output terlebih dahulu.";
                            @endphp
                            <x-core::handler :$isReference :$canCreate :title="$handlerTitle"
                                             :subtitle="$handleSubtitle"/>
                        @endif
                    </x-core::table>
                @endif

                <div class="card__body">
                    @if($isReviewer)
                        @if($isEdit)
                            @php
                                $item = $fieldKomentarUmum;
                                $attributes = \Modules\Core\Helpers\Page::buildAttributes($item);
                            @endphp
                            <div class="grid cols-1 cols-sm-2">
                                <x-core::controls.form {{ $attributes }} />
                            </div>
                        @else
                            <div class="form-control">
                                <label class="form-control__label util_fs-14px">{{ $fieldKomentarUmum['label'] }}</label>
                                <div class="form-control__text">
                                    @if(!empty($fieldKomentarUmum['value']))
                                        {{ $fieldKomentarUmum['value'] }}
                                    @else
                                        -- Anda belum memberikan komentar umum --
                                    @endif
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="form-control">
                            <label class="form-control__label">Komentar Umum</label>
                            <div class="form-control__text">
                                @if(!empty($komentarUmumAllReviewer))
                                    @foreach ($komentarUmumAllReviewer as $komentar)
                                        {{ $komentar }}<br>
                                    @endforeach
                                @else
                                    -- Belum ada komentar umum --
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </x-core::form>
        </div>
    </div>

    <div class="card card_details-default">
        <div class="card__header">
            <div class="card__header-left">
                <div class="card__header-block">
                    <div class="util_d-flex">
                        <span data-tooltip="Penilaian yang diberikan harus berdasarkan kesepakatan bersama antara semua reviewer pada proposal ini"
                              data-placement="right" class="util_mr-8">
                            <span class="icon icon-information-circle"></span>
                        </span>
                        <h2 class="header__title util_d-flex">
                            Berikan Penilaian Akhir Output (Bersama)
                            <x-core::badge :variant="$infoStatusOutputBersama['variant']" type="secondary"
                                           size="sm" class="util_ml-8">
                                {{ $infoStatusOutputBersama['text'] }}
                            </x-core::badge>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="card__header-right">
                @if($isReviewer)
                    <div class="util_d-flex">
                        <x-core::button
                                href="{{ ($canUpdateOutputBersama) ? route('litabmas.penilaian-output.bersama.edit', $resourceId) : '#' }}"
                                :disabled="!$canUpdateOutputBersama" size="sm" leading-icon="pencil-square-solid">
                            Berikan Penilaian
                        </x-core::button>
                    </div>
                @endif
            </div>
        </div>
        <div class="card__body">
            <div class="form-control">
                <label class="form-control__label util_fs-14px">
                    Daftar Nama Reviewer:
                    @foreach($infoOutputBersama['biodata_reviewer'] as $reviewer)
                        <span class="avatar avatar_xs util_ml-12 util_mr-4"
                              data-avatar-name="{{ $reviewer['nama'] }}"></span>
                        {{ $reviewer['nama'] }}
                    @endforeach
                </label>
            </div>

        </div>
    </div>
    @pushonce('head')
        <style>
            .col-12.col-sm-8.col-md-9.col-lg-9 {
                display: flex;
                align-items: flex-start;
                gap: 0.25rem;
            }

            .form-control .form-control__label {
                color: #364152 !important;
            }

            .form-control__text {
                color: #697586;
            }
        </style>
    @endpushonce
</x-core::layouts.main>
