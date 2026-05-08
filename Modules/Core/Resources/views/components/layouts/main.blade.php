@props([
    'action' => null,
    'menu' => [],
    'sidebar' => null,
    'subtitle' => null,
    'title' => null,
    'withContainer' => true,
])
<x-core::layouts.main-outer :$menu :submenu="$sidebar" :$title>
    @if ($withContainer)
        <x-core::layouts.main.container :$menu :$action :$title :$subtitle>
            {{ $slot }}
        </x-core::layouts.main.container>
    @else
        {{ $slot }}
    @endif
</x-core::layouts.main-outer>
