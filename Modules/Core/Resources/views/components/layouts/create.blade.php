@props([
    'data' => [],
    'menu' => [],
    'subtitle' => null,
    'title' => null,
    'showCollapseInSection' => false,
])
@php
    // default title
    if (empty($title)) {
        $title = (empty($resourceId) ? 'Tambah' : 'Edit') . ' ' . $resourceTitle;
    }

    // parameter form
    if (empty($resourceId)) {
        $method = 'POST';
        $action = Page::indexURL();
    } else {
        $method = 'PUT';
        $action = Page::detailURL($resourceId);
    }

    // buat table of content jika advanced
    $toc = [];
    if (!empty(current($data)['items'])) {
        foreach ($data as $i => $section) {
            if (empty($section['id'])) {
                $section['id'] = Str::kebab($section['title']);
            }

            $toc[$section['id']] = $section['title'];
            $data[$i] = $section;
        }
    }
@endphp
<x-core::layouts.outer header-class="header_position-static" :$menu :$title>
    <x-core::form :$method :$action>
        <div class="form-nav">
            <div class="form-nav__left">
                <a onclick="history.back()"
                    {{-- href="{{ $action }}" --}}
                    class="btn btn_outline">
                    <span class="icon icon-arrow-left-mini"></span>
                    <span class="btn__text">Kembali</span>
                </a>
            </div>
            <div class="form-nav__middle">
                <ul class="form-nav__breadcrumb">
                    <li class="form-nav__breadcrumb-item">
                        <a href="#" class="form-nav__breadcrumb-btn">{{ $title }}</a>
                    </li>
                    <li class="form-nav__breadcrumb-diagonal"></li>
                    <li class="form-nav__breadcrumb-item active">
                        Tambah
                    </li>
                </ul>
            </div>
            <div class="form-nav__right">
                <div class="form-nav__wrapper">
                    <div class="form-nav__button-wrapper">
                        <button type="submit" class="btn btn_primary">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @if ($toc)
            <x-core::layouts.create.toc :data="$toc" />
        @endif
        <x-core::layouts.main.container :$menu :$title :$subtitle>
            <x-core::layouts.html.alert style="margin-bottom:1rem" />
            <div class="grid">
                @if ($slot->isEmpty())
                    <x-core::layouts.create.cards :$data :$showCollapseInSection />
                @else
                    {{ $slot }}
                @endif
            </div>
        </x-core::layouts.main.container>
    </x-core::form>
</x-core::layouts.outer>
