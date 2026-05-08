@props([
    'data' => [],
    'backUrl' => null
])
@php
    // tambahan kembali ke list
    if (!empty($resourceId)) {
        array_unshift($data, [
            'items' => [['label' => 'Kembali ke List', 'path' => $backUrl ?? Page::backURL(), 'icon' => 'arrow-left-circle-mini']],
        ]);
    }
@endphp

@pushOnce('head')
    @vite('Modules/DMS/Resources/assets/sass/components/sidebar.scss')
@endPushOnce

<aside {{ $attributes->merge(['class' => 'sidebar']) }}>
    @foreach ($data as $item)
        <div class="sidebar__group">
            @if (!empty($item['label']))
                <div class="sidebar__group-header">
                    <span class="sidebar__group-title">{{ $item['label'] }}</span>
                </div>
            @endif
            <ul class="sidebar__list">
                @foreach ($item['items'] as $sub)
                    <li @class(['sidebar__item', 'active' => !empty($sub['active'])])>
                        <a class="sidebar__link" href="{{ url($sub['path']) }}">
                            @if (!empty($sub['icon']))
                                <span class="icon icon-{{ $sub['icon'] ?? 'Dokumen-text-solid' }}"></span>
                            @endif
                            <span class="sidebar__link-text">{{ $sub['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach

    <x-dms::usage-card :max="$maxSize" :value="$currentSize" />
</aside>
