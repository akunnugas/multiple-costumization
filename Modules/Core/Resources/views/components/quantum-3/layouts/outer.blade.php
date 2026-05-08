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
<x-core::quantum-3.layouts.html :$title>
    <div class="">
        <x-core::quantum-3.layouts.outer.header :with-form-header="!empty($headerForm)" :class="$headerClass" :$menu />
        @if (!empty($headerForm))
            {{ $headerForm }}
        @endif
        @if (!empty($sidebar))
            {{ $sidebar }}
        @endif
        <main class="qn-main bg-body-tertiary d-flex flex-column">
            {{ $slot }}
            <x-core::quantum-3.layouts.outer.footer />
        </main>
    </div>
</x-core::quantum-3.layouts.html>
