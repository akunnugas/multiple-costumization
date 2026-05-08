@props([
    'data' => [],
])
@foreach ($data as $section)
    @php
        $attributes = Page::buildAttributes(Arr::only($section, ['title', 'subtitle', 'icon', 'page_conf']) + ['data' => $section['items']]);
    @endphp
    <x-core::layouts.detail.card {{ $attributes }}>
        <x-slot:action>
            @if (!empty($section['edit_url']))
                <x-core::button href="{{ $section['edit_url'] }}" variant="outline"
                    leading-icon="{{ $section['edit_icon'] ?? 'pencil-square-solid' }}">
                    {{ $section['edit_label'] ?? 'Ubah Data' }}
                </x-core::button>
            @endif
        </x-slot:action>
    </x-core::layouts.detail.card>
@endforeach
