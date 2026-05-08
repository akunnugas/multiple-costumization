@props([
    'action' => null,
    'title' => null,
    'menu' => [],
    'backUrl' => null,
])

<x-dms::layouts.main :$menu :$title :$action>
    @if ($submenu)
        <x-slot:sidebar>
            <x-dms::layouts.outer.sidebar :data="$submenu" :backUrl="$backUrl" />
        </x-slot:sidebar>
    @endif

    {{$slot}}
</x-dms::layouts.main>
