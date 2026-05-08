@props([
    'type' => null,
    'variant' => null,
    'icon' => null
])

@php
    $class = ['sym', 'sym-' . $type . ($variant ? '-' . $variant : '')];

    if (!empty($icon)) {
        $class = ['sym', 'sym-'.$icon];
    }
@endphp

<i @class($class) {{ $attributes }}></i>
