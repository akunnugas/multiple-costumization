@props([
    'data' => [],
    'header' => [],
    'menu' => [],
    'submenu' => [],
    'subtitle' => null,
    'title' => null,
    'action' => null,
])
@php
    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }
@endphp
<x-core::layouts.main :$menu :$title :$subtitle :$action>
    <x-slot:sidebar>
        <x-core::layouts.outer.sidebar :data="$submenu" />
    </x-slot:sidebar>
    <x-core::layouts.html.alert />
    @if ($slot->isEmpty())
        @if (!empty($header))
            <x-core::layouts.detail.header :data="$header" />
        @endif
        <x-core::layouts.detail.cards :$data />
    @else
        {{ $slot }}
    @endif
</x-core::layouts.main>
