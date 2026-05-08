@props([
    'type' => null,
    'variant' => null,
])

@php($class = ['icon', 'icon-' . $type . ($variant ? '-' . $variant : '')])

<span @class($class) {{ $attributes }}></span>
