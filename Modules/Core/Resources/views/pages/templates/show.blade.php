@props([
    'isDetailV2' => false,
])

@if ($isDetailV2)
    <x-core::layouts.detail-v2 :data="$data" :isFullwidth="$isFullwidth ?? false" />
@else
    <x-core::layouts.detail :data="$data" :title="$title ?? null" />
@endif
