@props([
    'checked' => false,
    'description' => null,
    'disabled' => false,
    'choiceLabel' => null,
    'label' => null,
    'size' => null,
    'isValueHtml' => false
])

@php
    $id = $attributes['id'] ?? null;
    $name = $attributes['name'] ?? null;
    if (empty($label) && !empty($name)) {
        $label = Page::defineLabelByField($name);
    }

    $label = $choiceLabel ?? $label;
@endphp

<div @class(array: ['form-check', 'form-check-' . $size => $size])>
    <input type="checkbox" {{ $attributes->class(['form-check-input']) }} @checked($checked) @disabled($disabled)>
    @empty($description)
        <label class="form-check-label" @if (!empty($id)) for="{{ $id }}" @endif>
            @if($isValueHtml)
                {!! $label !!}
            @else
                {{ $label }}
            @endif
        </label>
    @else
        <div class="form-control__label">
            <label class="form-check-label" @if (!empty($id)) for="{{ $id }}" @endif>
                @if($isValueHtml)
                    {!! $label !!}
                @else
                    {{ $label }}
                @endif
            </label>
            <span class="form-control__label-desc">{{ $description }}</span>
        </div>
    @endempty
</div>
