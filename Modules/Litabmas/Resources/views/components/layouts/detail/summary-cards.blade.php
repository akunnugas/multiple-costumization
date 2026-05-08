@props([
    'data' => [],
])
@foreach ($data as $section)
    @php
        $attributes = Page::buildAttributes(['data' => $section['items']]);
    @endphp
    <x-litabmas::layouts.detail.summary-card {{ $attributes }} />
@endforeach
