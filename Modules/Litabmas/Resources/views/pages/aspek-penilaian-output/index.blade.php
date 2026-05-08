@php
    $showDetail ??= false;
    $canUpdate ??= false;
    $isEditInline = false;
@endphp
<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false"
                      :showNumber="$showNumber ?? false" :staticAlert="$staticAlert ?? []" :$title
                      :showDeleteChecked="$showDeleteChecked ?? true" :$canUpdate :$showDetail :$isEditInline
/>
