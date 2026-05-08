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
<x-dms::layouts.outer :header-class="$headerClass" :$menu :$title>
    @if (!empty($submenu))
        <x-slot:sidebar>
            {{ $submenu }}
        </x-slot:sidebar>
    @endif
    {{ $slot }}
    @push('scriptsModule')
        @vite('Modules/Core/Resources/assets/js/app.js')
        @vite('Modules/Core/Resources/assets/js/livewire-hook.js')
    @endpush
</x-dms::layouts.outer>
