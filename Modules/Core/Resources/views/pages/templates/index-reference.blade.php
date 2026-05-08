@props([
    'isDetailV2' => false,
])

@if ($isDetailV2)
    <x-core::layouts.detail-v2.list :$data :$header :$create :$edit :$filter :$search :$sort :$sortDesc
        :withSync="$withSync ?? false" :showNumber="$showNumber ?? false" :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :is-reference="true" />
@else
    @php
        $title ??= $resourceTitle;
    @endphp
    <x-core::layouts.reference :$data :$header :$create :$edit :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false"
        :title="$title" :subtitle="$subtitle ?? null" :syncMessage="$syncMessage ?? null" :showNumber="$showNumber ?? false" :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true" />
@endif
