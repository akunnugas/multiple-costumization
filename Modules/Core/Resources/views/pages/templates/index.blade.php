@props([
    'isDetailV2' => false,
])

@php
    $title = $title ?? null;
    $subtitle = $subtitle ?? null;
@endphp

@if ($isDetailV2)
    <x-core::layouts.detail-v2.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false"
        :showNumber="$showNumber ?? false" :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :title="$title" :subtitle="$subtitle" :createLabel="$createLabel ?? null" :customCreateUrl="$customCreateUrl ?? null" />
@else
    <x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false" :showNumber="$showNumber ?? false"
        :staticAlert="$staticAlert ?? []" :emptyState="$emptyState ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :title="$title" :subtitle="$subtitle" :createLabel="$createLabel ?? null" :customCreateUrl="$customCreateUrl ?? null"/>
@endif
