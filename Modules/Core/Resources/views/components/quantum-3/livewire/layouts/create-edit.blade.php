@props([
    'data' => [],
    'menu' => [],
    'subtitle' => null,
    'title' => null,
    'backSubFooter' => null,
])
@php
    // default title
    if (empty($title)) {
        $title = (empty($resourceId) ? 'Tambah' : 'Edit') . ' ' . $resourceTitle;
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

    $defaultRouteName = $attributes['routeName'] ?? $routeName;
    $alert = $attributes['alert'] ?? $alert ?? null;
    [$module, $resource, $type] = explode('.', $defaultRouteName);

    if (!empty($backSubFooter)) {
        $action = url($backSubFooter);
    } else if (empty($resourceId)) {
        $action = route($module . '.' . $resource . '.' . 'index');
    } else {
        $action = route($module . '.' . $resource . '.' . 'show', [$resourceId]);
    }
@endphp

@pushOnce('head')
    <style>
        .full-page-loader {
            display: flex;
            position: fixed;
            left: 0;
            top: 0;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: #ffffff90;
            z-index: 9999;
        }

        .hidden {
            display: none !important;
        }
    </style>
@endPushOnce

<form wire:submit="save">
    <header class="qn-header z-1 sticky-top p-md-3 py-2 py-md-2 border-bottom shadow-sm bg-white">
        <div class="container-fluid d-grid d-flex justify-content-between align-items-center position-relative">
            <!-- Back Button -->
            <a href="{{ $action }}" class="btn btn-light gap-2 d-none d-md-flex" role="button">
                <i class="sym sym-arrow-narrow-left"></i>Kembali
            </a>
            <a href="{{ $action }}" class="btn btn-icon btn-light d-block d-md-none" role="button">
                <i class="sym sym-arrow-narrow-left"></i>
            </a>

            <!-- Breadcrumb Navigation -->
            <nav class="position-absolute top-50 start-50 translate-middle" aria-label="breadcrumb">
                <ol class="qn-form-breadcrumb breadcrumb m-0 d-none d-md-flex opacity-0">
                    <li class="breadcrumb-item">
                        <a href="#">{{ $resourceTitle }}</a>
                    </li>
                    <li class="breadcrumb-item active fw-bold" aria-current="page">
                        {{ empty($resourceId) ? 'Tambah' : 'Edit' }}
                    </li>
                </ol>
                <ol class="breadcrumb m-0 d-flex d-md-none">
                    <li class="breadcrumb-item active fw-bold" aria-current="page">
                        {{ empty($resourceId) ? 'Tambah ' . $resourceTitle : 'Edit ' . $resourceTitle }}
                    </li>
                </ol>
            </nav>

            <!-- Actions -->
            <div class="d-flex gap-3 align-items-center">
                <!-- Form Submit Action -->
                @if(empty($customAction))
                    <button wire:click="save" type="button" class="d-none d-md-block btn btn-primary">
                        Simpan
                    </button>
                @else
                    {{ $customAction }}
                @endif
            </div>
        </div>
    </header>
    @if ($toc)
        <x-core::quantum-3.layouts.create.toc :data="$toc" />
    @endif
    <x-core::quantum-3.layouts.main.container :$menu :$title :$subtitle>
        <x-core::quantum-3.layouts.html.alert style="margin-bottom:1rem" :data="$alert" />

        @if (!isset($slot))
            <x-core::quantum-3.layouts.create.cards :$data />
        @else
            {{ $slot }}
        @endif

    </x-core::quantum-3.layouts.main.container>
    @if (isset($attributes['routeName']))
        <input type="hidden" wire:model="routeName" value="{{ $defaultRouteName }}" />
    @endif
</form>

@pushOnce('end')
    <div class="full-page-loader hidden">
        <div class="loader">
            <span class="loader__spinner"></span>
        </div>
    </div>
@endPushOnce

@pushOnce('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('show-loading', () => {
                document.querySelector('.full-page-loader').classList.remove('hidden');
            });

            Livewire.on('hide-loading', () => {
                document.querySelector('.full-page-loader').classList.add('hidden');
            });

            Livewire.on('scroll-to-top', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });

            Livewire.on('scroll-to-section', (sectionId) => {
                const section = document.getElementById(sectionId);
                if (section) {
                    window.scrollTo({ top: section.offsetTop - 20, behavior: 'smooth' });
                }
            });

            Livewire.hook('commit', ({succeed}) => {
                succeed(() => {
                    queueMicrotask(() => {
                        // scroll to first input error
                        let firstError = document.querySelector('.form-control__helper.error');
                        if (firstError) {
                            window.scrollTo({ top: firstError.parentElement.offsetTop - 20, behavior: 'smooth' });
                        }
                    });
                });
            });
        });
    </script>
@endPushOnce
