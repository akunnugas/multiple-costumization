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

<select {{ $attributes->class(['select-multiple']) }} id="{{ $attributes['name'] }}" multiple>
    @if ($slot->isEmpty())
        @foreach ($options as $value => $text)
            @php
                $selected = in_array($value, $values);
            @endphp
            <option value="{{ $value }}" @selected($selected)>{!! $text !!}</option>
        @endforeach
    @else
        {{ $slot }}
    @endif
</select>
