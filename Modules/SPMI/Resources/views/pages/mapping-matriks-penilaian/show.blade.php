@props([
    'data' => [],
    'header' => [],
    'menu' => [],
    'submenu' => [],
    'subtitle' => null,
    'title' => null,
    'isFullwidth' => false,
    'disableEdit' => false,

    // Slot
    'action' => null,
    'outer' => null,

    // permission
    'canUpdate' => true,
])
@php
    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }

    $permission = request()->permission;
    if ($canUpdate && empty($permission['put'])) {
        $canUpdate = false;
    }
@endphp
<x-core::layouts.outer header-class="header_position-static" :$menu :$title>
    @pushOnce('head')
        @vite('resources/scss/layouts/_detail.scss')
        @vite('resources/scss/custom-utils.scss')
        <style>
            .content__title-2 {
                font-size: 0.875rem;
                font-weight: 600;
                color: #0F6AF5;
            }

            .hr {
                margin: 0.5rem 0 1rem 0;
                border: none;
                border-top: 1px solid #0F6AF5;
            }

            .content.content-full {
                width: 100%;
                padding-left: 3rem;
                padding-right: 3rem;
            }

            .sidebar_within {
                width: 340px !important;
                max-width: 340px !important;
            }
        </style>
    @endPushOnce

    @php
        $isProdi = !in_array($rawData['detail']['level'], [
            \Modules\Core\Models\UnitKerja::UNIVERSITY,
            \Modules\Core\Models\UnitKerja::FACULTY,
            \Modules\Core\Models\UnitKerja::MAJOR,
        ]);
    @endphp

    <div class="container">
        <div class="card">
            <div class="card__header">
                <ul class="breadcrumb">
                    <li class="breadcrumb__item">
                        <span class="icon icon-home-mini"></span>
                    </li>
                    @foreach ($breadcrumb['items'] as $i => $item)
                        @if ($item['showLink'])
                            <li class="breadcrumb__item">
                                <a href="{{ url($item['path']) }}">
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @elseif (!empty($item['label']))
                            <li class="breadcrumb__item active">
                                @if ($i === count($breadcrumb['items']) - 1)
                                    Detail {{ $item['label'] }}
                                @else
                                    {{ $item['label'] }}
                                @endif
                            </li>
                        @endif
                    @endforeach
                    @if ($breadcrumb['showTitle'])
                        <li class="breadcrumb__item active">{{ $subtitle }}</li>
                    @endif
                </ul>

                <div class="button-group">
                    <div class="button-group__left">
                        <a class="btn btn_outline btn_xs"
                            href="{{ !empty($resourceId) ? Page::indexURL() : Page::backURL() }}">
                            Kembali ke List
                        </a>

                        @if (!empty($action))
                            {{ $action }}
                        @endif

                        @if ($canUpdate && !$disableEdit)
                            <a class="btn btn_primary btn_xs" href="{{ Page::editURL($resourceId) }}">
                                Set Mapping Matriks Penilaian
                            </a>
                        @endif
                    </div>

                    <div class="button-group__mobile">
                        <a href="{{ !empty($resourceId) ? Page::indexURL() : Page::backURL() }}"
                            class="btn btn_outline btn_icon btn_xs">
                            <span class="icon icon-arrow-left-solid"></span>
                        </a>

                        @if ($canUpdate && !$disableEdit)
                            <a class="btn btn_primary btn_icon btn_xs" href="{{ Page::editURL($resourceId) }}">
                                <span class="icon icon-pencil-solid"></span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card__body">
                <!-- Sidebar -->
                <div class="sidebar sidebar_within">
                    <div class="sidebar__header" style="padding: 0; margin-bottom: 1rem;">
                        <h3 class="sidebar__title">
                            Mapping
                        </h3>

                        {{-- <div class="sidebar__action">
                            <button type="button" class="btn btn_icon">
                                <img src="{{ asset('images/icon/icon-layout.svg') }}" alt="">
                            </button>
                        </div> --}}
                    </div>
                    <ul class="sidebar__list">
                        @if (empty($submenu))
                            <li class="sidebar__item">
                                <a class="sidebar__link active" href="#">
                                    <span class="sidebar__link-text">{{ $subtitle }}</span>
                                </a>
                            </li>
                        @endif
                        @foreach ($submenu as $item)
                            @foreach ($item['items'] as $key => $sub)
                                <li class="sidebar__item">
                                    @php
                                        $active = false;
                                        if (request()->get('tab') == 'se' && $key == 1) {
                                            $active = true;
                                        }

                                        if (empty(request()->get('tab')) && $key == 0) {
                                            $active = true;
                                        }
                                    @endphp
                                    <a @class(['sidebar__link', 'active' => $active]) href="{{ url($sub['path']) }}">
                                        <span class="sidebar__link-text">{{ $sub['label'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                </div>
                <!-- Content -->
                <div class="content content-full">
                    <div class="content__header">
                        <h3 class="content__title">
                            {{ $rawData['title'] }}
                        </h3>
                    </div>

                    <div class="content__body">
                        <h3 class="content__title-2">Informasi Umum</h3>
                        <hr class="hr" />
                        <div class="grid">
                            <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                                <label class="row-data__name">Periode</label>
                            </div>
                            <div class="col-8 col-sm-8 col-md-8 col-lg-8">
                                <span class="row-data__value" style="font-size: 0.75rem;">
                                    <span class="row-data__colon">:</span>
                                    <p>{{ $rawData['detail']['tahun_audit'] }}</p>
                                </span>
                            </div>
                        </div>
                        @if ($isProdi)
                            <div class="grid">
                                <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                                    <label class="row-data__name">Panduan</label>
                                </div>
                                <div class="col-8 col-sm-8 col-md-8 col-lg-8">
                                    <span class="row-data__value" style="font-size: 0.75rem;">
                                        <span class="row-data__colon">:</span>
                                        {{ $rawData['detail']['penilaian_panduan_label'] ?? '-- Belum ada panduan yang dipilih --' }}
                                    </span>
                                </div>
                            </div>
                        @endif
                        @if (!$isProdi)
                            <div class="grid">
                                <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                                    <label class="row-data__name">Unit Kerja</label>
                                </div>
                                <div class="col-8 col-sm-8 col-md-8 col-lg-8">
                                    <span class="row-data__value" style="font-size: 0.75rem;">
                                        <span class="row-data__colon">:</span>
                                    </span>
                                    <span style="margin-left: 10px;">{{ $rawData['detail']['unit_name'] }}</span>
                                    <div style="margin-left: 1.5rem;">
                                        <ul>
                                            @foreach ($rawData['detail']['child_unit_options'] as $item)
                                                <li>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="grid">
                                <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                                    <label class="row-data__name">Unit Kerja</label>
                                </div>
                                <div class="col-8 col-sm-8 col-md-8 col-lg-8">
                                    <span class="row-data__value" style="font-size: 0.75rem;">
                                        <span class="row-data__colon">:</span>
                                        <p>{{ $rawData['detail']['unit_name'] }}</p>
                                    </span>
                                </div>
                            </div>
                        @endif
                        @if ($isProdi)
                            <div class="grid">
                                <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                                    <label class="row-data__name">Status</label>
                                </div>
                                <div class="col-8 col-sm-8 col-md-8 col-lg-8">
                                    <span class="row-data__value" style="font-size: 0.75rem;">
                                        <span class="row-data__colon">:</span>
                                        <x-spmi::fields.mapping_status :value="!empty($rawData['detail']['penilaian_panduan_label'])" />
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div style="margin-top: 1rem; margin-bottom: 1rem;"></div>
                    <div class="content__body">
                        <h3 class="content__title-2">Hasil Mapping</h3>
                        <hr class="hr" />

                        @php
                            $width = '30%';
                            if (!$isProdi) {
                                $width = '50%';
                            }
                        @endphp
                        <div style="display: flex; gap: 0.5rem; max-width: {{ $width }};">
                            @if (!$isProdi)
                                <x-core::select label="Pilih Unit Kerja" :options="$rawData['detail']['child_unit_options']" :selected="$rawData['detail']['selected_child_unit']"
                                    onchange="setFilterUnit(event.target.value)" />
                            @endif
                            @if($rawData['detail']['penilaian_panduan_options'])
                            <x-core::select label="Pilih Panduan Penilaian" :options="$rawData['detail']['penilaian_panduan_options']" :selected="$rawData['detail']['selected_penilaian_panduan']"
                                onchange="setFilterPanduan(event.target.value)" />
                            @endif
                            <script>
                                function setFilterUnit(value) {
                                    const url = new URL(window.location.href);
                                    url.searchParams.set('id_unit', value);
                                    window.location.href = url.toString();
                                }

                                function setFilterPanduan(value) {
                                    const url = new URL(window.location.href);
                                    url.searchParams.set('id_penilaian_panduan', value);
                                    window.location.href = url.toString();
                                }
                            </script>
                        </div>
                        <div style="margin-top: 1rem; margin-bottom: 1rem;"></div>
                        <div class="table-max">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 80%;">Nama Butir</th>
                                        <th style="width: 20%;">Kategori</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rawData['indicator_list'] as $row)
                                        <tr>
                                            <td>
                                                <span
                                                    style="display: inline-block; margin-left: {{ $row->info_level * 18 }}px">{!! strip_tags($row->indikator) !!}</span>
                                            </td>
                                            <td><x-spmi::fields.apakah_iku_ikt :value="$row->apakah_data_default" :type="'secondary'"
                                                    :decoration="true" /></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" style="text-align: center;">Belum ada data mapping indikator penilaian yang tersedia</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (!empty($outer))
        {{ $outer }}
    @endif

    @push('scripts')
        <script type="text/javascript" src="{{ asset('js/validation.js') }}"></script>
        <!-- [END] Core script -->


        <script>
            const sidebarWithin = document.querySelector('.sidebar.sidebar_within');
            const mainContent2 = document.querySelector('.content');

            document.querySelector('.sidebar__action button').onclick = function() {
                sidebarWithin.classList.toggle('sidebar_within-collapsed');
                mainContent2.classList.toggle('content_large');

            }
        </script>
    @endpush
</x-core::layouts.outer>
