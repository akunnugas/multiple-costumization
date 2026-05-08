@php
    $link = $value ?? null;
    $linkName = $header['link_name'] ?? null;
@endphp
@if (!empty($link))
    <a href="{{ $link }}" rel="noopener" target="blank">
        {{ $linkName ?? 'Kunjungi URL' }}
    </a>
@endif
