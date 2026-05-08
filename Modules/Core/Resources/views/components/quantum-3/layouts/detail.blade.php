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
    $title ??= 'Detail ' . $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }
@endphp
<x-core::quantum-3.layouts.main :$menu :$title :$subtitle :$action :withContainer="true" :fullWidth="false">
    <x-slot:sidebar>
        <x-core::quantum-3.layouts.outer.sidebar :data="$submenu" />
    </x-slot:sidebar>
    <x-core::quantum-3.layouts.html.alert :title="$resourceTitle" />
    @if ($slot->isEmpty())
        @if (!empty($header))
            <x-core::quantum-3.layouts.detail.header :data="$header" />
        @endif
        <x-core::quantum-3.layouts.detail.cards :$data />
    @else
        {{ $slot }}
    @endif
</x-core::quantum-3.layouts.main>
