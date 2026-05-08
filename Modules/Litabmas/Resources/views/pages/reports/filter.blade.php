@props([
    'data' => [],
])

@push('head')
    @vite('Modules/Litabmas/Resources/assets/sass/reports/show.scss')
    @vite('Modules/Litabmas/Resources/assets/js/reports/show.js')
@endpush

@php
    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle =  $title;
    }
@endphp

<x-core::layouts.main :$menu :$title :$subtitle>
    <x-core::form id="form_list">
        <x-core::layouts.html.alert />
        <x-core::layouts.reports.card :$data />
    </x-core::form>
</x-core::layouts.main>