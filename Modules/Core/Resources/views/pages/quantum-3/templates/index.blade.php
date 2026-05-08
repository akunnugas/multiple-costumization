{{-- @props([
    'isDetailV2' => false,
])

@if ($isDetailV2)
    <x-core::layouts.detail-v2.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false"
        :showNumber="$showNumber ?? false" :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :title="$title" :subtitle="$subtitle" />
@else
    <x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false" :showNumber="$showNumber ?? false"
        :staticAlert="$staticAlert ?? []" :emptyState="$emptyState ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :title="$title" :subtitle="$subtitle" :createLabel="$createLabel ?? null" />
@endif --}}


@props([
    'data' => [],
    'header' => [],
    'menu' => [],
    'submenu' => [],

    'canCreate' => null,
    'canDelete' => null
])
@php
    $title = $title ?? null;
    $subtitle = $subtitle ?? null;
@endphp
<x-core::quantum-3.layouts.list 
    :$data 
    :$header 
    :$filter 
    :$search 
    :$sort 
    :$sortDesc 
    :withSync="$withSync ?? false" 
    :withExport="$withExport ?? false"
    :withImport="$withImport ?? false"
    :syncLabel="$syncLabel ?? null"
    :showNumber="$showNumber ?? false"
    :staticAlert="$staticAlert ?? []" 
    :emptyState="$emptyState ?? []" 
    :showDeleteChecked="$showDeleteChecked ?? true" 
    :title="$title" 
    :subtitle="$subtitle" 
    :createLabel="$createLabel ?? null"
    :customCreateLink="$customCreateLink ?? null"
    :$canCreate
    :$canDelete
/>
