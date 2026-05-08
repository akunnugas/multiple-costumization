@props([
    'data' => [],
])
@foreach ($data as $section)
    @php
        $attributes = Page::buildAttributes(Arr::only($section, ['title', 'subtitle', 'icon', 'page_conf']) + ['data' => $section['items']]);
    @endphp
    <x-core::quantum-3.layouts.detail.card {{ $attributes }}>
        <x-slot:action>
            @if (!empty($section['edit_url']))
                <x-core::quantum-3.button 
                    href="{{ $section['edit_url'] }}" 
                    variant="light" 
                    leading-icon="{{ $section['edit_icon'] ?? 'edit-02' }}"
                >
                    {{ $section['edit_label'] ?? 'Ubah Data' }}
                </x-core::quantum-3.button>
            @endif
        </x-slot:action>
    </x-core::quantum-3.layouts.detail.card>
@endforeach
