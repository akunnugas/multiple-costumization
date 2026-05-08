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

    // handle $data
    $primaryData ??= $data['primary-section']['items'];
    unset($data['primary-section']);
    $penilaianReviewer = $data['penilaian-reviewer'];
    unset($data['penilaian-reviewer']);

    // $resourceId dari Page::buildViewData()
    if (!empty($resourceId)) {
        $editUrl = route('litabmas.pengajuan-pendanaan.edit', $resourceId);
        $data['informasi-umum']['edit_url'] = $editUrl;
    }

    // jika url previous mengandung 'pendanaan-kegiatan'
    $urlPrevious = url()->previous();
    if (!Str::contains($urlPrevious, 'pendanaan-kegiatan')) {
        $urlPrevious = Page::backURL();
    }
@endphp

@pushonce('head')
    @vite('resources/scss/custom-utils.scss')
@endpushonce

<x-core::layouts.main :$menu :$title :$subtitle :$action>
    <x-slot:sidebar>
        <x-core::layouts.outer.sidebar :data="$submenu" :backUrl="$urlPrevious"/>
    </x-slot:sidebar>

    <x-core::layouts.html.alert/>

    <div class="card card_details-primary">
        <div class="grid">
            <x-litabmas::layouts.detail.line :data="$primaryData"/>
        </div>
    </div>

    {{-- Pernyataan Penelitian --}}
    @php
        $section = $data['informasi-umum'];
        $attributes = Page::buildAttributes(
            Arr::only($section, ['title', 'subtitle', 'icon', 'page_conf']) + ['data' => $section['items']]
        );
    @endphp
    <x-litabmas::layouts.detail.card {{ $attributes }} />

    {{-- Penilaian Reviewer --}}
    <x-litabmas::layouts.detail.cards :data="[$penilaianReviewer]"/>

    {{-- Data Peneliti --}}
    <x-litabmas::layouts.detail.card title="Data Peneliti">
        @php
            $noDosen = 0;
        @endphp
        @foreach($dataPenelitiDosen as $item)
            @php
                $isKetua = $item->apakah_ketua;
                if (!$isKetua) {
                    $noDosen++;
                }
                $rowTitle = $isKetua ? 'Data Ketua' : "Data Anggota Dosen $noDosen";
            @endphp
            <div class="col-12 col-sm-8 col-md-9 col-lg-12">
                <h3 class="row-data__title">{{ $rowTitle }}</h3>
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                <label class="row-data__name">Nama Lengkap</label>
            </div>
            <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                <span class="row-data__value">
                    <span class="row-data__colon">:</span>
                    {!! $item->nama_user !!}
                </span>
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                <label class="row-data__name">Asal Institusi</label>
            </div>
            <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                <span class="row-data__value">
                    <span class="row-data__colon">:</span>
                    {{ $item->nama_pt }}
                </span>
            </div>
        @endforeach

        @php
            $noMahasiswa = 1;
        @endphp
        @foreach($dataPenelitiMahasiswa as $item)
            @php
                $rowTitle = 'Data Anggota Mahasiswa ' . $noMahasiswa++;
            @endphp
            <div class="col-12 col-sm-8 col-md-9 col-lg-12">
                <h3 class="row-data__title">{{ $rowTitle }}</h3>
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                <label class="row-data__name">Nama Lengkap</label>
            </div>
            <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                <span class="row-data__value">
                    <span class="row-data__colon">:</span>
                    {!! $item->nama_user !!}
                </span>
            </div>
        @endforeach
    </x-litabmas::layouts.detail.card>

    @pushonce('head')
        <style>
            .col-12.col-sm-8.col-md-9.col-lg-9 {
                display: flex;
                align-items: flex-start;
                gap: 0.25rem;
            }

            .card .row-data__value {
                width: unset;
            }

            .row-data__title {
                font-size: 0.75rem;
                font-weight: 600;
                line-height: 1.125rem;
                display: inline-flex;
                padding-right: 0.75rem;
            }

            @media (max-width: 768px) {
                .card .row-data__colon {
                    display: none;
                }

                .card .card__header .card__header-left {
                    flex-direction: column;
                    align-items: flex-start;
                }
            }

            @media (min-width: 768px) {
                .card .row-data__colon {
                    display: inline-flex !important;
                }
            }
        </style>
    @endpushonce
</x-core::layouts.main>
