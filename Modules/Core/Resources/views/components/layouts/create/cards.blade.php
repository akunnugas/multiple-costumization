@props([
    'data' => [],
    'showCollapseInSection' => false,
])
{{-- dengan atau tanpa header --}}
@if (!empty(current($data)['items']))
    @foreach ($data as $section)
        @php
            $section['data'] = $section['items'];
            $section = Arr::only($section, ['title', 'subtitle', 'data', 'icon']);

            $attributes = Page::buildAttributes($section);
        @endphp
        <x-core::layouts.create.card {{ $attributes }} :$showCollapseInSection />
    @endforeach
@else
    <x-core::layouts.create.card :$data :$showCollapseInSection />
@endif
