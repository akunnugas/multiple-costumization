@props([
    'disabled' => false,
    'href' => null,
    'icon' => false,
    'leadingIcon' => null,
    'size' => null,
    'trailingIcon' => null,
    'type' => 'button',
    'variant' => 'primary',
    'style' => null,
])

@php($class = ['btn', 'btn-icon d-md-flex justify-content-center align-items-center' => $icon, 'gap-2 d-md-flex' => !$icon, 'btn-' . $variant => $variant, 'btn-' . $size => $size])

@if ($href && empty($disabled))
    <a href="{{ $href }}" {{ $attributes->class($class) }} {{ $attributes->style($style) }}
        {{ $attributes->whereStartsWith('wire') }}>
@else
        <button type="{{ $type }}" @disabled($disabled) {{ $attributes->class($class) }}
            {{ $attributes->style($style) }} {{ $attributes->whereStartsWith('wire') }}>
@endif
@if (!empty($leadingIcon))
    <x-core::quantum-3.icon :type="$leadingIcon" />
@endif
{{ $slot }}
@if (!empty($trailingIcon))
    <x-core::quantum-3.icon :type="$trailingIcon" />
@endif
@if ($href && empty($disabled))
    </a>
@else
    </button>
@endif
