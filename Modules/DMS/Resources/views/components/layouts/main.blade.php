@props([
    'action' => null,
    'menu' => [],
    'sidebar' => null,
    'subtitle' => null,
    'title' => null,
])
<x-dms::layouts.main-outer :$menu :submenu="$sidebar" :$title>
    <x-core::layouts.main.container :$menu :$action :$title :$subtitle>
        {{ $slot }}
    </x-core::layouts.main.container>
</x-dms::layouts.main-outer>
