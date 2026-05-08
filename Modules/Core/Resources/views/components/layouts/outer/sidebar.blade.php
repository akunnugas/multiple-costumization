@props([
    'data' => [],
    'backUrl' => null,
])
@php
    // tambahan kembali ke list
    if (!empty($resourceId)) {
        $backUrl ??= Page::backURL();
        array_unshift($data, [
            'items' => [['label' => 'Kembali ke List', 'path' => $backUrl, 'icon' => 'arrow-left-circle-mini']],
        ]);
    }
@endphp
<aside {{ $attributes->merge(['class' => 'sidebar'])  }}>
    @foreach ($data as $item)
        <div class="sidebar__group">
            @if (!empty($item['label']))
                <div class="sidebar__group-header">
                    <span class="sidebar__group-title">{!! $item['label'] !!}</span>
                </div>
            @endif
            <ul class="sidebar__list">
                @foreach ($item['items'] as $sub)
                    <li @class(['sidebar__item', 'active' => !empty($sub['active'])])>
                        <a class="sidebar__link" href="{{ url($sub['path']) }}">
                            @if (!empty($sub['icon']))
                                <span class="icon icon-{{ $sub['icon'] ?? 'document-text-solid' }}"></span>
                            @endif
                            <span class="sidebar__link-text" style="white-space: normal; word-break: break-word; overflow-wrap: break-word;">{{ $sub['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</aside>
