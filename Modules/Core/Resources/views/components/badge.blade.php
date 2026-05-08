@props([
    'decoration' => false,
    'size' => null,
    'type' => null,
    'variant' => 'primary',
])

@php($class = ['badge', 'badge_' . ($type ? $type . '-' : '') . $variant => $variant, 'badge_' . $size => $size])

{{-- <span @class($class) {{ $attributes }}> --}}
<span {{ $attributes->class($class)}} {{ $attributes }}>
    @if ($decoration === true)
        <span class="badge__dot"></span>
    @elseif (!empty($decoration))
        {{ $decoration }}
    @endif
    {{ $slot }}
</span>
