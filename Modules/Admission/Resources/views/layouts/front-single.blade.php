@props([
    'title' => null,
    'withSidebar' => true,
])
@php
    $menu = [
        [
            'label' => 'Home',
            'path' => 'admission/home#',
        ],
        [
            'label' => 'Program Studi',
            'path' => 'admission/home#program-section',
        ],
        [
            'label' => 'Fasilitas',
            'path' => 'admission/home#facility-section',
        ],
        [
            'label' => 'Alumni',
            'path' => 'admission/home#alumni-section',
        ],
        [
            'label' => 'Pengumuman',
            'path' => 'admission/home#announcement-section',
        ],
    ];
@endphp
<x-core::layouts.html :$title :class="'admission'">
    {{-- Header --}}
    <x-admission::header-single :menu="$menu" />

    {{-- Sidebar --}}
    @if ($withSidebar)
        <x-core::layouts.outer.sidebar :data="$submenu" :class="'sidebar_without-icon aside-mobile'" />
    @endif

    <main class="main">
        {{-- Main Content --}}
        {{ $slot }}

        {{-- Footer --}}
        {{-- <x-admission::footer /> --}}
    </main>

    @push('head')
        @vite('Modules/Admission/Resources/assets/sass/admission.scss')
    @endpush

    @push('scripts')
        @vite('Modules/Core/Resources/assets/js/livewire-hook.js')
    @endpush
</x-core::layouts.html>
