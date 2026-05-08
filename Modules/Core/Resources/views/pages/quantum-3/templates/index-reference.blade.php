@props([
    'isDetailV2' => false,
])
@if ($isDetailV2)
    <x-core::quantum-3.layouts.detail-v2.list :$data :$header :$create :$edit :$filter :$search :$sort :$sortDesc
        :withSync="$withSync ?? false" :showNumber="$showNumber ?? false" :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :is-reference="true" />
@else
    <x-core::quantum-3.layouts.reference :$data :$header :$create :$edit :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false"
        :showNumber="$showNumber ?? false" :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true" />
@endif
