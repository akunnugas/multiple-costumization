@php
    $link = $item['original'] ?? null;
    $linkName = $item['link_name'] ?? null;
    $linkIcon = $item['link_icon'] ?? null;
@endphp
@if (!empty($link))
    <a href="{{ $link }}" rel="noopener" target="blank">
        {{ $linkName ?? 'Kunjungi URL' }}

        @if($linkIcon)
            <x-core::icon type="{{ $linkIcon }}" />
        @endif
    </a>
@endif
