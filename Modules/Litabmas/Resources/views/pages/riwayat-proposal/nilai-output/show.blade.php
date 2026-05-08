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

    <x-litabmas::layouts.detail.card customClassBody="util_d-flex util_flex-column util_gap-1" title="Informasi Reviewer">
        <div class="col-12">
            <div class="util_d-flex">
                @foreach($dataOutputBersama['biodata_reviewer'] as $reviewerKe => $reviewer)
                    <span class="util_mr-12 nama-reviewer">
                        Reviewer {{ $reviewerKe }}
                        &nbsp;
                        ({{ $reviewer['nama'] }})
                    </span>
                @endforeach
            </div>
        </div>
    </x-litabmas::layouts.detail.card>

    <x-litabmas::layouts.detail.card customClassBody="util_d-flex util_flex-column util_gap-1"
                                     title="Hasil Penilaian Presentasi Output">
        <div class="col-12">
            <div class="grid cols-1">
                @if(!empty($dataPaginateFeedbackReviewer->items))
                    <x-core::table>
                        <x-core::table.data :header="$headerFeedbackReviewer" :data="$dataPaginateFeedbackReviewer->items"
                                            :showNumber="true"
                                            :paginateInfo="$dataPaginateFeedbackReviewer" :resourceTitle="$title"/>
                    </x-core::table>
                @else
                    <div class="form-control">
                        <div class="form-control__text">
                            -- Belum ada penilaian presentasi output --
                        </div>
                    </div>
                @endif
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
    </x-litabmas::layouts.detail.card>

    <x-litabmas::layouts.detail.card customClassBody="util_d-flex util_flex-column util_gap-1"
                                     title="Hasil Penilaian Akhir Output (Bersama)">
        <div class="col-12">
            <div class="grid cols-1">
                @if(!$dataPenilaianOutputBersama->isEmpty())
                    <x-core::table>
                        <x-core::table.data :header="$headerPenilaianOutputBersamaFields" :data="$dataPenilaianOutputBersama"
                                            :showNumber="true"
                                            :paginateInfo="$dataPaginateFeedbackReviewer" :resourceTitle="$title"/>
                    </x-core::table>
                @else
                    <div class="form-control">
                        <div class="form-control__text">
                            -- Belum ada penilaian akhir output bersama --
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </x-litabmas::layouts.detail.card>

    <x-litabmas::layouts.detail.card customClassBody="util_d-flex util_flex-column util_gap-1"
                                     title="Kesimpulan">
        <div class="col-12" id="kesimpulan">
            <div class="grid cols-1">
                <div class="form-control">
                    <div class="form-control__text">
                        @if(!empty($fieldKesimpulanOutputBersama['value']))
                            {{ $fieldKesimpulanOutputBersama['value'] }}
                        @else
                            -- Belum ada kesimpulan --
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-litabmas::layouts.detail.card>

    @pushonce('head')
        <style>
            .box-table__content {
                border-top: none;
                padding: 0;
            }
            .form-control .form-control__label {
                color: #364152 !important;
            }
            .form-control__text, .nama-reviewer {
                color: #697586;
            }
        </style>
    @endpushonce
</x-core::layouts.main>
