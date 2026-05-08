@props([
    'title' => null,
    'withSidebar' => true,
])

<x-core::layouts.html :$title :class="'admission'">
    {{-- Header --}}
    <x-admission::header />

    {{-- Sidebar --}}
    @if($withSidebar)
        <x-core::layouts.outer.sidebar :data="$submenu" :class="'sidebar_without-icon aside-mobile'" />
    @endif

    <main class="main">
        {{-- Main Content --}}
        {{ $slot }}

        {{-- Footer --}}
        <x-admission::footer />
    </main>

    @pushonce('head')
        @vite('Modules/Admission/Resources/assets/sass/admission.scss')

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
        </style>
    @endpushonce

    @pushonce('end')
        <div class="full-page-loader util_d-none">
            <div class="loader">
                <span class="loader__spinner"></span>
            </div>
        </div>
    @endpushonce

    @pushonce('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('show-loading', (event) => {
                    document.querySelector('.full-page-loader').classList.remove('util_d-none');
                });

                // livewire on dispatch event 'hide-loading'
                Livewire.on('hide-loading', (event) => {
                    document.querySelector('.full-page-loader').classList.add('util_d-none');
                });
            });
        </script>
    @endpushonce
</x-core::layouts.html>
