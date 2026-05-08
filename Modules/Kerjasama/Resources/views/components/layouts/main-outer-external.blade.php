@props([
    'menu' => [],
    'submenu' => null,
    'title' => null,
    'headerClass' => null,
])
@php
    // default title
    $title ??= $resourceTitle;
@endphp

<x-kerjasama::layouts.outer-external :header-class="$headerClass" :$menu :$title>
   @if (!empty($submenu))
        <x-slot:sidebar>
            {{ $submenu }}
        </x-slot:sidebar>
    @endif
   {{ $slot }}
    @push('scriptsModule')
        @vite('Modules/Core/Resources/assets/quantum-3/js/app.js')
    @endpush
</x-kerjasama::layouts.outer-external>
