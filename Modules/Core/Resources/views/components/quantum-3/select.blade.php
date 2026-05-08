@props([
    'label' => null,
    'options' => [],
    'disabledItems' => [],
    'selected' => null,
    'style' => 'default',
    'variant' => null,
    'isEmpty' => null,
    'withLabel' => true
])

@php
    $attributes = Page::buildAttributes(attributes: $attributes, isLivewire: $isLivewire ?? null);
    $variant ??= $style;
    $noClassDefault = $attributes['no_class_default'] ?? null;
    unset($attributes['no_class_default']);

    if (!$withLabel) {
        $options = array_filter($options, function($key) {
            return !empty($key);
        }, ARRAY_FILTER_USE_KEY);
    }

    if ($variant === 'multiple') {
        $attributes->setAttributes([...$attributes,'multiple' => true]);
        unset($options[""]);
    }
    if (!$attributes->has('id')) {
        $attributes->setAttributes([...$attributes, 'id' => 'form-control-' . $attributes->get('name')]);
    }
    //$attributes->setAttributes([...$attributes, 'id' => 'form-control-' . $attributes->get('name')]);

@endphp

<select {{ $attributes->class([
    'form-select', 'select-' . $variant => empty($noClassDefault),
]) }}>
    @if ($slot->isEmpty())
        @if (!empty($label) || $variant !== 'multiple')
            <option selected @if (!$isEmpty) disabled @endif value>{{ $label }}</option>
        @endif

        @if ($variant == 'multiple')
            @foreach ($options as $value => $text)
                @php
                    $selected = in_array($value, $values);
                @endphp
                <option value="{{ $value }}" @selected($selected)>{!! $text !!}</option>
            @endforeach
        @else            
            @foreach ($options as $value => $text)
                @php
                    $disabled = in_array($value, $disabledItems);
                @endphp
                <option wire:key="{{ $attributes['name'] }}-{{ $value }}" value="{{ $value }}" @selected($value == $selected) @disabled($disabled)>
                    {!! $text !!}</option>
            @endforeach
        @endif
    @else
        {{ $slot }}
    @endif
</select>
