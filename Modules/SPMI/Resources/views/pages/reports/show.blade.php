@props([
    'data' => [],
])


@push('head')
    @vite('Modules/SPMI/Resources/assets/sass/reports/show.scss')
@endpush
@php
    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle =  $title;
    }
@endphp

<x-core::layouts.main :$menu :$title :$subtitle>
    <x-core::layouts.html.alert />
    @livewire('spmi.report-form')
</x-core::layouts.main>