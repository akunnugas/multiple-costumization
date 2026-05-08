@props([
    'data' => [],
    'showCollapseInSection' => false,
])
{{-- dengan atau tanpa header --}}
@if (!empty(current($data)['items']))
    @foreach ($data as $sectionId => $section)
        @php
            $section['id'] = $sectionId;
            $section['data'] = $section['items'];
            $section = Arr::only($section, ['title', 'subtitle', 'data', 'icon', 'id']);
            $attributes = Page::buildAttributes($section);
        @endphp
        <x-core::quantum-3.layouts.create.card :$sectionId {{ $attributes }} :$showCollapseInSection />
    @endforeach
@else
    <x-core::quantum-3.layouts.create.card :$data :$showCollapseInSection />
@endif
