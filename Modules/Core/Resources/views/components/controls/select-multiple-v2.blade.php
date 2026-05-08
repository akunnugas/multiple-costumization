@props([
    'label' => null,
    'options' => [],
    'selected' => null,
    'variant' => null,
    'values' => [],
])

@php
    $attributes = Page::buildAttributes(attributes: $attributes, isLivewire: $isLivewire ?? null);
    unset($attributes['no_class_default']);

    if (isset($attributes['wire:model'])) {
        unset($attributes['wire:model']);
    }

    $name = $attributes['name'];
    $dataCy = $attributes['data-cy'] ?? $name ?? null;
    if ($dataCy) {
        $attributes['data-cy'] = $dataCy;
    }
@endphp

@if($isLivewire)
    <div wire:ignore class="wrapper-select-multiple-v2" style="width:100%;">
@endif
    <select {{ $attributes->class(['select-multiple-v2']) }} id="{{ $attributes['name'] }}" multiple>
        @if ($slot->isEmpty())
            @foreach ($options as $value => $text)
                @php
                    $selected = in_array($value, $values);
                @endphp
                <option value="{{ $value }}" @selected($selected) wire:ignore.self>{!! $text !!}</option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>
@if($isLivewire)
    </div>
@endif
