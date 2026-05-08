@props([
    'headerClass' => null,
    'menu' => [],
    'sidebar' => null,
    'title' => null,
])
@php
    // default title
    $title ??= $resourceTitle;
@endphp
<x-core::layouts.html :$title>
    <x-core::layouts.outer.header :class="$headerClass" :$menu />
    @if (!empty($sidebar))
        {{ $sidebar }}
    @endif
    <main class="main">
        {{ $slot }}
        <x-core::layouts.outer.footer />
    </main>
</x-core::layouts.html>
