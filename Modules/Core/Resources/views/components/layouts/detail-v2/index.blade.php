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
    @endPushOnce

    <div class="container">
        <x-core::layouts.html.alert class="mb-3"/>
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
                                Ubah Data
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
                    <div class="sidebar__header" style="padding: 0;">
                        <h3 class="sidebar__title">
                            {{ $title }}
                        </h3>

                        <div class="sidebar__action">
                            <button type="button" class="btn btn_icon">
                                <img src="{{ asset('images/icon/icon-layout.svg') }}" alt="">
                            </button>
                        </div>
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
                            @foreach ($item['items'] as $sub)
                                <li class="sidebar__item">
                                    <a @class(['sidebar__link', 'active' => !empty($sub['active'])]) href="{{ url($sub['path']) }}">
                                        <span class="sidebar__link-text">{{ $sub['label'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                </div>
                <!-- Content -->
                <div @class(['content', 'content_full' => $isFullwidth])>
                    @if ($slot->isEmpty())
                        <x-core::layouts.detail-v2.cards :$data />
                    @else
                        {{ $slot }}
                    @endif
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
