@props([
    'data' => [],
    'menu' => [],
    'subtitle' => null,
    'title' => null,
    'showCollapseInSection' => false,
])
@php
    // default title
    $actionLabel = (empty($resourceId) ? 'Tambah' : 'Edit');
    if (empty($title)) {
        $title = $actionLabel . ' ' . $resourceTitle;
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

    $withUpload = !empty(array_column($data, 'file_type'));
@endphp
<x-core::quantum-3.layouts.outer header-class="position-static" :$menu :$title>
        <x-core::quantum-3.layouts.main.container :$menu :$title :$subtitle class="row" :withHeader="false">
            <x-core::quantum-3.layouts.html.alert style="margin-bottom:1rem" />

            <header class="qn-header z-1 sticky-top p-md-3 py-2 py-md-2 border-bottom shadow-sm bg-white">
                <div class="container-fluid d-grid d-flex justify-content-between align-items-center position-relative">
                    <a href="{{ $action }}" class="btn btn-light gap-2 d-none d-md-flex" role="button">
                        <i class="sym sym-arrow-narrow-left"></i>
                        Kembali
                    </a>
                    <a href="{{ $action }}" class="btn btn-icon btn-light d-block d-md-none" role="button">
                        <i class="sym sym-arrow-narrow-left"></i>
                    </a>
                    <nav class="position-absolute top-50 start-50 translate-middle" aria-label="breadcrumb">
                        <ol class="qn-form-breadcrumb breadcrumb m-0 d-none d-md-flex">
                            <li class="breadcrumb-item">
                                <a href="{{ $action }}">{{ $resourceTitle }}</a>
                            </li>
                            <li class="breadcrumb-item active fw-bold" aria-current="page">
                                {{ $actionLabel }}
                            </li>
                        </ol>
                        <ol class="breadcrumb m-0 d-flex d-md-none">
                            <li class="breadcrumb-item active fw-bold" aria-current="page">
                                {{ $title }}
                            </li>
                        </ol>
                    </nav>
                    <div class="d-flex gap-3 align-items-center">
                        @if (!empty($resourceId))                            
                            {{-- <span class="d-none d-xl-flex gap-1 text-body-tertiary">
                                <i class="sym sym-check"></i>
                                Tersimpan 2 menit yang lalu
                            </span> --}}
                            <span
                                class="d-flex d-xl-none gap-1 text-body-tertiary"
                                data-bs-toggle="tooltip"
                                data-bs-placement="left"
                                data-bs-title="Tersimpan 2 menit yang lalu"
                            >
                            <i class="sym sym-check"></i>
                        @endif
                    </span>
                        <button
                            type="submit"
                            class="d-none d-md-block btn btn-primary"
                        >
                            Simpan
                        </button>
                    </div>
                </div>
            </header>

            @if ($toc)
                <x-core::quantum-3.layouts.create.toc :data="$toc" />
            @endif

            <form action="{{ $action }}" method="{{ $method == 'GET' ? 'GET' : 'POST' }}" @if (!empty($withUpload)) enctype="multipart/form-data" @endif class="qn-form needs-validation py-3 ps-3 position-relative col-xl-6 {{ $toc ? 'col-md-9 offset-xxl-1' : 'col-xl-6 offset-xl-3' }}" novalidate>
                <div class="row px-3 row-cols-1 gy-4">
                    <div class="d-md-block d-none">
                        <x-core::quantum-3.layouts.main.header :$menu :$title :$subtitle />
                    </div>
                    @if ($method != 'GET')
                        @csrf
                    @endif
                    @if (!in_array($method, ['GET', 'POST']))
                        @method($method)
                    @endif
                    @if ($slot->isEmpty())
                        <x-core::quantum-3.layouts.create.cards :$data :$showCollapseInSection />
                    @else
                        {{ $slot }}
                    @endif
                </div>
            </form>
        </x-core::quantum-3.layouts.main.container>
        <div class="d-block d-md-none rounded-top-4 shadow-lg sticky-bottom">
            @if ($toc)
                <x-core::quantum-3.layouts.create.toc-mobile :data="$toc"/>
            @endif
            <div class="w-100 d-flex bg-white gap-2 p-3">
                @if ($toc)
                    <button type="button" class="btn btn-icon btn-light" aria-label="Table of Content" data-bs-toggle="collapse" data-bs-target="#tocMobile" aria-expanded="false" aria-controls="tocMobile">
                        <i class="sym sym-list"></i>
                    </button>
                @endif
                <x-core::quantum-3.button type="submit" class="w-100" color="primary">
                    Simpan
                </x-core::quantum-3.button>
            </div>
        </div>

        @pushOnce('scripts')
            <script type="module">
                document.addEventListener('DOMContentLoaded', () => {
                    Form.eventCheckFormValidity()
                    Form.submitEvent(document.querySelector('.qn-form'), document.querySelector('button[type="submit"]'))
                })
            </script>
        @endPushOnce
</x-core::quantum-3.layouts.outer>

